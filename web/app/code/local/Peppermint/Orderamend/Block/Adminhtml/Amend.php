<?php
/**
 * @category    Peppermint
 * @package     Peppermint\Orderamend
 * @author      Sergejs Plisko <techteam@rockar.com>
 * @copyright   Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Orderamend_Block_Adminhtml_Amend extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'peppermint_orderamend';
        $this->_controller = 'adminhtml_amend';
        $this->_headerText = $this->__('Incomplete Order Amendments');

        parent::__construct();
    }
}
