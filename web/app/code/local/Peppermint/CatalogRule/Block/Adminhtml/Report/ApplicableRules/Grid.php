<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Block_Adminhtml_Report_ApplicableRules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Peppermint_Customer_Block_Adminhtml_CustomersGrid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('applicableRules');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setRowClickCallback(null);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Peppermint_CatalogRule_Block_Adminhtml_Report_ApplicableRules_Grid
     */
    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        $date = Mage::getModel('core/date')->gmtDate('Y-m-d');
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'vin_number');

        $collection = Mage::getModel('peppermint_catalogrule/rule_product')->getCollection();
        $collection->getSelect()
            ->joinInner(
                ['rule_product_price' => $resource->getTableName('catalogrule/rule_product_price')],
                "main_table.product_id = rule_product_price.product_id AND 
                main_table.customer_group_id = rule_product_price.customer_group_id AND
                main_table.website_id = rule_product_price.website_id AND 
                rule_product_price.rule_date = '$date'",
                ['rule_price']
            )
            ->joinLeft(
                ['rule' => $resource->getTableName('catalogrule/rule')],
                'main_table.rule_id = rule.rule_id',
                ['name', 'from_date', 'to_date']
            )
            ->joinLeft(
                ['catalog' => $resource->getTableName('catalog/product')],
                "main_table.product_id = catalog.entity_id",
                ['type_id']
            )
            ->joinLeft(
                ['vin_attribute_table' => $attribute->getBackendTable()],
                "main_table.product_id = vin_attribute_table.entity_id AND vin_attribute_table.attribute_id={$attribute->getId()}",
                ['vin_number' => 'value']
            )
            ->joinLeft(
                ['configurables' => new Zend_Db_Expr('(
                    SELECT product_id, GROUP_CONCAT(parent_id) AS conf_ids
                    FROM catalog_product_super_link
                    GROUP BY product_id)'
                )],
                'main_table.product_id = configurables.product_id',
                ['conf_ids' => 'conf_ids']
            );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid Columns
     *
     * @return Peppermint_CatalogRule_Block_Adminhtml_Report_ApplicableRules_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('peppermint_catalogrule');

        $this->addColumn(
            'product_id',
            [
                'header' => $helper->__('Product ID'),
                'width' => '50',
                'index' => 'product_id',
                'filter_index' => 'main_table.product_id',
                'type' => 'number'
            ]
        );

        $this->addColumn(
            'vin_number',
            [
                'header' => $helper->__('VIN'),
                'width' => '50',
                'index' => 'vin_number',
                'filter_index' => 'vin_attribute_table.value'
            ]
        );

        $this->addColumn(
            'conf_ids',
            [
                'header' => $helper->__('Associated Configurable ID'),
                'width' => '50',
                'index' => 'conf_ids'
            ]
        );

        $this->addColumn(
            'rule_price',
            [
                'header' => $helper->__('Calculated Price'),
                'width' => '50',
                'index' => 'rule_price',
                'type' => 'price',
                'currency_code' => Mage::app()->getStore(0)->getBaseCurrency()->getCode()
            ]
        );

        $this->addColumn(
            'from_date',
            [
                'header' => $helper->__('Start Date'),
                'width' => '50',
                'index' => 'from_date',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'to_date',
            [
                'header' => $helper->__('End Date'),
                'width' => '50',
                'index' => 'to_date',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'rule_id',
            [
                'header' => $helper->__('Rule ID'),
                'width' => '50',
                'index' => 'rule_id',
                'type' => 'number',
                'filter_index' => 'main_table.rule_id'
            ]
        );

        $this->addColumn(
            'rule_name',
            [
                'header' => $helper->__('Rule Name'),
                'width' => '50',
                'index' => 'name'
            ]
        );

        $this->addColumn(
            'action_amount',
            [
                'header' => $helper->__('Rule Value'),
                'align' => 'right',
                'width' => '50',
                'index' => 'action_amount',
                'renderer' => 'Peppermint_CatalogRule_Block_Adminhtml_Report_ApplicableRules_Renderer_ActionAmount'
            ]
        );

        $this->addColumn(
            'action_operator',
            [
                'header' => $helper->__('Rule Type'),
                'width' => '50',
                'index' => 'action_operator',
                'type' => 'options',
                'options' => [
                    Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => $helper->__('By Percentage of the Original Price'),
                    Mage_SalesRule_Model_Rule::BY_FIXED_ACTION => $helper->__('By Fixed Amount'),
                    Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION => $helper->__('To Percentage of the Original Price'),
                    Mage_SalesRule_Model_Rule::TO_FIXED_ACTION => $helper->__('To Fixed Amount')
                ]
            ]
        );

        $groups = Mage::getResourceModel('customer/group_collection')
            ->load()
            ->toOptionHash();

        $this->addColumn('customer_group_id', [
            'header' => $helper->__('Customer Group'),
            'width' => '100',
            'index' => 'customer_group_id',
            'filter_index' => 'main_table.customer_group_id',
            'type' => 'options',
            'options' => $groups
        ]);

        parent::_prepareColumns();

        $this->addExportType('*/*/exportCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportExcel', $helper->__('Excel XML'));

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
