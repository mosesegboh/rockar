<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Block_Adminhtml_Role_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * @var Mage_Core_Helper_Abstract
     */
    private $_adminHelper;

    /**
     * Peppermint_Admin_Block_Adminhtml_Role_Edit constructor.
     */
    public function __construct()
    {
        $this->_adminHelper = Mage::helper('peppermint_admin');
        parent::__construct();
        $this->_blockGroup = 'peppermint_admin';
        $this->_controller = 'adminhtml_role';

        $this->_updateButton(
            'save',
            'label',
            $this->_adminHelper->__('Save Role')
        );

        $this->_updateButton(
            'delete',
            'label',
            $this->_adminHelper->__('Delete Role')
        );

        $this->_addButton(
            'saveandcontinue',
            [
                'label' => $this->_adminHelper->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save'
            ],
            -100
        );

        $this->_formScripts[] = '
            function saveAndContinueEdit() {
                editForm.submit($(\'edit_form\').action+\'back/edit/\');
            }
        ';
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return $this->_adminHelper->__('Add New Role');
    }
}
