<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ShortfallAllowance_Adminhtml_Shortfall_AllowanceController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * New action.
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit action.
     *
     * @return boolean
     */
    public function editAction()
    {
        $shortfallId = $this->getRequest()->getParam('id', false);
        $model = Mage::getModel('peppermint_shortfallallowance/shortfall_allowance');

        if ($shortfallId) {
            $model->load($shortfallId);

            if (!$model->getId()) {
                $this->_getSession()->addError(
                    $this->__('This item no longer exists.')
                );

                $this->_redirect('*/*/index');

                return false;
            }
        }

        $data = $this->_getSession()->getFormData(true);

        if (!empty($data)) {
            if ($this->checkForDuplicates($shortfallId, $data)) {
                $this->_getSession()->addError($this->__('Duplicates found.'));
                $this->_redirect('*/*/edit', ['id' => $shortfallId]);

                return false;
            }

            $model->setData($data);
        }

        Mage::register('peppermint_shortfall_allowance', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Save action.
     *
     * @return boolean
     */
    public function saveAction()
    {
        $request = $this->getRequest();

        $redirectBack = $request->getParam('back', false);

        if ($data = $request->getPost()) {
            $model = Mage::getModel('peppermint_shortfallallowance/shortfall_allowance');

            try {
                $shortfallId = $request->getParam('id', false);

                if ($shortfallId) {
                    $model->load($shortfallId);

                    if (!$model->getId()) {
                        $this->_getSession()->addError(
                            $this->__('This item no longer exists.')
                        );
                        $this->_redirect('*/*/index');

                        return false;
                    }
                }

                if ($this->checkForDuplicates($shortfallId, $data)) {
                    $this->_getSession()->addError($this->__('Duplicates found.'));
                    $redirectBack = true;
                } else {
                    // save
                    $this->_getSession()->setFormData($data);

                    $data['models'] = implode(',', $data['models']);

                    $model->addData($data);
                    $model->save();

                    $this->_getSession()->setFormData(false);
                    $this->_getSession()->addSuccess(
                        $this->__('Item has been saved.')
                    );
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Unable to save item.'));
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                return false;
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Delete action.
     * @return void
     */
    public function deleteAction()
    {
        if (!$shortfallId = $this->getRequest()->getParam('id', false)) {
            $this->_getSession()->addError(
                $this->__('Unable to find an item to delete.')
            );

            $this->_redirect('*/*/index');

            return;
        }

        try {
            $model = Mage::getModel('peppermint_shortfallallowance/shortfall_allowance')->load($shortfallId);

            if (!$model->getId()) {
                Mage::throwException($this->__('Unable to find an item to delete.'));
            }
            $model->delete();

            $this->_getSession()->addSuccess(
                $this->__('The item has been deleted.')
            );

            $this->_redirect('*/*/index');

            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $this->__('An error occurred while deleting item.')
            );
            Mage::logException($e);
        }

        $this->_redirect('*/*/edit', ['id' => $shortfallId]);
    }

    /**
     * checkForDuplicates.
     *
     * @param integer $shortfallId
     * @param array $data
     * @return boolean
     */
    public function checkForDuplicates($shortfallId, $data)
    {
        $modelsToCheck = [];

        foreach ($data['models'] as $modelId) {
            $modelsToCheck[] = ['finset' => $modelId];
        }
        $duplicates = Mage::getModel('peppermint_shortfallallowance/shortfall_allowance')->getCollection()
            ->addFieldToFilter('models', $modelsToCheck)
            ->addFieldToFilter('id', ['neq' => $shortfallId])
            ->toArray();

        return !empty($duplicates['items']);
    }

    /**
     * Init action.
     *
     * @return void
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo');

        $this->_title($this->__('Manage Shortfall Allowance'));
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/peppermint_shortfallallowance');
    }
}
