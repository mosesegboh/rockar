<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Youdrive
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar_YouDrive') . DS . 'AjaxController.php';

class Peppermint_YouDrive_AjaxController extends Rockar_YouDrive_AjaxController
{
    /**
     * Rewrite of parent function to create new booking for cancelled bookings
     *
     * {@inheritDoc}
     */
    protected function _processBooking(array $params)
    {
        $helper = Mage::helper('rockar_youdrive');
        $result = ['success' => false, 'message' => null, 'error_code' => null];
        $isTDRequest = (int) $params['isTDRequest'];

        try {
            if (!array_key_exists('modelIds', $params)) {
                $result['error_code'] = 'vehicle_not_selected';
                throw new Exception($helper->__('Vehicle for test drive is not selected!'));
            }

            $vehicles = Mage::getModel('rockar_youdrive/vehicle')->getCollection()
                ->addFieldToFilter('id', ['in' => $params['modelIds']]);

            $store = Mage::helper('peppermint_localstores/data')->getStoreFromCode($params['localStoreCode']);

            if (!$store) {
                $result['error_code'] = 'localstore_invalid';
                throw new Exception($helper->__('Local store does not exist'));
            }

            foreach ($vehicles as $vehicle) {
                if (!$vehicle->getId()) {
                    $result['error_code'] = 'vehicle_unavailable';
                    throw new Exception($helper->__('Vehicle is not available for test drive'));
                }

                /** Check compound if car is requested */
                if ($isTDRequest) {
                    if ($vehicle->getAssignedTo() != $params['localStoreCode']) {
                         if ($store->getAssociatedCompoundDealer() !== $vehicle->getAssignedTo()) {
                            $result['error_code'] = 'vehicle_unavailable';
                            throw new Exception($helper->__('Vehicle cannot be requested at this local store.'));
                        }
                    } else {
                        $result['error_code'] = 'cannot_request';
                        throw new Exception($helper->__('Vehicle request invalid.'));
                    }
                }
            }

            if (!$isTDRequest && !array_key_exists('bookingDatetime', $params)) {
                $result['error_code'] = 'booking_date_unavailable';
                throw new Exception($helper->__('Incorrect or no test drive booking date is selected!'));
            }

            if (!$isTDRequest && !$helper->validateBookingByTime($params)) {
                $result['error_code'] = 'booking_date_unavailable';
                throw new Exception($helper->__('Test drive booking date is not available!'),
                    self::YOU_DRIVE_SLOTS_TAKEN_CODE);
            }

            $booking = Mage::helper('rockar_youdrive')->getMatchingBooking([
                'assigned_to' => $params['localStoreCode'],
                'booked_to' => $this->formatBookedToDate($params['bookingDatetime']),
                'customer_id' => $this->_getCustomerSession()->getCustomer()->getId()
            ]);

            if (array_key_exists('bookingId', $params)) {
                $booking->load($params['bookingId']);

                if ($booking->getCustomerId() !== $this->_getCustomerSession()->getCustomer()->getId() ||
                    (int) $booking->getStatus() === Rockar_YouDrive_Helper_Booking_Status::BOOKING_STATUS_CANCELLED) {
                    $booking = Mage::getModel('rockar_youdrive/booking');
                }

                $booking->setIsRebooking((bool) Mage::helper('rockar_youdrive/booking_progress')->getBookingId());
            }

            $booking->addData([
                'status' => $isTDRequest ?
                    Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_REQUESTED :
                    Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_DUE,
                'ip' => Mage::helper('core/http')->getRemoteAddr(),
                'customer_id' => $this->_getCustomerSession()->getCustomer()->getId(),
                'vehicle_ids' => $params['modelIds'], // used in _afterSave method for saving booking items
                'vehicle_id' => $params['modelIds'][0],
                'booked_on' => $isTDRequest ? '' : $params['bookingDatetime'],
                'booked_to' => $isTDRequest ? '' : $this->formatBookedToDate($params['bookingDatetime']),
                'store_id' => $this->getStore()->getId(),
                'assigned_to' => $params['localStoreCode'],
                'send_confirmation' => true // Add send_confirmation field to send confirmation on afterSave event
            ])->save();

            Mage::dispatchEvent('rockar_youdrive_save_booking_after', ['booking' => $booking]);

            Mage::helper('rockar_youdrive/booking_progress')->clearProgress();

            $result = [
                'success' => true,
                'message' => $helper->__('Test drive booking created'),
                'bookingId' => $booking->getId()
            ];
        } catch (Exception $e) {
            $result['error_code'] = $e->getCode();
            $result['message'] = $e->getMessage();
            $result['slots_taken'] = ($e->getCode() == self::YOU_DRIVE_SLOTS_TAKEN_CODE) ? 1 : 0;

            Mage::logException($e);
        }

        return $result;
    }

    /**
     * Profile update from the YouDrive
     *
     * @return void
     */
    public function customerUpdateAction()
    {
        $session = Mage::getSingleton('customer/session');

        if ($session->isLoggedIn()) {
            try {
                $customerData = array_intersect_key(
                    $this->getRequest()->getParam('customer'),
                    array_fill_keys([
                        'firstname',
                        'lastname',
                        'prefix',
                        'primary_phone_number',
                        'driving_license_type',
                        'dob'
                    ], 1)
                );
                $customerData['primary_number'] = $customerData['primary_phone_number'];

                $customerData = array_map('htmlentities', $customerData);
                $customerData = array_map(function ($str) {
                    // Remove vue control chars
                    return str_replace([
                        '(',
                        ')',
                        '{',
                        '}'
                    ], '', $str);
                }, $customerData);

                $customer = $session->getCustomer();
                $customer->addData(array_map('htmlspecialchars', $customerData))
                    ->save();

                $response = [
                    'success' => true,
                    'customer' => $this->getLayout()
                        ->createBlock('rockar_youdrive/list')
                        ->getCustomerData()
                ];
            } catch (Exception $e) {
                $response = [
                    'error' => true,
                    'message' => $this->__($e->getMessage())
                ];
                $this->setResponseHttpStatusCodeBadRequest();
            }
        } else {
            $response = [
                'error' => true,
                'message' => $this->__('Customer not logged in')
            ];
            $this->setResponseHttpStatusCodeUnauthorized();
        }

        return $this->sendJson($response);
    }

    /**
     * Cancel booking
     */
    public function cancelBookingAction()
    {
        $result = ['success' => false, 'message' => ''];
        $session = Mage::getSingleton('customer/session');

        if (!$session->isLoggedIn()) {
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }

        try {
            if ($session->isLoggedIn()) {
                $booking = Mage::getResourceModel('rockar_youdrive/booking_collection')
                    ->addCustomerFilter($session->getCustomer()->getId())
                    ->addIdFilter($this->getRequest()->getParam('bookingId'))
                    ->fetchItem();

                if ($booking && $booking->getId()) {
                    $booking->setStatus(Rockar_YouDrive_Helper_Booking_Status::BOOKING_STATUS_CANCELLED)
                        ->setIsCancelled(1)
                        ->save();

                    Mage::dispatchEvent('rockar_youdrive_cancel_booking_after', ['booking' => $booking]);

                    $result['success'] = true;
                    $result['bookingId'] = $this->getRequest()->getParam('bookingId');
                    $block = $this->getLayout()->createBlock('rockar_youdrive/customer_list');
                    $cancelledTestDrives = $block->getCancelledTestDrivesJson();
                    $result['cancelled_drives'] = Mage::helper('rockar_all')->jsonDecode($cancelledTestDrives);
                }
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            $this->setResponseHttpStatusCodeBadRequest();
        }

        $this->sendJson($result);
    }
}
