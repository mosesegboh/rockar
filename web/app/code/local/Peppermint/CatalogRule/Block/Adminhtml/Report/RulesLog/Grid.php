<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Block_Adminhtml_Report_RulesLog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Peppermint_CatalogRule_Block_Adminhtml_Report_RulesLog_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('rulesLog');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setRowClickCallback(null);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Peppermint_CatalogRule_Block_Adminhtml_Report_RulesLog_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('peppermint_catalogrule/rulesLog')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid Columns
     *
     * @return Peppermint_CatalogRule_Block_Adminhtml_Report_RulesLog_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'created_at',
            [
                'header' => $this->__('Date &amp; Time of Action'),
                'index' => 'created_at',
                'type' => 'datetime'
            ]
        );

        $this->addColumn(
            'username',
            [
                'header' => $this->__('Username'),
                'index' => 'username'
            ]
        );

        $this->addColumn(
            'full_name',
            [
                'header' => $this->__('Full Name'),
                'index' => 'full_name'
            ]
        );

        $this->addColumn(
            'action_type',
            [
                'header' => $this->__('Action Type'),
                'index' => 'action_type',
                'type' => 'options',
                'options' => [
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_CREATE => Peppermint_CatalogRule_Model_RulesLog::ACTION_CREATE,
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_UPDATE => Peppermint_CatalogRule_Model_RulesLog::ACTION_UPDATE,
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_DELETE => Peppermint_CatalogRule_Model_RulesLog::ACTION_DELETE,
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_APPROVE => Peppermint_CatalogRule_Model_RulesLog::ACTION_APPROVE
                ]
            ]
        );

        $this->addColumn(
            'rule_type',
            [
                'header' => $this->__('Rule Type'),
                'index' => 'rule_type',
                'type' => 'options',
                'options' => [
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_CATALOG_RULE => Peppermint_CatalogRule_Model_RulesLog::TYPE_CATALOG_RULE,
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_CART_RULE => Peppermint_CatalogRule_Model_RulesLog::TYPE_CART_RULE,
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_SHORTFALL_SUPPORT => Peppermint_CatalogRule_Model_RulesLog::TYPE_SHORTFALL_SUPPORT,
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_TRADE_IN => Peppermint_CatalogRule_Model_RulesLog::TYPE_TRADE_IN
                ]
            ]
        );

        $this->addColumn(
            'rule_id',
            [
                'header' => $this->__('Rule ID'),
                'index' => 'rule_id',
                'type' => 'number'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => $this->__('Name'),
                'index' => 'name'
            ]
        );

        $this->addColumn(
            'is_active',
            [
                'header' => $this->__('Status'),
                'index' => 'is_active',
                'type' => 'options',
                'options' => [
                    1 => Mage::helper('catalogrule')->__('Active'),
                    0 => Mage::helper('catalogrule')->__('Inactive')
                ]
            ]
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'website_ids',
                [
                    'header' => $this->__('Website'),
                    'align' => 'left',
                    'index' => 'website_ids',
                    'type' => 'options',
                    'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                    'filter_condition_callback' => [$this, 'filterWebsite']
                ]
            );
        }

        $this->addColumn(
            'from_date',
            [
                'header' => $this->__('Date Start'),
                'align' => 'left',
                'type' => 'date',
                'index' => 'from_date'
            ]
        );

        $this->addColumn(
            'to_date',
            [
                'header' => $this->__('Date Expire'),
                'align' => 'left',
                'type' => 'date',
                'index' => 'to_date'
            ]
        );

        $this->addColumn(
            'sort_order',
            [
                'header' => $this->__('Priority'),
                'index' => 'sort_order'
            ]
        );

        $this->addColumn(
            'sort_order',
            [
                'header' => $this->__('Priority'),
                'index' => 'sort_order'
            ]
        );

        $groups = Mage::getResourceModel('customer/group_collection')
            ->load()
            ->toOptionHash();

        $this->addColumn(
            'customer_group_ids', [
                'header' => $this->__('Customer Group'),
                'align' => 'left',
                'index' => 'customer_group_ids',
                'type' => 'options',
                'options' => $groups,
                'filter_condition_callback' => [$this, 'filterCustomerGroup']
            ]
        );

        $this->addColumn(
            'conditions_serialized',
            [
                'header' => $this->__('Conditions'),
                'index' => 'conditions_serialized',
                'renderer' => 'peppermint_catalogrule/adminhtml_report_rulesLog_renderer_serialized2Format'
            ]
        );

        $this->addColumn(
            'actions_serialized',
            [
                'header' => $this->__('Actions'),
                'index' => 'actions_serialized',
                'renderer' => 'peppermint_catalogrule/adminhtml_report_rulesLog_renderer_serialized2Format'
            ]
        );

        $this->addColumn(
            'simple_action',
            [
                'header' => $this->__('Rule Action'),
                'index' => 'simple_action',
                'type' => 'options',
                'options' => [
                    Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => $this->__('By Percentage of the Original/Product Price'),
                    Mage_SalesRule_Model_Rule::BY_FIXED_ACTION => $this->__('By Fixed Amount'),
                    Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION => $this->__('To Percentage of the Original Price'),
                    Mage_SalesRule_Model_Rule::TO_FIXED_ACTION => $this->__('To Fixed Amount'),
                    Mage_SalesRule_Model_Rule::CART_FIXED_ACTION => $this->__('Fixed amount discount for whole cart'),
                    Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION => $this->__('Buy X get Y free (discount amount is Y)')
                ]
            ]
        );

        $this->addColumn(
            'discount_amount',
            [
                'header' => $this->__('Rule Discount'),
                'align' => 'right',
                'index' => 'discount_amount',
                'renderer' => 'peppermint_catalogrule/adminhtml_report_rulesLog_renderer_discountAmount'
            ]
        );

        $this->addColumn(
            'stop_rules_processing',
            [
                'header' => $this->__('Stop Further Processing'),
                'index' => 'stop_rules_processing',
                'type' => 'options',
                'options' => [
                    1 => $this->__('Yes'),
                    0 => $this->__('No')
                ]
            ]
        );

        $this->addColumn(
            'coupon_code',
            [
                'header' => $this->__('Coupon Code'),
                'index' => 'coupon_code'
            ]
        );

        $this->addColumn(
            'uses_per_coupon',
            [
                'header' => $this->__('Uses Per Coupon'),
                'index' => 'uses_per_coupon'
            ]
        );

        $this->addColumn(
            'uses_per_customer',
            [
                'header' => $this->__('Uses Per Customer'),
                'index' => 'uses_per_customer'
            ]
        );

        $this->addColumn(
            'is_rss',
            [
                'header' => $this->__('RSS Feed'),
                'index' => 'is_rss',
                'type' => 'options',
                'options' => [
                    1 => $this->__('Yes'),
                    0 => $this->__('No')
                ]
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

    /**
     * Filter by website
     *
     * @param $collection
     * @param $column
     * @return $this
     */
    public function filterWebsite($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection->addFieldToFilter('website_ids', ['finset' => $value]);

        return $this;
    }

    /**
     * Filter by customer group
     *
     * @param $collection
     * @param $column
     * @return $this
     */
    public function filterCustomerGroup($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection->addFieldToFilter('customer_group_ids', ['finset' => $value]);

        return $this;
    }
}
