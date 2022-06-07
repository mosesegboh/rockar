<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ShortfallAllowance_Block_Adminhtml_Shortfall_Allowance_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('peppermint_shortfall_allowance_grid');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return Peppermint_ShortfallAllowance_Block_Adminhtml_Shortfall_Allowance_Grid
     */
    public function _prepareCollection()
    {
        $collection = Mage::getModel('peppermint_shortfallallowance/shortfall_allowance')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Return row url.
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    /**
     * _prepareColumns.
     *
     * @throws Exception
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id', 
            [
                'header' => $this->__('ID'),
                'index' => 'id',
                'width' => '10px'
            ]
        );

        $this->addColumn(
            'models', 
            [
                'header' => $this->__('Models'),
                'index' => 'models',
                'renderer' => 'Peppermint_ShortfallAllowance_Block_Adminhtml_Shortfall_Allowance_Renderer_Models'
            ]
        );

        $this->addColumn(
            'shortfall_limit', 
            [
                'header' => $this->__('Shortfall Limit'),
                'index' => 'shortfall_limit'
            ]
        );

        Mage::dispatchEvent('peppermint_shortfallallowance_default_shortfall_allowance_grid', ['grid' => $this]);

        return parent::_prepareColumns();
    }
}
