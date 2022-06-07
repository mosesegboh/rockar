<?php

/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Terms
 */
class Peppermint_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Terms extends Rockar_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Terms
{
    /**
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->removeColumn('finance_facility_fee');
        $this->removeColumn('purchase_fee');
        $this->removeColumn('representative_apr');

        $this->addColumn(
            'individual_fee_monthly',
            [
                'header' => $this->_helper->__('Individual Fee Monthly'),
                'index' => 'individual_fee_monthly',
            ]
        );

        $this->addColumn(
            'individual_fee_capitalised',
            [
                'header' => $this->_helper->__('Individual Fee Capitalised'),
                'index' => 'individual_fee_capitalised',
            ]
        );

        $this->addColumn(
            'corporate_fee_monthly',
            [
                'header' => $this->_helper->__('Corporate Fee Monthly'),
                'index' => 'corporate_fee_monthly',
            ]
        );

        $this->addColumn(
            'corporate_fee_capitalised',
            [
                'header' => $this->_helper->__('Corporate Fee Capitalised'),
                'index' => 'corporate_fee_capitalised',
            ]
        );

        return $this;
    }
}
