<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Block_Adminhtml_Promo_Quote_Grid extends Mage_Adminhtml_Block_Promo_Quote_Grid
{
    /**
     * Add websites to sales rules collection
     * Set collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var $collection Mage_SalesRule_Model_Mysql4_Rule_Collection */
        $collection = Mage::getModel('peppermint_salesrule/rulePending')
            ->getResourceCollection();

        $collection->addWebsitesToResult();
        $this->setCollection($collection);

        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumnAfter(
                'rule_website',
                [
                    'header' => Mage::helper('salesrule')->__('Website'),
                    'align' => 'left',
                    'index' => 'website_ids',
                    'type' => 'options',
                    'sortable' => false,
                    'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                    'width' => 200,
                    'filter_condition_callback' => [
                        $this,
                        'filterWebsites'
                    ],
                ],
                'is_active'
            );
        }

        $this->addColumnAfter(
            'simple_action',
            [
                'header' => Mage::helper('salesrule')->__('Rule Action'),
                'index' => 'simple_action',
                'type' => 'options',
                'width' => '150',
                'options' => [
                    Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => Mage::helper('salesrule')->__('Percent of product price discount'),
                    Mage_SalesRule_Model_Rule::BY_FIXED_ACTION => Mage::helper('salesrule')->__('Fixed amount discount'),
                    Mage_SalesRule_Model_Rule::CART_FIXED_ACTION => Mage::helper('salesrule')->__('Fixed amount discount for whole cart'),
                    Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION => Mage::helper('salesrule')->__('Buy X get Y free (discount amount is Y)'),
                ],
            ],
            'name'
        );

        $this->addColumnAfter(
            'discount_amount',
            [
                'header' => Mage::helper('salesrule')->__('Rule Discount'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'discount_amount',
                'renderer' => 'Peppermint_SalesRule_Block_Adminhtml_Promo_Quote_Renderer_DiscountAmount'
            ],
            'simple_action'
        );

        $this->addColumnAfter(
            'pending_action',
            [
                'header' => Mage::helper('salesrule')->__('Pending Action'),
                'index' => 'pending_action',
                'align' => 'right',
                'width' => 100,
            ],
            'to_date'
        );

        $this->addColumnAfter(
            'is_approved',
            [
                'header' => Mage::helper('salesrule')->__('Approval Status'),
                'index' => 'is_approved',
                'type' => 'options',
                'align' => 'right',
                'width' => 100,
                'options' => [
                    Mage::helper('adminhtml')->__('Pending Approval'),
                    Mage::helper('adminhtml')->__('Approved')
                ],
            ],
            'pending_action'
        );

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportExcel', $this->__('Excel XML'));
        $this->sortColumnsByOrder();

        return $this;
    }

    /**
     * Websites filter
     *
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function filterWebsites($collection, $column)
    {
        $filterValue = $column->getFilter()->getValue();

        if ($filterValue !== null) {
            $collection->addFieldToFilter(
                ['website_ids'],
                [
                    [
                        $filterValue,
                        ['finset' => $filterValue],
                    ]
                ]
            );
        }

        return $this;
    }
}
