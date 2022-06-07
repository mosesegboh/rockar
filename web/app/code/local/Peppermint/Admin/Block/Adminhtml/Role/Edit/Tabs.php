<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Block_Adminhtml_Role_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * @var Mage_Core_Helper_Abstract
     */
    private $_adminHelper;

    /**
     * Peppermint_Admin_Block_Adminhtml_Role_Edit_Tabs constructor.
     */
    public function __construct()
    {
        $this->_adminHelper = Mage::helper('peppermint_admin');
        parent::__construct();
        $this->setId('role_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->_adminHelper->__('Role Information'));
    }

    /**
     * @throws Exception
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_role',
            [
                'label' => $this->_adminHelper->__('Role Info'),
                'title' => $this->_adminHelper->__('Role Info'),
                'content' => $this->getLayout()
                    ->createBlock('peppermint_admin/adminhtml_role_edit_tab_form')
                    ->toHtml()
            ]
        );

        return parent::_beforeToHtml();
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return Mage::registry('current_role');
    }
}
