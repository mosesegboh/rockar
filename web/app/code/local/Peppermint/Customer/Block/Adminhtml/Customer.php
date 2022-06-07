<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Adminhtml_Customer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Peppermint_Customer_Block_Adminhtml_Customer constructor.
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_customer';
        $this->_blockGroup = 'peppermint_customer';
        $this->_headerText = Mage::helper('customer')->__('Assign a Customer Group');
        parent::__construct();
        $this->_removeButton('add');
    }
}
