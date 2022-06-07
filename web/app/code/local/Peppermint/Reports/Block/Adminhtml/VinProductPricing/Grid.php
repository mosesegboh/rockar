<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Reports_Block_Adminhtml_VinProductPricing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Peppermint_Reports_Block_Adminhtml_VinProductPricing_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('vin_product_pricing');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setRowClickCallback(null);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Peppermint_Reports_Block_Adminhtml_VinProductPricing_Grid
     */
    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('peppermint_reports/vinProductPricingLog')->getCollection();
        $collection->getSelect()
            ->joinLeft(
            ['sales_additional_data' => $resource->getTableName('peppermint_sales_additional_data')],
            'main_table.vin_number = sales_additional_data.vin',
            ['rfs_date']
        );
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid Columns
     *
     * @return Peppermint_Reports_Block_Adminhtml_VinProductPricing_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => $this->__('ID'),
                'index' => 'id',
                'type' => 'number'
            ]
        );

        $this->addColumn(
            'vin_number',
            [
                'header' => $this->__('VIN Number'),
                'index' => 'vin_number'
            ]
        );

        $this->addColumn(
            'product_id',
            [
                'header' => $this->__('Product ID'),
                'index' => 'product_id',
                'type' => 'number'
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => $this->__('Action'),
                'index' => 'action',
                'type' => 'text'
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => $this->__('Created At'),
                'index' => 'created_at',
                'type' => 'datetime'
            ]
        );

        $currencyCode = Mage::app()->getStore(0)->getBaseCurrency()->getCode();

        $this->addColumn(
            'price',
            [
                'header' => $this->__('RRP'),
                'index' => 'price',
                'type' => 'price',
                'currency_code' => $currencyCode
            ]
        );

        $this->addColumn(
            'mplan_price',
            [
                'header' => $this->__('MPlan'),
                'index' => 'mplan_price',
                'type' => 'price',
                'currency_code' => $currencyCode
            ]
        );

        $this->addColumn(
            'co2_tax',
            [
                'header' => $this->__('CO2'),
                'index' => 'co2_tax',
                'type' => 'price',
                'currency_code' => $currencyCode
            ]
        );

        $this->addColumn(
            'final_price',
            [
                'header' => $this->__('Calculated Price'),
                'index' => 'final_price',
                'type' => 'price',
                'currency_code' => $currencyCode
            ]
        );

        $this->addColumn(
            'price_rules',
            [
                'header' => $this->__('Price Rules Applied'),
                'index' => 'price_rules',
                'renderer' => 'Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_PriceRules'
            ]
        );

        $groups = Mage::getResourceModel('customer/group_collection')
            ->load()
            ->toOptionHash();

        $this->addColumn(
            'customer_group_id',
            [
                'header' => $this->__('Customer Group'),
                'index' => 'customer_group_id',
                'type' => 'options',
                'options' => $groups
            ]
        );

        $this->addColumn(
            'published_to_ds_date',
            [
                'header' => $this->__('DS Published Date'),
                'index' => 'published_to_ds_date',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'vehicle_condition',
            [
                'header' => $this->__('Vehicle Condition'),
                'index' => 'vehicle_condition',
                'type' => 'options',
                'options' => Mage::getModel('peppermint_reports/vinProductPricingLog')
                    ->getSelectValues('vehicle_condition')
            ]
        );

        $this->addColumn(
            'cap_code',
            [
                'header' => $this->__('Asset ID'),
                'index' => 'cap_code',
                'type' => 'text'
            ]
        );

        $this->addColumn(
            'rfs_date',
            [
                'header' => $this->__('RFS Date'),
                'index' => 'rfs_date',
                'type' => 'date',
            ]
        );

        parent::_prepareColumns();

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportExcel', $this->__('Excel XML'));

        return $this;
    }

    /**
     * Return grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}