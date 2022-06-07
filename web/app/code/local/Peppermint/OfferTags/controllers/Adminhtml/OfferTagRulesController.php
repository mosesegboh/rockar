<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili<techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Adminhtml_OfferTagRulesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Handles index action, displays the grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage Offer Tag Rules'));
        $this->loadLayout();

        $this->_title('Add New Data');
        $this->_setActiveMenu('promo/offerTags');

        $this->renderLayout();
    }

    /**
     * Grid Action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Init action.
     *
     * @return void
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo/offerTags');

        $this->_title($this->__('Manage Offer Tag Rules'));

        return $this;
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
        $offerTagRuleId = $this->getRequest()->getParam('rule_id', false);
        $model = Mage::getModel('peppermint_offertags/offerTagRules');

        if ($offerTagRuleId) {
            $model->load($offerTagRuleId);

            if (!$model->getId()) {
                $this->_getSession()->addError(
                    $this->__('This item no longer exists.')
                );

                $this->_redirect('*/*/index');

                return false;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);

        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        Mage::register('peppermint_offertags', $model);

        $this->_initAction()->getLayout()->getBlock('offerTagRules')
            ->setData('action', $this->getUrl('*/*/save'));
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
            $model = Mage::getModel('peppermint_offertags/offerTagRules');

            try {
                $offerTagRuleId = $request->getParam('rule_id', false);

                if ($offerTagRuleId) {
                    $model->load($offerTagRuleId);

                    if (!$model->getId()) {
                        $this->_getSession()->addError(
                            $this->__('This item no longer exists.')
                        );
                        $this->_redirect('*/*/index');

                        return false;
                    }
                }

                $data = $this->_filterDates($data, ['from_date', 'to_date']);

                $validateResult = $model->validateData(new Varien_Object($data));

                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }

                    $this->_getSession()->setPageData($data);
                    $this->_redirect('*/*/edit', ['rule_id'=>$model->getId()]);

                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);

                $model->loadPost($data);

                // save
                $this->_getSession()->setFormData($data);
                $model->addData($data);
                $model->save();

                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess(
                    $this->__('Item has been saved.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Unable to save item.'));
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', ['rule_id' => $model->getId()]);

                return false;
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * deleteAction
     *
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('rule_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('peppermint_offertags/offerTagRules')->load($id);

                if (!$model->getId()) {
                    Mage::throwException($this->__('Unable to find record to delete.'));
                }

                $model->delete();

                $this->_getSession()->addSuccess(
                    $this->__('The record has been deleted.')
                );

                $this->_redirect('*/*/index');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('An error occurred while deleting record.')
                );
                Mage::logException($e);
            }

            $this->_redirect('*/*/edit', ['rule_id' => $id]);

            return;
        }

        $this->_getSession()->addError(
            $this->__('Unable to find record to delete.')
        );

        $this->_redirect('*/*/index');
    }

    /**
     * Manages condition rule row generation
     */
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('peppermint_offertags/offerTagRules'))
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }


    /**
     * Check the permission to run it
     *
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/peppermint_offertags/manage_offertagrules');
    }
}
