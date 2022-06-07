<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_ExperiencesRules_Edit_Tab_Coupons_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('couponCodesGrid');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $rule = Mage::registry('current_experiences_rule');

        /**
         * @var Peppermint_Experiences_Model_Resource_Coupon_Collection $collection
         */
        $collection = Mage::getResourceModel('peppermint_experiences/coupon_collection')
            ->addRuleToFilter($rule)
            ->addGeneratedCouponsFilter();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('code', [
            'header' => $this->__('Coupon Code'),
            'index'  => 'code'
        ]);

        $this->addColumn('created_at', [
            'header' => $this->__('Created On'),
            'index'  => 'created_at',
            'type'   => 'datetime',
            'align'  => 'center',
            'width'  => '160'
        ]);

        $this->addColumn('used', [
            'header'   => $this->__('Used'),
            'index'    => 'used',
            'width'    => '100',
            'type'     => 'options',
            'options'  => [
                Mage::helper('adminhtml')->__('No'),
                Mage::helper('adminhtml')->__('Yes')
            ],
            'renderer' => 'adminhtml/promo_quote_edit_tab_coupons_grid_column_renderer_used',
            'filter_condition_callback' => [
                Mage::getResourceModel('peppermint_experiences/coupon_collection'), 'addIsUsedFilterCallback'
            ]
        ]);

        $this->addColumn('times_used', [
            'header' => $this->__('Times Used'),
            'index'  => 'times_used',
            'width'  => '50',
            'type'   => 'number'
        ]);

        $this->addExportType('*/*/exportCouponsCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportCouponsXml', Mage::helper('customer')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Configure grid mass actions
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('coupon_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem('delete', [
             'label'=> Mage::helper('adminhtml')->__('Delete'),
             'url'  => $this->getUrl('*/*/couponsMassDelete', ['_current' => true]),
             'confirm' => $this->__('Are you sure you want to delete the selected coupon(s)?'),
             'complete' => 'refreshCouponCodesGrid'
        ]);

        return $this;
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/couponsGrid', ['_current'=> true]);
    }
}
