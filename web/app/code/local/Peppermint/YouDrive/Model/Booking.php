<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Model_Booking extends Rockar_YouDrive_Model_Booking
{
    const EMAIL_TYPE_REQUEST_CONFIRMATION = 'youdrive_request_confirmation';

    const XML_PATH_REQUEST_CONFIRMATION_TEMPLATE = 'sales_email/youdrive_request/confirmation_template';
    const XML_PATH_REQUEST_ENABLED = 'sales_email/youdrive_request/enabled';
    const XML_PATH_REQUEST_SENDER = 'sales_email/youdrive_request/identity';
    const XML_PATH_REQUEST_COPY_TO = 'sales_email/youdrive_request/copy_to';
    const XML_PATH_REQUEST_COPY_METHOD = 'sales_email/youdrive_request/copy_method';
    const XML_PATH_REQUEST_COPY_TO_DEALER = 'sales_email/youdrive_request/send_copy_to_dealer';
    const XML_PATH_BOOKING_COPY_TO_DEALER = 'sales_email/youdrive_booking/send_copy_to_dealer';
    const XML_PATH_BOOKING_CANCELLED_COPY_TO_DEALER = 'sales_email/youdrive_notification/send_copy_to_dealer';

    /**
     * Rewrite of a parent function
     * to differentiate between different cases
     * when saving booking
     *
     * {@inheritDoc}
     */
    protected function _beforeSave()
    {
        $this
            ->setRelationFieldFromTmpField('vehicle_ids', 'vehicles_ids')
            ->setRelationFieldFromTmpField('customer_id', 'customer');

        if ($this->getData('booked_on') && !$this->getData('booked_to')) {
            $this->_verifyStoreId();
            $this->validate();
        }

        if (!$this->getData('booked_on') && !$this->getData('booked_to')) {
            $this->_verifyStoreId();
        }

        // additional check if state is "Requested". Change status to "Due", if true.
        if ($this->getData('booked_on') && $this->getData('booked_to')) {
            $this->_roundBookingDates();
            $this->_verifyStoreId();
            $this->validate();

            if ($this->getData('status') == Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_REQUESTED) {
                $this->setData('status', Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_DUE);
            }
        }

        $this->setData('updated_at', Mage::getModel('core/date')->date());

        if (null === $this->getData('created_at')) {
            $this->setData('created_at', $this->getData('updated_at'));
        }

        return Rockar_YouDrive_Model_Abstract::_beforeSave();
    }

    /**
     * Rewrite of parent function
     * to fix bug that always loads form fields
     * when saving booking
     *
     * {@inheritDoc}
     */
    protected function _afterSave()
    {
        if (!$this->getIsCancelled()) {
            /** @var Rockar_YouDrive_Model_Booking_Item $bookingItem */
            $bookingItem = Mage::getModel('rockar_youdrive/booking_item');

            /** @var Rockar_YouDrive_Model_Resource_Booking_Item_Collection $bookingItems */
            $bookingItems = $bookingItem->getCollection()
                ->addFieldToFilter('booking_id', $this->getId());

            /** @var Rockar_Catalog_Helper_Vehicle $helper */
            $helper = Mage::helper('rockar_catalog/vehicle');

            /** @var Rockar_YouDrive_Helper_Data $youdriveHelper */
            $youdriveHelper = Mage::helper('rockar_youdrive');

            //try to get items populated by form in case saving from form
            $itemIds = $this->getData('item_ids');

            //if not saving from form, get existing items
            if (empty($itemIds) || $this->getIsRebooking()) {
                foreach ($bookingItems as $bookingItem) {
                    $itemIds[$bookingItem->getVehicleId()] = $bookingItem->getId();
                }
            }

            $vehicleIds = $this->getData('vehicle_ids') ?: [];
            $mileageIn = $this->getData('mileage_in');
            $mileageOut = $this->getData('mileage_out');

            if (is_string($vehicleIds)) {
                $vehicleIds = explode(',', $vehicleIds);
            }

            $this->_removeItemVehicleIds = array_diff($bookingItems->getColumnValues('vehicle_id'), $vehicleIds);

            /* Total count = existing items + new items - items to be removed */
            $totalCount = count(
                array_diff(
                    array_unique(
                        array_merge(
                            $vehicleIds,
                            $bookingItems->getColumnValues('vehicle_id')
                        )
                    ),
                    $this->_removeItemVehicleIds
                ));

            if ($youdriveHelper->getVehiclesInBookingLimit() >= $totalCount) {
                $vehicles = Mage::getModel('rockar_youdrive/vehicle')->getCollection()
                    ->addFieldToFilter('id', ['in' => $vehicleIds]);

                foreach ($vehicles as $vehicle) {
                    $product = Mage::getModel('catalog/product')->load($vehicle->getProductId());

                    $extras = [];
                    foreach ($product->getOptions() as $option) {
                        foreach ($option->getValues() as $value) {
                            $extras[] = $value->getTitle();
                        }
                    }

                    $data = [
                        'booking_id' => $this->getId(),
                        'title' => ($helper->getTitle($product)) ? $helper->getTitle($product) : $product->getName(),
                        'subtitle' => ($helper->getSubTitle($product)) ? $helper->getSubTitle($product) : '',
                        'extras' => implode(',', $extras),
                        'model' => $product->getData(Mage::helper('rockar_all')->getModelAttribute()),
                        'vehicle_id' => $vehicle->getId(),
                        'mileage_in' => $mileageIn[$vehicle->getId()] ?? 0,
                        'mileage_out' => $mileageOut[$vehicle->getId()] ?? 0
                    ];

                    if (isset($itemIds[$vehicle->getId()])) {
                        $bookingItem->load($itemIds[$vehicle->getId()]);

                        if ($this->getIsRebooking()) {
                            foreach ($this->_rebookUnChangeItemData as $item) {
                                if (isset($data[$item])) {
                                    unset($data[$item]);
                                }
                            }
                        }
                        $bookingItem->addData($data);
                    } else {
                        $bookingItem->setData($data);
                    }
                    $bookingItem->save();
                }

                if ($this->isSendConfirmationEmail()) {
                    $this->queueConfirmationEmail();
                }
            } else {
                throw new Exception(
                    $youdriveHelper->__(
                        sprintf('Sorry, maximum of %s vehicle(-s) can be booked', $youdriveHelper->getVehiclesInBookingLimit())
                    ),
                    self::YOU_DRIVE_BOOKING_LIMIT_REACHED_CODE
                );
            }

            if ($this->getIsRebooking() && $this->_removeItemVehicleIds) {
                $items = $bookingItem->getCollection()
                    ->addFieldToFilter('booking_id', $this->getId())
                    ->addFieldToFilter('vehicle_id', ['in' => $this->_removeItemVehicleIds]);

                foreach ($items as $item) {
                    $item->delete();
                }
            }
        } else {
            if (!$this->getData('cancelled_notification_sent')) {
                $this->queueCancelledEmail();
            }
        }

        return Rockar_YouDrive_Model_Abstract::_afterSave();
    }

    /**
     * @throws Mage_Core_Exception
     * @return void
     */
    public function validate()
    {
        if ($this->getData('booked_on') >= $this->getData('booked_to')) {
            Mage::throwException(Mage::helper('rockar_youdrive')->__('Booked To is less or equal than Booked On'));
        }
    }

    /**
     * Set store id form the vehicle product
     *
     * @return Rockar_YouDrive_Model_Booking
     */
    protected function _verifyStoreId()
    {
        if (!$this->getStoreId() && $this->getVehicleId()) {
            $product = $this->getVehicle();
            
            if (
                $product &&  $product instanceOf Rockar_YouDrive_Model_Vehicle
                && $product->getId() && $product->getStoreIds()
            ) {
                $storeIds = $product->getStoreIds();
                $this->setStoreId(array_pop($storeIds));
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function queueConfirmationEmail()
    {
        $email = $this->getData('status') == Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_REQUESTED
            ? $email = static::EMAIL_TYPE_REQUEST_CONFIRMATION
            : $email = static::EMAIL_TYPE_BOOKING_CONFIRMATION;

        return $this->_queueEmail($email);
    }

    /**
     * Indicates if this is re-booking
     *
     * @return bool
     */
    protected function isReBooking()
    {
        return $this->getData('status') == Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_DUE
            && $this->getOrigData('status') == Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_CANCELLED;
    }

    /**
     * Whether to send TD confirmation email
     *
     * @return bool
     */
    protected function isSendConfirmationEmail(): bool
    {
        if ($this->isObjectNew() || $this->getData('send_confirmation') || $this->isReBooking()) {
            return true;
        }

        return $this->getData('status') == Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_DUE
            && ($this->dataHasChangedFor('booked_on') || $this->dataHasChangedFor('booked_to'));
    }
}
