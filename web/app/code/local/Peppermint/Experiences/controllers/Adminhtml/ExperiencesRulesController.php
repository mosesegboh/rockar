<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Adminhtml_ExperiencesRulesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Controller predispatch method
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function preDispatch()
    {
        $this->_setForcedFormKeyActions('delete');

        return parent::preDispatch();
    }

    /**
     * Initializes the current rule
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    protected function _initRule()
    {
        Mage::register('current_experiences_rule', Mage::getModel('peppermint_experiences/experiencesRules'));
        $id = (int) $this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('rule_id')) {
            $id = (int) $this->getRequest()->getParam('rule_id');
        }

        if ($id) {
            Mage::registry('current_experiences_rule')->load($id);
        }
    }

    /**
     * Init action.
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo/experiences');

        $this->_title($this->__('Manage Experience Rules'));

        return $this;
    }

    /**
     * Handles index action, displays the grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage Experience Rules'));
        $this->loadLayout();

        $this->_title('Add New Data');
        $this->_setActiveMenu('promo/experiences');

        $this->renderLayout();
    }

    /**
     * Coupon codes grid
     */
    public function couponsGridAction()
    {
        $this->_initRule();
        $this->loadLayout()->renderLayout();
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
        $experiencesRuleId = $this->getRequest()->getParam('rule_id', false);
        $model = Mage::getModel('peppermint_experiences/experiencesRules');

        if ($experiencesRuleId) {
            $model->load($experiencesRuleId);

            if (!$model->getId()) {
                $this->_getSession()->addError(
                    $this->__('This item no longer exists.')
                );

                $this->_redirect('*/*/index');

                return false;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        Mage::register('current_experiences_rule', $model);

        $this->_initAction()->getLayout();

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
            $model = Mage::getModel('peppermint_experiences/experiencesRules');

            try {
                $experiencesRuleId = $request->getParam('rule_id', false);

                if ($experiencesRuleId) {
                    $model->load($experiencesRuleId);

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
                    foreach ($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }

                    $this->_getSession()->setPageData($data);
                    $this->_redirect('*/*/edit', ['rule_id' => $model->getId()]);

                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
                $data['use_auto_generation'] = (int) isset($data['use_auto_generation']);

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
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('rule_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('peppermint_experiences/experiencesRules')->load($id);

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
            ->setRule(Mage::getModel('peppermint_experiences/experiencesRules'))
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
     * Coupons mass delete action
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    public function couponsMassDeleteAction()
    {
        $this->_initRule();
        $rule = Mage::registry('current_experiences_rule');

        if (!$rule->getId()) {
            $this->_forward('noRoute');
        }

        $codesIds = $this->getRequest()->getParam('ids');

        if (is_array($codesIds)) {
            $couponsCollection = Mage::getResourceModel('peppermint_experiences/coupon_collection')
                ->addFieldToFilter('coupon_id', ['in' => $codesIds]);

            foreach ($couponsCollection as $coupon) {
                $coupon->delete();
            }
        }
    }

    /**
     * Generate Coupons action
     */
    public function generateAction()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noRoute');

            return;
        }

        $result = [];
        $this->_initRule();

        /** @var $rule Peppermint_Experiences_Model_ExperiencesRules */
        $rule = Mage::registry('current_experiences_rule');

        if (!$rule->getId()) {
            $result['error'] = $this->__('Rule is not defined');
        } else {
            try {
                $data = $this->getRequest()->getParams();

                if (!empty($data['to_date'])) {
                    $data = array_merge($data, $this->_filterDates($data, ['to_date']));
                }

                /** @var $generator Peppermint_Experiences_Model_Coupon_Massgenerator */
                $generator = $rule->getCouponMassGenerator();

                if (!$generator->validateData($data)) {
                    $result['error'] = $this->__('Not valid data provided');
                } else {
                    $generator->setData($data);
                    $generator->generatePool();
                    $generated = $generator->getGeneratedCount();
                    $this->_getSession()->addSuccess($this->__('%s Coupon(s) have been generated', $generated));
                    $this->_initLayoutMessages('adminhtml/session');
                    $result['messages'] = $this->getLayout()->getMessagesBlock()->getGroupedHtml();
                }
            } catch (Mage_Core_Exception $e) {
                $result['error'] = $e->getMessage();
            } catch (Exception $e) {
                $result['error'] = $this->__('An error occurred while generating coupons. Please review the log and try again.');
                Mage::logException($e);
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Export coupon codes as excel xml file
     *
     * @return void
     */
    public function exportCouponsXmlAction()
    {
        $this->_initRule();
        $rule = Mage::registry('current_experiences_rule');

        if ($rule->getId()) {
            $fileName = 'coupon_codes.xml';
            $content = $this->getLayout()
                ->createBlock('peppermint_experiences/adminhtml_experiencesRules_edit_tab_coupons_grid')
                ->getExcelFile($fileName);
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            $this->_redirect('*/*/detail', ['_current' => true]);
        }
    }

    /**
     * Export coupon codes as CSV file
     *
     * @return void
     */
    public function exportCouponsCsvAction()
    {
        $this->_initRule();
        $rule = Mage::registry('current_experiences_rule');

        if ($rule->getId()) {
            $fileName = 'coupon_codes.csv';
            $content = $this->getLayout()
                ->createBlock('peppermint_experiences/adminhtml_experiencesRules_edit_tab_coupons_grid')
                ->getCsvFile();
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            $this->_redirect('*/*/detail', ['_current' => true]);
        }
    }

    /**
     * Check the permission to run it
     *
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/peppermint_experiences/manage_experiencesrules');
    }
}
