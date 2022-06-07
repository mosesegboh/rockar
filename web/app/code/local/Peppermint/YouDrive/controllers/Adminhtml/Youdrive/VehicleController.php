<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Youdrive
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once(Mage::getModuleDir('controllers',
        'Rockar_YouDrive') . DS . 'Adminhtml' . DS . 'Youdrive' . DS . 'VehicleController.php');

class Peppermint_YouDrive_Adminhtml_Youdrive_VehicleController extends Rockar_YouDrive_Adminhtml_Youdrive_VehicleController
{
    /**
     * save action
     */
    public function saveAction()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        $helper = Mage::helper('rockar_youdrive');

        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('rockar_youdrive/vehicle');

            if ($id = $this->getRequest()->getParam('id', false)) {
                $model->load($id);

                if (!$model->getId()) {
                    $this->_getSession()->addError(
                        $helper->__('This YouDrive Vehicle no longer exists.')
                    );

                    $this->_redirect('*/*/index');

                    return;
                }
            }

            // save model
            try {
                $productId = explode('/', $data['product']);
                $data['parent_id'] = $productId[1];
                $model->addData($data);
                $this->_getSession()->setFormData($data);
                $model->save();
                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess(
                    $helper->__('The YouDrive Vehicle has been saved.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            } catch (Exception $e) {
                $this->_getSession()->addError($helper->__('Unable to save the YouDrive Vehicle.'));
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                return;
            }
        }

        $this->_redirect('*/*/index');
    }

}