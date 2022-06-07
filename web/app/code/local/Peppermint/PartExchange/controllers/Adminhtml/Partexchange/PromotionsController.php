<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once(Mage::getModuleDir('controllers', 'Rockar_PartExchange') . DS . 'Adminhtml/Partexchange/PromotionsController.php');

class Peppermint_PartExchange_Adminhtml_Partexchange_PromotionsController extends Rockar_PartExchange_Adminhtml_Partexchange_PromotionsController
{
    /**
     * Edit action, sets up edit rule view
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function editAction()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('PX Promotion Rules'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('peppermint_partexchange/promotionsPending');

        if ($id) {
            $model->load($id);

            if (!$model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('rockar_partexchange/promotions')->__('This rule no longer exists.')
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

        Mage::register('current_rockar_partexchange_promotions_rule', $model);

        if ($model->getData('pending_action')) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                $this->__("Rule is pending for '%s' approval", $model->getData('pending_action'))
            );
        }

        $this->_initAction()->getLayout()->getBlock('partexchange_promotion_rule_edit')
            ->setData('action', $this->getUrl('*/partexchange_promotions/save'));

        $breadcrumb = $id
            ? Mage::helper('rockar_partexchange/promotions')->__('Edit Rule')
            : Mage::helper('rockar_partexchange/promotions')->__('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb)->renderLayout();
    }

    /**
     * Save actions, validates and saves rule
     *
     * @return void
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('peppermint_partexchange/promotionsPending');
                Mage::dispatchEvent(
                    'rockar_partexchange_promotion_rule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = $this->getRequest()->getPost();
                $data = $this->_filterDates($data, ['from_date', 'to_date']);

                if ($id = $this->getRequest()->getParam('rule_id')) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('rockar_partexchange/promotions')->__('Wrong rule specified.'));
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
                $data['actions'] = $data['rule']['actions'];
                unset($data['rule']);

                $autoApply = false;

                if (!empty($data['auto_apply'])) {
                    $autoApply = true;
                    unset($data['auto_apply']);
                }

                $model->loadPost($data);

                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());

                $ruleLogActionType = Peppermint_CatalogRule_Model_RulesLog::ACTION_CREATE;
                $rulePendingAction = Peppermint_PartExchange_Model_PromotionsPending::ACTION_CREATE;

                if ($model->getId()) {
                    $ruleLogActionType = Peppermint_CatalogRule_Model_RulesLog::ACTION_UPDATE;

                    $partexchangePromotionModel = Mage::getModel('rockar_partexchange/promotions_rule')->load($model->getId());

                    if ($partexchangePromotionModel->getId()) {
                        $rulePendingAction = Peppermint_PartExchange_Model_PromotionsPending::ACTION_UPDATE;
                    }
                }

                $model->setData('is_approved', 0)
                    ->setData('pending_action', $rulePendingAction)
                    ->save();

                $ruleType = Peppermint_CatalogRule_Model_RulesLog::TYPE_SHORTFALL_SUPPORT;

                if ($model->getScope() == 'trade_in') {
                    $ruleType = Peppermint_CatalogRule_Model_RulesLog::TYPE_TRADE_IN;
                }

                Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                    $model,
                    $ruleLogActionType,
                    $ruleType
                );

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rockar_partexchange/promotions')->__('The rule has been saved.')
                );
                Mage::getSingleton('adminhtml/session')->setPageData(false);

                if ($autoApply) {
                    $this->getRequest()->setParam('rule_id', $model->getId());
                    $this->_forward('applyRules');
                } else {
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                        return;
                    }

                    $this->_redirect('*/*/');
                }

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('rockar_partexchange/promotions')->__('An error occurred while saving the rule data. Please review the log and try again.')
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
     * Delete action, deletes the current rule
     *
     * @return void
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('peppermint_partexchange/promotionsPending');
                $model->load($id);
                $model->setData('is_approved', 0)
                    ->setData('pending_action', Peppermint_PartExchange_Model_PromotionsPending::ACTION_DELETE)
                    ->save();

                $ruleType = Peppermint_CatalogRule_Model_RulesLog::TYPE_SHORTFALL_SUPPORT;

                if ($model->getScope() == 'trade_in') {
                    $ruleType = Peppermint_CatalogRule_Model_RulesLog::TYPE_TRADE_IN;
                }

                Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                    $model,
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_DELETE,
                    $ruleType
                );

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rockar_partexchange/promotions')->__('The rule has been deleted.')
                );

                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('rockar_partexchange/promotions')->__('An error occurred while deleting the rule. Please review the log and try again.')
                );
                Mage::logException($e);

                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('rockar_partexchange/promotions')->__('Unable to find a rule to delete.')
        );

        $this->_redirect('*/*/');
    }

    /**
     * Approve Action
     *
     * @return void
     */
    public function approveAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('peppermint_partexchange/promotionsPending');
                $model->load($id);

                $promotionsRuleModel = Mage::getModel('rockar_partexchange/promotions_rule');
                $promotionsRuleModel->load($id);

                if (!$model->getRuleId()) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('rockar_partexchange/promotions')->__('Unable to find a rule to approve.')
                    );
                    $this->_redirect('*/*/');

                    return;
                }

                if ($model->getPendingAction() === Peppermint_PartExchange_Model_PromotionsPending::ACTION_DELETE) {
                    $model->delete();

                    if ($promotionsRuleModel->getRuleId()) {
                        $promotionsRuleModel->delete();
                    }
                } else {
                    $promotionsRuleModel->setData($model->getData());
                    $promotionsRuleModel->save();

                    $model->setData('is_approved', 1)
                        ->setData('pending_action')
                        ->save();
                }

                $ruleType = Peppermint_CatalogRule_Model_RulesLog::TYPE_SHORTFALL_SUPPORT;

                if ($model->getScope() == 'trade_in') {
                    $ruleType = Peppermint_CatalogRule_Model_RulesLog::TYPE_TRADE_IN;
                }

                Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                    $model,
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_APPROVE,
                    $ruleType
                );

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rockar_partexchange/promotions')->__('The rule has been approved.')
                );
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('rockar_partexchange/promotions')->__('An error occurred while approving the rule. Please review the log and try again.') . $e->getMessage()
                );
                Mage::logException($e);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('rockar_partexchange/promotions')->__('Unable to find a rule to approve.')
        );

        $this->_redirect('*/*/');
    }

    /**
     * Export csv action
     */
    public function exportCsvAction()
    {
        $fileName = 'partexchange_promotion_rules.csv';
        $content = $this->getLayout()->createBlock('peppermint_partexchange/adminhtml_promotions_rule_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export excel action
     */
    public function exportExcelAction()
    {
        $fileName = 'partexchange_promotion_rules.xml';
        $content = $this->getLayout()->createBlock('peppermint_partexchange/adminhtml_promotions_rule_grid')
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }
}
