<?php

/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Data
 */
class Peppermint_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Data extends Rockar_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Data
{
    /**
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter(
            'rate_subvention_value',
            [
                'header' => $this->_helper->__('Rate Subvention Value'),
                'index' => 'rate_subvention_value'
            ],
            'optional_final_payment'
        );

        $this->addColumnAfter(
            'rate_subvention_type',
            [
                'header' => $this->_helper->__('Rate Subvention Type'),
                'index' => 'rate_subvention_type'
            ],
            'rate_subvention_value'
        );

        $this->addColumnAfter(
            'max_balloon_percent',
            [
                'header' => $this->_helper->__('Maximum Balloon Percentage'),
                'index' => 'max_balloon_percent'
            ],
            'rate_subvention_type'
        );

        $this->addColumnAfter(
            'vehicle_type',
            [
                'header' => $this->_helper->__('Vehicle Type'),
                'index' => 'vehicle_type'
            ],
            'rate_subvention_type'
        );

        parent::sortColumnsByOrder();

        return $this;
    }
}
