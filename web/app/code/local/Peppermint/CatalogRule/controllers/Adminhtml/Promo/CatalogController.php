<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Promo' . DS . 'CatalogController.php');

class Peppermint_CatalogRule_Adminhtml_Promo_CatalogController extends Mage_Adminhtml_Promo_CatalogController
{
    /**
     * Edit Action
     */
    public function editAction()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('Catalog Price Rules'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('peppermint_catalogrule/rulePending');

        if ($id) {
            $model->load($id);

            if (!$model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('catalogrule')->__('This rule no longer exists.')
                );
                $this->_redirect('*/*');

                return;
            }
        }

        $this->_title($model->getRuleId() ? $model->getName() : $this->__('New Rule'));

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);

        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        Mage::register('current_promo_catalog_rule', $model);

        if ($model->getData('pending_action')) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                $this->__("Rule is pending for '%s' approval", $model->getData('pending_action'))
            );
        }

        $this->_initAction()->getLayout()->getBlock('promo_catalog_edit')
            ->setData('action', $this->getUrl('*/promo_catalog/save'));

        $breadcrumb = $id
            ? Mage::helper('catalogrule')->__('Edit Rule')
            : Mage::helper('catalogrule')->__('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb)->renderLayout();
    }

    /**
     * Save Action
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('peppermint_catalogrule/rulePending');
                Mage::dispatchEvent(
                    'adminhtml_controller_catalogrule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = $this->getRequest()->getPost();

                if (Mage::helper('adminhtml')->hasTags($data['rule'], ['attribute'], false)) {
                    Mage::throwException(Mage::helper('catalogrule')->__('Wrong rule specified'));
                }

                $data = $this->_filterDates($data, ['from_date', 'to_date']);

                if ($id = $this->getRequest()->getParam('rule_id')) {
                    $model->load($id);

                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('catalogrule')->__('Wrong rule specified.'));
                    }
                }

                $validateResult = $model->validateData(new Varien_Object($data));

                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }

                    $this->_getSession()->setPageData($data);
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);

                $model->loadPost($data);

                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());

                $ruleLogActionType = Peppermint_CatalogRule_Model_RulesLog::ACTION_CREATE;
                $rulePendingAction = Peppermint_CatalogRule_Model_RulePending::ACTION_CREATE;

                if ($model->getId()) {
                    $ruleLogActionType = Peppermint_CatalogRule_Model_RulesLog::ACTION_UPDATE;

                    $catalogRuleModel = Mage::getModel('catalogrule/rule')->load($model->getId());

                    if ($catalogRuleModel->getId()) {
                        $rulePendingAction = Peppermint_CatalogRule_Model_RulePending::ACTION_UPDATE;
                    }
                }

                $model->setData('is_approved', 0);
                $model->setData('pending_action', $rulePendingAction);
                $model->save();

                Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                    $model,
                    $ruleLogActionType,
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_CATALOG_RULE
                );

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('catalogrule')->__('The rule has been saved.')
                );
                Mage::getSingleton('adminhtml/session')->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                    return;
                }

                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('catalogrule')->__('An error occurred while saving the rule data. Please review the log and try again.')
                );
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);

                return;
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Delete Action
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('peppermint_catalogrule/rulePending');
                $model->load($id);

                if (!$model->getRuleId()) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('catalogrule')->__('Unable to find a rule to delete.')
                    );
                    $this->_redirect('*/*/');

                    return;
                }

                $model->setData('is_approved', 0);
                $model->setData('pending_action', Peppermint_CatalogRule_Model_RulePending::ACTION_DELETE);
                $model->save();

                Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                    $model,
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_DELETE,
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_CATALOG_RULE
                );

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('catalogrule')->__('The rule has been deleted.')
                );
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('catalogrule')->__('An error occurred while deleting the rule. Please review the log and try again.')
                );
                Mage::logException($e);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('catalogrule')->__('Unable to find a rule to delete.')
        );

        $this->_redirect('*/*/');
    }

    /**
     * Approve Action
     */
    public function approveAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('peppermint_catalogrule/rulePending');
                $model->load($id);

                $catalogRuleModel = Mage::getModel('catalogrule/rule');
                $catalogRuleModel->load($id);

                if (!$model->getRuleId()) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('catalogrule')->__('Unable to find a rule to approve.')
                    );
                    $this->_redirect('*/*/');

                    return;
                }

                if ($model->getPendingAction() === Peppermint_CatalogRule_Model_RulePending::ACTION_DELETE) {
                    $model->delete();

                    if ($catalogRuleModel->getRuleId()) {
                        $catalogRuleModel->delete();
                    }
                } else {
                    $catalogRuleModel->setData($model->getData());
                    $catalogRuleModel->save();

                    $model->setData('is_approved', 1);
                    $model->setData('pending_action', NULL);
                    $model->save();
                }

                Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                    $model,
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_APPROVE,
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_CATALOG_RULE
                );

                Mage::getModel('catalogrule/flag')->loadSelf()
                    ->setState(1)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('catalogrule')->__('The rule has been approved.')
                );
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('catalogrule')->__('An error occurred while approving the rule. Please review the log and try again.') . $e->getMessage()
                );
                Mage::logException($e);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('catalogrule')->__('Unable to find a rule to approve.')
        );

        $this->_redirect('*/*/');
    }
    
    /**
     * Export csv action
     */
    public function exportCsvAction()
    {
        $fileName = 'catalog_price_rules.csv';
        $content = $this->getLayout()->createBlock('adminhtml/promo_catalog_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export excel action
     */
    public function exportExcelAction()
    {
        $fileName = 'catalog_price_rules.xml';
        $content = $this->getLayout()->createBlock('adminhtml/promo_catalog_grid')
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }
}
