<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transactional
 * @author    Craig Goodspeed <craig.goodspeed@partner.bmw.co.za>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Helper_TestDrive extends Peppermint_Transactional_Helper_Abstract
{
    /**
     * SAP caters for 4 values, the values provided by DSP need to be mapped to
     * something that SAP will understand.
     *
     * SAP statuses
     * E0001 -- Open
     * E0002 -- Completed
     * E0003 -- Delete
     * E0004 -- Expired
     * @var array
     */
    private $_dspSapStatusMapping = [
        Rockar_YouDrive_Helper_Booking_Status::BOOKING_STATUS_COMPLETED =>  'E0002',
        Rockar_YouDrive_Helper_Booking_Status::BOOKING_STATUS_NOT_COMPLETED => 'E0001',
        Rockar_YouDrive_Helper_Booking_Status::BOOKING_STATUS_CANCELLED => 'E0003',
        Rockar_YouDrive_Helper_Booking_Status::BOOKING_STATUS_DUE => 'E0001',
        Rockar_YouDrive_Helper_Booking_Status::BOOKING_STATUS_COMPLETED_ORDERED => 'E0002',
        Rockar_YouDrive_Helper_Booking_Status::BOOKING_STATUS_COMPLETED_AT_DEALER => 'E0002',
        Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_REQUESTED => 'E0001'
    ];
    private $_bookingInformation = null;

    /**
     * Get JSON Data for sending to the abstract API call
     * @return array
     */
    public function getSapData()
    {
        $bookingInformation = $this->_getBookingInformation();
        $customerGcdmId = isset($bookingInformation['customer_id'])
            ? (Mage::getModel('peppermint_gcdm/customer_access')->load($bookingInformation['customer_id'])->getGcid() ?? '')
            : '';
        $bookingFrom = DateTime::createFromFormat('Y-m-d H:i:s', $bookingInformation['booked_on']);
        $bookingTo = DateTime::createFromFormat('Y-m-d H:i:s', $bookingInformation['booked_to']);
        $createdDate = $bookingInformation->getCreatedAt();
        $updatedDate = $bookingInformation->getUpdatedAt();
        $dealerId = Mage::helper('peppermint_all')->getDealerId();

        return [
            'transactionType' =>
            $bookingInformation['status'] === Peppermint_YouDrive_Helper_Booking_Status::BOOKING_STATUS_REQUESTED
                ? 'ZDS9'
                : 'ZDS4',
            'gcdmId' => $customerGcdmId,
            'status' => $this->_dspSapStatusMapping[$bookingInformation['status']],
            'processingMode' => ($createdDate && $updatedDate && $createdDate === $updatedDate)  ? 'A' : 'B',
            'productId' => $this->_getVistaOrderNumberFromBookingVehicleId($bookingInformation['id']) ?: '',
            'campaignId' => $this->_getCampaignCookie(),
            'transactionId' => '',
            'dealerCode' => strstr($bookingInformation['assigned_to'], '_', true) ?: 0,
            'dspWebStoreId' => $bookingInformation['store_id'] ?? 0,
            'externalId' => $bookingInformation->getId() ?? 0,
            'userid' => $dealerId,
            'dateList' => [[
                'dateFrom' => $this->_performDateTranslation($bookingFrom,'Y-m-d'),
                'dateTo' => $this->_performDateTranslation($bookingTo,'Y-m-d'),
                'technicalName' => '',
                'timeFrom' => $this->_performDateTranslation($bookingFrom,'H:i:s'),
                'timeTo' => $this->_performDateTranslation($bookingTo, 'H:i:s')
            ]]
        ];
    }

    /**
     * Set the data to be sent to SAP.
     *
     * @param array $bookingInformation, object as passed by the event object.
     * @return $this
     */
    public function setTestDriveData($bookingInformation)
    {
        $this->_bookingInformation = $bookingInformation;

        return $this;
    }

    /**
     * Get booking formation, as passed in from the event object
     *
     * @return array | null
     */
    protected function _getBookingInformation()
    {
        return $this->_bookingInformation;
    }

    /**
     * There is no information about this vehicle, the vehicle vista_order_number is expected
     * this method receives a booking identity and joins relevant tables to retrieve a vista_order_number
     *
     * @param String | Integer $bookingId, this booking identity
     * @return String the vista order number stored against this booking.
     */
    protected function _getVistaOrderNumberFromBookingVehicleId($bookingId)
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $vistaOrderAttribute = Mage::getModel('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'vista_order_number');

        $select = $connection->select()
            ->from(['booking' => $resource->getTableName('rockar_youdrive/booking')], [] )
            ->join(['vehicle' => $resource->getTableName('rockar_youdrive/vehicle')],
                'booking.vehicle_id = vehicle.id',
                []
            )
            ->join(['attr_var' => $resource->getTableName('catalog_product_entity_varchar')],
                'attr_var.entity_id = vehicle.product_id and attr_var.attribute_id = '.$vistaOrderAttribute->getId(),
                'value'
            )
            ->where('booking.id = ?', $bookingId);

        return $connection->fetchOne($select);
    }

    /**
     * Helper method to translate given object into a string representation of the date
     *
     * @param DateTime | Boolean $date the value to format
     * @param String $format to return the data in
     * @return String representation of the given date formatted to the provided format
     */
    private function _performDateTranslation($date, $format)
    {
        return $date ? $date->format($format) : '';
    }
}
