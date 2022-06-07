<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$connection = $this->getConnection();
$this->startSetup();

try {
    /** @var Rockar_YouDrive_Model_Booking_Item $bookingItemModel */
    $bookingItemModel = Mage::getModel('rockar_youdrive/booking_item');

    /** @var Rockar_YouDrive_Model_Resource_Booking_Item_Collection $collection */
    $collection = $bookingItemModel->getCollection();

    //get all duplicate item_ids grouped by vehicle_id and booking_id
    $collection->getSelect()
        ->reset(Zend_Db_Select::COLUMNS)
        ->columns([
            'vehicle_id',
            'booking_id',
            'GROUP_CONCAT(id) AS item_ids'
        ])
        ->group(['vehicle_id', 'booking_id']);

    //check the item ids for all vehicles
    foreach ($collection as $vehicleBooking) {
        $itemIds = explode(',', $vehicleBooking->getItemIds());

        //remove first item, because we need to leave one
        array_shift($itemIds);

        //delete the rest
        foreach ($itemIds as $itemId) {
            $bookingItem = $bookingItemModel->load($itemId);

            if ($bookingItem) {
                $bookingItem->delete();
            }
        }
    }
} catch (Exception $e) {
    Mage::logException($e);
}

$this->endSetup();
