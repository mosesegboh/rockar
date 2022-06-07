<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Block_Adminhtml_Role extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Peppermint_Admin_Block_Adminhtml_Role constructor.
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_role';
        $this->_blockGroup = 'peppermint_admin';
        parent::__construct();
        $helper = Mage::helper('peppermint_admin');
        $this->_headerText = $helper->__('Manage Roles');
        $this->_updateButton('add', 'label', $helper->__('Add New Role'));
    }
}
