<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Block_Adminhtml_Promo_Quote_Edit_Tab_Coupons_Grid extends Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Coupons_Grid
{
    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $priceRule = Mage::registry('current_promo_quote_rule');

        /**
         * @var Peppermint_SalesRule_Model_Resource_CouponPending_Collection $collection
         */
        $collection = Mage::getResourceModel('peppermint_salesrule/couponPending_collection')
            ->addRuleToFilter($priceRule)
            ->addGeneratedCouponsFilter();

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * Define grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter(
            'used',
            [
                'header' => Mage::helper('salesrule')->__('Used'),
                'index' => 'times_used',
                'width' => '100',
                'type' => 'options',
                'options' => [
                    Mage::helper('adminhtml')->__('No'),
                    Mage::helper('adminhtml')->__('Yes')
                ],
                'renderer' => 'adminhtml/promo_quote_edit_tab_coupons_grid_column_renderer_used',
                'filter_condition_callback' => [
                    Mage::getResourceModel('peppermint_salesrule/couponPending_collection'), 'addIsUsedFilterCallback'
                ]
            ],
            'created_at'
        );

        $this->sortColumnsByOrder();

        return $this;
    }

    /**
     * Configure grid mass actions
     *
     * @return Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Coupons_Grid
     */
    protected function _prepareMassaction()
    {
        if (Mage::getSingleton('admin/session')->isAllowed('promo/quote/actions/coupons')) {
            return parent::_prepareMassaction();
        }

        return $this;
    }
}
