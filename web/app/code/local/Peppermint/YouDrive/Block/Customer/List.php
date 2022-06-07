<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_YouDrive_Block_Customer_List
 */
class Peppermint_YouDrive_Block_Customer_List extends Rockar_YouDrive_Block_Customer_List
{
    /**
     * Add necessary data to collection
     *
     * @param Rockar_YouDrive_Model_Resource_Booking_Collection $collection
     * @return Rockar_YouDrive_Model_Resource_Booking_Collection $collection
     */
    protected function _prepareCollection($collection)
    {
        parent::_prepareCollection($collection);
        $collection->getSelect()
            ->joinLeft(
                ['y' => Mage::getSingleton('core/resource')->getTableName('rockar_youdrive/vehicle')],
                'y.id = main_table.vehicle_id',
                ['y.product_id', 'y.parent_id', 'y.is_active']
            );

        Mage::dispatchEvent('peppermint_youdrive_my_account_prepare_collection', ['collection' => $collection]);

        return $collection;
    }

    /**
     * Get test drive requests
     *
     * @return string
     */
    public function getTestDriveRequestsJson()
    {
        return $this->_testDrivesFilteredByStatus(
            Mage::helper('rockar_youdrive/booking_status')->getRequestedStatuses()
        );
    }

    /**
     * Add booking data
     *
     * @param $booking
     * @return array|mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _populateBookingData($booking)
    {
        $vehicles = $this->_populateBookingItemsData($booking);

        $data = [
            'time' => date('D M jS Y \- g:i A', strtotime($booking->getBookedOn())),
            'place' => $booking->getStoreName(),
            'store' => $this->getLocalStores($booking->getStoreCode()),
            'vehicles' => $vehicles,
            'vehicles_enabled' => $this->_checkVehicleEnabled($vehicles),
        ];

        return array_merge($booking->getData(), $data);
    }

    /**
     * Get you drive data
     *
     * @param $testDrive
     * @return array|bool
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _populateBookingItemsData($testDrive)
    {
        /** @var Rockar_YouDrive_Model_Resource_Booking_Item $testDriveItems */
        $testDriveItems = $testDrive->getItems();

        if (!$testDriveItems) {
            return false;
        }

        $return = [];

        foreach ($testDriveItems as $item) {
            $simpleProduct = Mage::getModel('catalog/product')->load($item->getData('product_id'));
            $image = ($simpleProduct->getId()) ?
                Mage::helper('rockar_catalog/images')->getFirstImage($simpleProduct,
                    Rockar_Catalog_Helper_Images::IMAGE_TYPE_EXTERIOR) :
                Mage::helper('rockar_youdrive')->getModelImage(Mage::app()->getStore()->getId(), $item['model']);

            $data = [
                'image' => $image,
                'title' => $item['title'],
                'model' => $item['subtitle'],
                'short_description' => $simpleProduct->getShortDescription(),
                'bodystyle' => $simpleProduct->getAttributeText('bodystyle') ?: '',
                'extras' => explode(',', $item['extras']),
                'enabled' => (
                    Mage::helper('rockar_youdrive/visibility')->isVehicleVisibleInYouDrive($simpleProduct)
                    && $testDrive->getData('is_active')
                ),
                'chooseUrl' => ''
            ];

            Mage::dispatchEvent('rockar_youdrive_prepare_test_drive_data',
                ['test_drive_model' => $testDrive, 'test_drive_data' => new Varien_Object($data), 'product' => $simpleProduct]);

            $return[] = $data;
        }

        return $return;
    }
}
