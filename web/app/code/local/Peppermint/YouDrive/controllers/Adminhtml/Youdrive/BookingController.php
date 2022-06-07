<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Youdrive
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once(Mage::getModuleDir('controllers',
        'Rockar_YouDrive') . DS . 'Adminhtml' . DS . 'Youdrive' . DS . 'BookingController.php');

class Peppermint_YouDrive_Adminhtml_Youdrive_BookingController extends Rockar_YouDrive_Adminhtml_Youdrive_BookingController
{
    /**
     * save action
     * @return void
     */
    public function saveAction()
    {
        $request = $this->getRequest();

        $redirectBack = $request->getParam('back', false);
        $backUrlAction = $request->getParam('back_url_action', false);

        if ($data = $request->getPost()) {
            $booking = Mage::getModel('rockar_youdrive/booking');
            $id = $request->getParam('id', false);

            if ($id) {
                $booking->load($id);

                if (!$booking->getId()) {
                    $this->_getSession()->addError(
                        Mage::helper('rockar_youdrive')->__('This YouDrive Booking no longer exists.')
                    );

                    $this->_redirect('*/*/index');
                    return;
                }

                $data['assigned_to'] = $booking->getData('assigned_to');
            }

            // save model
            try {
                //Validate booking time and store changes
                /** @var Rockar_YouDrive_Helper_Data $helper */
                $helper = Mage::helper('rockar_youdrive');

                $data = $this->_filterDateTime($data, ['booked_on', 'booked_to']);

                // Existing bookings will have vehicle_ids already retrieved from DB
                if (!isset($data['vehicle_ids']) || !$data['vehicle_ids']) {
                    $data['vehicle_ids'] = $this->_prepareVehicleIds($this->_collectSelectedVehicles($data));
                }

                $params['modelIds'] = $data['vehicle_ids'];
                $params['localStoreCode'] = $data['assigned_to'];
                $params['bookingDatetime'] = $data['booked_on'];

                $data['vehicle_id'] = (int) reset($params['modelIds']);

                if (!array_key_exists('available_from', $data)) {
                    $data['available_from'] = [$helper->getAvailableFromDate($params['modelIds'])];
                }

                $params['availableFrom'] = $data['available_from'];

                if (!$helper->validateBookingByVehiclesAvailableDate($params)) {
                    $this->_getSession()->addError(($helper->__('One of selected Test drive vehicles is not available at selected booking date!')));
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/*/edit', ['id' => $booking->getId()]);

                    return;
                }

                if (!$data['booked_on'] && $data['booked_to'] && $id) {
                    $this->_getSession()->addError(($helper->__('Test drive booking date is not available!')));
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/*/edit', ['id' => $booking->getId()]);

                    return;
                } elseif ($data['booked_on'] || $data['booked_to'] || ($data['booked_on'] && $data['booked_to'])) {
                    if ((
                            !$id ||
                            $data['assigned_to'] != $booking->getData('assigned_to') ||
                            $data['booked_on'] != $booking->getData('booked_on')
                        ) &&
                        !$helper->validateBookingByTime($params)
                    ) {
                        $this->_getSession()->addError(($helper->__('Test drive booking date is not available!')));
                        $this->_getSession()->setFormData($data);
                        $this->_redirect('*/*/edit', ['id' => $booking->getId()]);

                        return;
                    }
                } elseif (!$data['booked_on'] && !$data['booked_to'] && $data['status'] != Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_REQUESTED) {
                    $this->_getSession()->addError(($helper->__('Test drive booking dates are empty!')));
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/*/edit', ['id' => $booking->getId()]);

                    return;
                }

                if ($data['assigned_to'] != $booking->getData('assigned_to') && !$helper->validateBookingByDealer($data)) {
                    $this->_getSession()->addError(($helper->__('Selected dealer doesn\'t have this vehicle!')));
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/*/edit', ['id' => $booking->getId()]);

                    return;
                }

                if ($data['status'] == Rockar_YouDrive_Helper_Booking_Status::BOOKING_STATUS_CANCELLED) {
                    $data['is_cancelled'] = 1;
                }

                if ($id && ($booking->getData('status') !== $data['status'])) {
                    $data['edited_by_admin'] = '1';
                }

                $booking->addData($data);
                $this->_getSession()->setFormData($data);

                $booking->save();

                Mage::dispatchEvent('rockar_youdrive_save_booking_after', ['booking' => $booking]);

                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess(
                    Mage::helper('rockar_youdrive')->__('The YouDrive Booking has been saved.')
                );

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('rockar_youdrive')->__('Unable to save the YouDrive Booking.'));
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', ['id' => $booking->getId()]);
                return;
            }
        }

        $this->_redirect(sprintf('*/*/%s', ($backUrlAction ?: 'index')));
    }
}
