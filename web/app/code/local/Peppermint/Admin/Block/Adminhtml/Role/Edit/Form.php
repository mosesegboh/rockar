<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Block_Adminhtml_Role_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @throws Exception
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            [
                'id' => 'edit_form',
                'action' => $this->getUrl(
                    '*/*/save',
                    ['id' => $this->getRequest()->getParam('id')]
                ),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
