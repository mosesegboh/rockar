<?php

/**
 * @category     Peppermint
 * @package      Peppermint_PartExchange
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
require_once(Mage::getModuleDir('controllers', 'Rockar_PartExchange') . DS . 'Adminhtml/Partexchange/ConditionsController.php');
class Peppermint_PartExchange_Adminhtml_Partexchange_ConditionsController extends Rockar_PartExchange_Adminhtml_Partexchange_ConditionsController
{
    /**
     * Slider Save Action
     * Restrict save condition rules if mapping not match "clean" value
     *
     * @return $this
     */
    public function sliderSaveAction()
    {
        $helper = Mage::helper('rockar_partexchange');
        
        if (!$data = $this->getRequest()->getPost(null, false)) {
            $this->_redirect('*/*/index', ['active_tab' => $this->_sliderActiveTab]);

            return;
        }
        /* @var $model Rockar_PartExchange_Model_Conditions_Slider */
        $id = $this->getRequest()->getParam('entity_id', false);
        $model = Mage::getModel('rockar_partexchange/conditions_slider')->load($id);

        if (!$model->getId() && $id) {
            Mage::getSingleton('adminhtml/session')->addError($helper->__('This rule no longer exists.'));
            $this->_redirect('*/*/index', ['active_tab' => $this->_sliderActiveTab]);

            return;
        }

        if (isset($data['mapping']) && $data['mapping'] != 'clean') {
            Mage::getSingleton('adminhtml/session')->addError($helper->__('Only clean mapping is allowed.'));
            $this->_redirect('*/*/sliderEdit', ['entity_id' => $model->getId(), '_current' => true]);

            return;
        }

        $model->setData($data);
        try {
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('The rule has been saved.'));
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/sliderEdit', ['entity_id' => $model->getId(), '_current' => true]);

                return;
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
        }

        $this->_redirect('*/*/index', ['active_tab' => $this->_sliderActiveTab]);
    }
}
