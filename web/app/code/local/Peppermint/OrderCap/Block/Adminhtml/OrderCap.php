<?php
/**
 * @category  Peppermint
 * @package   Peppermint\OrderCap
 * @author    Lika Sikharulia <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OrderCap_Block_Adminhtml_OrderCap extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'peppermint_ordercap';
        $this->_controller = 'adminhtml_orderCap';
        $this->_headerText = $this->__('Order Cap Report');
        parent::__construct();
        $this->_removeButton('add');
    }
}