<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Block_Adminhtml_Booking_Item_Grid extends Rockar_YouDrive_Block_Adminhtml_Booking_Item_Grid
{
    /**
     * Prepare collection to show in grid
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');

        $collection = Mage::getModel('rockar_youdrive/booking_item')
            ->getCollection();

        $collection->getSelect()
            ->joinLeft(
                ['booking' => $resource->getTableName('rockar_youdrive/booking')],
                'main_table.booking_id = booking.id',
                ['booked_on', 'booked_to', 'status', 'assigned_to', 'booking_store_id' => 'booking.store_id']
            )
            ->joinLeft(
                ['yd_vehicle' => $resource->getTableName('rockar_youdrive/vehicle')],
                'main_table.vehicle_id = yd_vehicle.id',
                ['product_id']
            )
            ->joinLeft(
                ['product' => $resource->getTableName('catalog/product')],
                'yd_vehicle.product_id = product.entity_id',
                ['variant' => 'SUBSTRING_INDEX(sku, "_", 1)']
            );

        Mage::dispatchEvent('rockar_youdrive_booking_item_grid_prepare_collection', ['collection' => $collection]);
        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}
