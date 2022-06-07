<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('product_order_pricing');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setRowClickCallback(null);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Grid
     */
    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getResourceModel('peppermint_sales/report_productOrderPricing_collection');
        $modelCodeAttributeId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode(Mage_Catalog_Model_Product::ENTITY, 'model_code');
        $modelYearAttributeId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode(Mage_Catalog_Model_Product::ENTITY, 'model_year');
        $modelPriceAttributeId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode(Mage_Catalog_Model_Product::ENTITY, 'price');

        $collection->getSelect()
            ->joinLeft(
                ['sales_order' => $resource->getTableName('sales/order')],
                'main_table.entity_id = sales_order.entity_id',
                [
                    'customer_group_id',
                    'original_increment_id',
                    'pricing_rule_snapshot',
                    'completed_on',
                    'abs(sales_order.discount_amount) as discount_amount',
                    'coupon_rule_name'
                ]
            )->joinLeft(
                ['sales_order_item' => $resource->getTableName('sales/order_item')],
                'main_table.entity_id = sales_order_item.order_id AND sales_order_item.parent_item_id IS NULL',
                ['finance_data_variables', 'pricing_details_snapshot', 'published_to_ds_date']
            )->joinLeft(
                ['original_order' => $resource->getTableName('sales/order')],
                'sales_order.original_increment_id = original_order.increment_id',
                ['original_created_at' => 'created_at']
            )->joinLeft(
                ['shortfall' => $resource->getTableName('rockar_orderamend/pxPromotionRule_data')],
                'main_table.entity_id = shortfall.order_id',
                ['shortfall_limit']
            )->joinLeft(
                ['product_model_code' => $resource->getTableName('catalog_product_entity_varchar')],
                'product_model_code.attribute_id = ' . $modelCodeAttributeId .
                ' AND product_model_code.entity_id = product_id',
                ['value']
            )->joinLeft(
                ['eav_attribute_option_value' => $resource->getTableName('eav_attribute_option_value')],
                'eav_attribute_option_value.option_id = ' . 'product_model_code.value',
                ['model_code' => 'value']
            )->joinLeft(
                ['product_model_year' => $resource->getTableName('catalog_product_entity_varchar')],
                'product_model_year.attribute_id = ' . $modelYearAttributeId .
                ' AND product_model_year.entity_id = product_id',
                ['model_year' => 'value']
            )->joinLeft(
                ['model_price' => $resource->getTableName('catalog_product_entity_decimal')],
                'model_price.attribute_id = ' . $modelPriceAttributeId .
                ' AND model_price.entity_id = product_id',
                ['price' => 'value']
            )->joinLeft(
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
     * @return Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            [
                'header' => Mage::helper('sales')->__('Order Number'),
                'type' => 'text',
                'index' => 'increment_id',
                'filter_index' => 'main_table.increment_id'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => Mage::helper('sales')->__('Order Status'),
                'index' => 'status',
                'type' => 'options',
                'width' => '70px',
                'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
                'filter_index' => 'main_table.status'
            ]
        );

        $this->addColumn(
            'vin_number',
            [
                'header' => $this->__('VIN Number'),
                'index' => 'vin_number',
                'filter_index' => 'main_table.vin_number'
            ]
        );

        $attributeId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode(Mage_Catalog_Model_Product::ENTITY, 'model_code');
        $attr = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId)->getSource()->getAllOptions();

        $options = [];
        foreach ($attr as $option) {
            $options[$option['value']] = $option['label'];
        }

        $this->addColumn(
            'model_code',
            [
                'header' => $this->__('Model Code'),
                'index' => 'model_code',
                'type' => 'options',
                'filter_index' => 'product_model_code.value',
                'options' => $options
            ]
        );

        $attributeId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode(Mage_Catalog_Model_Product::ENTITY, 'vehicle_condition');
        $attr = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId)->getSource()->getAllOptions();

        $options = [];
        foreach ($attr as $option) {
            $options[$option['value']] = $option['label'];
        }

        $this->addColumn(
            'vehicle_condition',
            [
                'header' => $this->__('Vehicle Condition'),
                'index' => 'vehicle_condition',
                'filter_index' => 'main_table.vehicle_condition',
                'type' => 'options',
                'options' => $options
            ]
        );

        $this->addColumn(
            'model_year',
            [
                'header' => $this->__('Year Model'),
                'index' => 'model_year',
                'type' => 'number',
                'filter_index' => 'product_model_year.value',
            ]
        );

        $this->addColumn(
            'dealer_code',
            [
                'header' => $this->__('Delivery Dealer'),
                'index' => 'dealer_code',
                'filter_index' => 'main_table.dealer_code',
                'type' => 'options',
                'options' => Mage::getModel('rockar_localstores/adminhtml_system_config_source_localStores')->toArray()
            ]
        );

        $this->addColumn(
            'billing_name',
            [
                'header' => Mage::helper('sales')->__('Customer Name'),
                'index' => 'billing_name',
                'filter_index' => 'main_table.billing_name'
            ]
        );

        $this->addColumn(
            'finance_payment_title',
            [
                'header' => $this->__('Payment Method'),
                'index' => 'finance_payment_title',
                'filter_index' => 'main_table.finance_payment_title'
            ]
        );

        $this->addColumn(
            'new_list_price',
            [
                'header' => $this->__('New List Price'),
                'index' => 'price',
                'type' => 'price',
                'renderer' => 'Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_newListPrice',
                'filter' => false,
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'grand_total',
            [
                'header' => $this->__('Calculated Price'),
                'index' => 'grand_total',
                'type' => 'price',
                'filter_index' => 'main_table.grand_total',
                'currency_code' => Mage::app()->getStore(0)->getBaseCurrency()->getCode()
            ]
        );

        $this->addColumn(
            'amount_of_credit',
            [
                'header' => $this->__('Final Finance Price'),
                'align' => 'right',
                'index' => 'finance_data_variables',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_AmountOfCredit'
            ]
        );

        $this->addColumn(
            'pricing_rule_snapshot',
            [
                'header' => $this->__('Price Rules Applied'),
                'index' => 'pricing_rule_snapshot',
                'filter_index' => 'sales_order.pricing_rule_snapshot',
                'renderer' => 'Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_PriceRules'
            ]
        );

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', ['gt' => 0])
            ->load()
            ->toOptionHash();

        $this->addColumn(
            'customer_group_id',
            [
                'header' => $this->__('Customer Group'),
                'index' => 'customer_group_id',
                'filter_index' => 'sales_order.customer_group_id',
                'type' => 'options',
                'options' => $groups
            ]
        );

        $this->addColumn(
            'pricing_details_snapshot',
            [
                'header' => $this->__('Price Protection'),
                'index' => 'pricing_details_snapshot',
                'renderer' => 'Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_PricingSnapshot'
            ]
        );

        $this->addColumn(
            'coupon_discount_amount',
            [
                'header' => $this->__('Voucher Discount'),
                'index' => 'discount_amount',
                'type' => 'price',
                'currency_code' => Mage::app()->getStore(0)->getBaseCurrency()->getCode()
            ]
        );

        $this->addColumn(
            'coupon_name',
            [
                'header' => $this->__('Voucher Name'),
                'index' => 'coupon_rule_name'
            ]
        );

        $this->addColumn(
            'accessories',
            [
                'header' => $this->__('Accessories'),
                'index' => 'finance_data_variables',
                'type' => 'text',
                'renderer' => 'Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_AccessoriesPrice',
                'filter_index' => 'finance_data_variables',
                'filter' => false,
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'shortfall_limit',
            [
                'header' => $this->__('Shortfall Allowance Available'),
                'index' => 'shortfall_limit',
                'type' => 'price',
                'currency_code' => Mage::app()->getStore(0)->getBaseCurrency()->getCode()
            ]
        );

        $this->addColumn(
            'shortfall_applied',
            [
                'header' => $this->__('Shortfall Applied'),
                'index' => 'finance_data_variables',
                'align' => 'right',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_ShortfallApplied'
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

        $this->addColumn(
            'dsp_published_date',
            [
                'header' => $this->__('DSP Original Publish Date'),
                'index' => 'published_to_ds_date',
                'type' => 'datetime',
                'filter_index' => 'sales_order_item.published_to_ds_date'
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => $this->__('Purchased On'),
                'index' => 'created_at',
                'type' => 'datetime',
                'filter_index' => 'main_table.created_at'
            ]
        );

        $this->addColumn(
            'completed_on',
            [
                'header' => $this->__('Completed On'),
                'index' => 'completed_on',
                'type' => 'datetime',
                'filter_index' => 'sales_order.completed_on'
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
