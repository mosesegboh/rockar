<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_ExperiencesRules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('experiencesRulesGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsoluteGridUrl($params = [])
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('peppermint_experiences/experiencesRules')->getCollection();
        $this->setCollection($collection);
        $collection->addWebsitesToResult();

        Mage::dispatchEvent(
            'peppermint_experiences_adminhtml_experiencesrules_grid_prepare_collection',
            ['collection' => $collection]
        );

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', [
            'header' => $this->__('ID'),
            'index' => 'rule_id',
            'width'     => '50px'
        ]);

        $this->addColumn('name', [
            'header' => $this->__('Rule Name'),
            'index' => 'name'
        ]);

        $experiencesArray = Mage::helper('peppermint_experiences')->getExperiencesArray();
        unset($experiencesArray['']);

        $this->addColumn('experience_id', [
            'header' => $this->__('Rule Action'),
            'index' => 'experience_id',
            'type' => 'options',
            'options' => $experiencesArray
        ]);

        $this->addColumn('coupon_type', [
            'header' => $this->__('Coupon'),
            'index' => 'coupon_type',
            'type' => 'options',
            'options' => Mage::getModel('peppermint_experiences/experiencesRules')->getCouponTypes()
        ]);

        $this->addColumn('from_date', [
            'header' => $this->__('Date Start'),
            'index' => 'from_date',
            'type'  => 'date'
        ]);

        $this->addColumn('to_date', [
            'header' => $this->__('Date Expire'),
            'index' => 'to_date',
            'type'  => 'date'
        ]);

        $this->addColumn('is_active', [
            'header'    => $this->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => [
                1 => $this->__('Active'),
                0 => $this->__('Inactive')
            ]
        ]);

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('rule_website', [
                'header' => $this->__('Website'),
                'align' => 'left',
                'index' => 'website_ids',
                'type' => 'options',
                'sortable' => false,
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'width' => 200
            ]);
        }

        $this->addColumn('priority', [
            'header' => $this->__('Priority'),
            'index' => 'priority',
            'align' => 'right',
            'width' => '50px'
        ]);

        Mage::dispatchEvent(
            'peppermint_experiences_adminhtml_experiencesrules_grid',
            ['block' => $this]
        );

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['rule_id' => $row->getId()]);
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
