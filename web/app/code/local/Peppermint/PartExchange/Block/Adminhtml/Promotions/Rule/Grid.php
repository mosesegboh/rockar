<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Block_Adminhtml_Promotions_Rule_Grid extends Rockar_PartExchange_Block_Adminhtml_Promotions_Rule_Grid
{
    /**
     * @inheritDoc
     */
    protected function _prepareCollection()
    {
        /** @var $collection Peppermint_PartExchange_Model_Resource_PromotionsPending_Collection */
        $collection = Mage::getModel('peppermint_partexchange/promotionsPending')
            ->getCollection();
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
                'header' => Mage::helper('peppermint_partexchange')->__('Rule Action'),
                'align' => 'right',
                'width' => '150px',
                'index' => 'simple_action',
                'type' => 'options',
                'options' => [
                    Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => Mage::helper('rockar_partexchange/promotions')->__('Percent of product price'),
                    Mage_SalesRule_Model_Rule::BY_FIXED_ACTION => Mage::helper('rockar_partexchange/promotions')->__('By Fixed Amount'),
                ],
            ],
            'name'
        );

        $this->addColumnAfter(
            'discount_amount',
            [
                'header' => Mage::helper('peppermint_partexchange')->__('Rule Discount'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'discount_amount',
                'renderer' => 'Peppermint_PartExchange_Block_Adminhtml_Promotions_Rule_Renderer_DiscountAmount'
            ],
            'simple_action'
        );

        $this->addColumnAfter(
            'pending_action',
            [
                'header' => Mage::helper('peppermint_partexchange')->__('Pending Action'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'pending_action'
            ],
            'to_date'
        );

        $this->addColumnAfter(
            'approval_status',
            [
                'header' => Mage::helper('peppermint_partexchange')->__('Approval Status'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'is_approved',
                'type' => 'options',
                'options' => [
                    0 => $this->__('Pending Approval'),
                    1 => $this->__('Approved')
                ]
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

    /**
     * Rewrite of parent method to remove mass actions
     *
     * @return $this|Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        return $this;
    }
}
