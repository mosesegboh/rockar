<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Block_Adminhtml_Promo_Catalog_Grid extends Mage_Adminhtml_Block_Promo_Catalog_Grid
{
    /**
     * @inheritDoc
     */
    protected function _prepareCollection()
    {
        /** @var $collection Peppermint_CatalogRule_Model_Resource_RulePending_Collection */
        $collection = Mage::getModel('peppermint_catalogrule/rulePending')
            ->getResourceCollection();
        $this->setCollection($collection);

        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter(
            'simple_action',
            [
                'header' => Mage::helper('peppermint_catalogrule')->__('Rule Action'),
                'width' => '200px',
                'index' => 'simple_action',
                'type' => 'options',
                'options' => [
                    Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => Mage::helper('peppermint_catalogrule')->__('By Percentage of the Original Price'),
                    Mage_SalesRule_Model_Rule::BY_FIXED_ACTION => Mage::helper('peppermint_catalogrule')->__('By Fixed Amount'),
                    Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION => Mage::helper('peppermint_catalogrule')->__('To Percentage of the Original Price'),
                    Mage_SalesRule_Model_Rule::TO_FIXED_ACTION => Mage::helper('peppermint_catalogrule')->__('To Fixed Amount')
                ]
            ],
            'name'
        );

        $this->addColumnAfter(
            'discount_amount',
            [
                'header' => Mage::helper('peppermint_catalogrule')->__('Rule Discount'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'discount_amount',
                'renderer' => 'Peppermint_CatalogRule_Block_Adminhtml_Promo_Catalog_Renderer_DiscountAmount'
            ],
            'simple_action'
        );

        $this->addColumnAfter(
            'pending_action',
            [
                'header' => Mage::helper('peppermint_catalogrule')->__('Pending Action'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'pending_action',
            ],
            'to_date'
        );

        $this->addColumnAfter(
            'is_approved',
            [
                'header' => Mage::helper('peppermint_catalogrule')->__('Approval Status'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'is_approved',
                'type' => 'options',
                'options' => array(
                    '1' => Mage::helper('catalog')->__('Approved'),
                    '0' => Mage::helper('catalog')->__('Pending Approval'),
                ),
            ],
            'pending_action'
        );

        parent::_prepareColumns();

        $this->removeColumn('rule_website');

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumnAfter(
                'rule_website',
                [
                    'header' => Mage::helper('catalogrule')->__('Website'),
                    'align' => 'left',
                    'index' => 'website_ids',
                    'type' => 'options',
                    'sortable' => false,
                    'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                    'width' => 200,
                    'filter_condition_callback' => [$this, 'filterWebsite']
                ],
                'is_active'
            );
        }

        Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportExcel', $this->__('Excel XML'));

        return $this;
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
}
