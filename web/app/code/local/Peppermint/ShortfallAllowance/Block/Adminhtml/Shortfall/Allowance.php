<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ShortfallAllowance_Block_Adminhtml_Shortfall_Allowance extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_shortfall_allowance';
        $this->_blockGroup = 'peppermint_shortfallallowance';
        $this->_headerText = $this->__('Manage Shortfall Allowance');
        $this->_addButtonLabel = $this->__('Create New');
        parent::__construct();
    }
}
