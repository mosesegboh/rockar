<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Alexander Metzgen <alexander.metzgen@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Adminhtml_Customer_Customer_Grid extends Rockar_Localstores_Block_Adminhtml_Customer_Grid
{
    /**
     * Override the _prepareColumns method to add a new column after the 'entity_id' column
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter(
            'registered_in',
            [
                'header'    => Mage::helper('customer')->__('Account Created'),
                'width'     => '100px',
                'index'     => 'registered_in',
                'type'      => 'options',
                'options'   => Mage::helper('peppermint_customer')->getRegistrationValues()
            ],
            'entity_id'
        );

        Mage::dispatchEvent('customer_grid_prepare_before', ['customer_grid' => $this]);

        return parent::_prepareColumns();
    }

    /**
     * Rewrite of parent function to call event
     * for adding custom attributes in the observer
     *
     * {@inheritDoc}
     */
    public function setCollection($collection)
    {
        Mage::dispatchEvent('customer_grid_collection_set_before', ['collection' => $collection]);

        parent::setCollection($collection);
    }
}
