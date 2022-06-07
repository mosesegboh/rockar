<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Promo' . DS . 'QuoteController.php');

class Peppermint_SalesRule_Adminhtml_Promo_QuoteController extends Mage_Adminhtml_Promo_QuoteController
{
    /**
     * Initializes the rule
     */
    protected function _initRule()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('Shopping Cart Price Rules'));

        Mage::register('current_promo_quote_rule', Mage::getModel('peppermint_salesrule/rulePending'));
        $id = (int) $this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('rule_id')) {
            $id = (int) $this->getRequest()->getParam('rule_id');
        }

        if ($id) {
            Mage::registry('current_promo_quote_rule')->load($id);
        }
    }
    /**
     * Edit action
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('peppermint_salesrule/rulePending');
        $session = $this->_getSession();

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $session->addError(
                    Mage::helper('salesrule')->__('This rule no longer exists.'));
                $this->_redirect('*/*');

                return;
            }
        }

        if (!$model->getIsApproved() && $model->getId()) {
            $session->addNotice(
                Mage::helper('salesrule')->__(
                    'Rule is pending for %s approval.',
                    $model->getPendingAction() ?: Peppermint_CatalogRule_Model_RulesLog::ACTION_CREATE
                )
            );

            $session->addUniqueMessages($session->getMessages(true)->getItems());
        }

        $this->_title($model->getRuleId() ? $model->getName() : $this->__('New Rule'));

        // set entered data if was error when we do save
        $session->getPageData(true);

        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $model->getActions()->setJsFormObject('rule_actions_fieldset');

        Mage::register('current_promo_quote_rule', $model);

        $this->_initAction()->getLayout()->getBlock('promo_quote_edit')
            ->setData('action', $this->getUrl('*/*/save'));

        $this
            ->_addBreadcrumb(
                $id ? Mage::helper('salesrule')->__('Edit Rule')
                    : Mage::helper('salesrule')->__('New Rule'),
                $id ? Mage::helper('salesrule')->__('Edit Rule')
                    : Mage::helper('salesrule')->__('New Rule'))
            ->renderLayout();
    }

    /**
     * New condition HTML action
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        if (!$this->_validateRequestParams([$id, $type])) {
            if ($this->getRequest()->getQuery('id')) {
                $this->getRequest()->setQuery('id', '');
            }

            Mage::throwException(Mage::helper('adminhtml')->__('An error occurred while adding condition.'));
        }

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('peppermint_salesrule/rulePending'))
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
     * New Action HTML action
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    public function newActionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('peppermint_salesrule/rulePending'))
            ->setPrefix('actions');
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
     * Promo quote save action
     */
    public function saveAction()
    {
        $session = $this->_getSession();

        if ($this->getRequest()->getPost()) {
            try {
                /** @var $model Mage_SalesRule_Model_Rule */
                $model = Mage::getModel('peppermint_salesrule/rulePending');
                Mage::dispatchEvent(
                    'adminhtml_controller_salesrule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = $this->getRequest()->getPost();

                if (Mage::helper('adminhtml')->hasTags($data['rule'], ['attribute'], false)) {
                    Mage::throwException(Mage::helper('catalogrule')->__('Wrong rule specified'));
                }

                $data = $this->_filterDates($data, ['from_date', 'to_date']);
                $id = $this->getRequest()->getParam('rule_id');

                if ($id) {
                    $model->load($id);

                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('salesrule')->__('Wrong rule specified.'));
                    }
                }

                $validateResult = $model->validateData(new Varien_Object($data));

                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $session->addError($errorMessage);
                    }

                    $session->setPageData($data);
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                    return;
                }

                if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                    && isset($data['discount_amount'])) {
                    $data['discount_amount'] = min(100, $data['discount_amount']);
                }

                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }

                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }

                unset($data['rule']);
                $model->loadPost($data);

                $useAutoGeneration = (int) !empty($data['use_auto_generation']);
                $model->setUseAutoGeneration($useAutoGeneration);

                $session->setPageData($model->getData());

                $ruleLogActionType = $model->getId()
                    ? Peppermint_CatalogRule_Model_RulesLog::ACTION_UPDATE
                    : Peppermint_CatalogRule_Model_RulesLog::ACTION_CREATE;
                $this->savePending($model);
                $model->unsetData('created_at');

                Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                    $model,
                    $ruleLogActionType,
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_CART_RULE
                );

                $session->addSuccess(Mage::helper('salesrule')->__('The rule has been saved.'));
                $session->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                    return;
                }

                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
                $id = (int) $this->getRequest()->getParam('rule_id');

                if (!empty($id)) {
                    $this->_redirect('*/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('*/*/new');
                }

                return;
            } catch (Exception $e) {
                $session->addError(
                    Mage::helper('salesrule')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
                $session->setPageData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);

                return;
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        $session = $this->_getSession();

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('peppermint_salesrule/rulePending');
                $model->load($id);

                if (!$model->getRuleId()) {
                    $session->addError(
                        Mage::helper('salesrule')->__('Unable to find a rule to delete.')
                    );
                    $this->_redirect('*/*/');

                    return;
                }

                $model->setData('pending_action', Peppermint_CatalogRule_Model_RulesLog::ACTION_DELETE)
                    ->setData('is_approved', false)
                    ->save();

                Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                    $model,
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_DELETE,
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_CART_RULE
                );

                $session->addSuccess(
                    Mage::helper('salesrule')->__('The rule has been deleted.'));
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addError(
                    Mage::helper('salesrule')->__('An error occurred while deleting the rule. Please review the log and try again.'));
                Mage::logException($e);
                $this->_redirect('*/*/edit', ['id' => $id]);

                return;
            }
        }

        $session->addError(Mage::helper('salesrule')->__('Unable to find a rule to delete.'));
        $this->_redirect('*/*/');
    }

    /**
     * Export csv action
     */
    public function exportCsvAction()
    {
        $fileName = 'shopping_cart_price_rules.csv';
        $content = $this->getLayout()->createBlock('adminhtml/promo_quote_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export excel action
     */
    public function exportExcelAction()
    {
        $fileName = 'shopping_cart_price_rules.xml';
        $content = $this->getLayout()->createBlock('adminhtml/promo_quote_grid')
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Generate Coupons action
     *
     * @return void
     */
    public function generateAction()
    {
        parent::generateAction();
        $couponGeneratedSuccess = json_decode($this->getResponse()->getBody(), true);

        if (isset($couponGeneratedSuccess['messages'])) {
            $rule = Mage::registry('current_promo_quote_rule');
            $this->savePending($rule);
        };
    }

    /**
     * Coupons mass delete action
     *
     * @return void
     */
    public function couponsMassDeleteAction()
    {
        $this->_initRule();
        $rule = Mage::registry('current_promo_quote_rule');

        if (!$rule->getId()) {
            $this->_forward('noRoute');
        }

        $codesIds = $this->getRequest()->getParam('ids');

        if (is_array($codesIds)) {
            $couponsCollection = Mage::getResourceModel('peppermint_salesrule/couponPending_collection')
                ->addFieldToFilter('coupon_id', ['in' => $codesIds]);

            foreach ($couponsCollection as $coupon) {
                $coupon->delete();
            }

            $this->savePending($rule);
        }
    }

    /**
     * Approve action
     *
     * @return void
     */
    public function approveAction()
    {
        $session = $this->_getSession();

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $unapprovedRule = Mage::getModel('peppermint_salesrule/rulePending')->load($id);

                if (!$unapprovedRule->getRuleId()) {
                    $session->addError(
                        Mage::helper('salesrule')->__('Unable to find a rule to approve.')
                    );
                    $this->_redirect('*/*/');

                    return;
                }

                $approvedRule = Mage::getModel('salesrule/rule')->load($unapprovedRule->getId());

                if ($unapprovedRule->getPendingAction() === Peppermint_CatalogRule_Model_RulesLog::ACTION_DELETE) {
                    $unapprovedRule->delete();

                    Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                        $unapprovedRule,
                        Peppermint_CatalogRule_Model_RulesLog::ACTION_APPROVE,
                        Peppermint_CatalogRule_Model_RulesLog::TYPE_CART_RULE
                    );

                    if ($approvedRule->getRuleId()) {
                        $approvedRule->delete();
                    }

                    $session->addSuccess(
                        Mage::helper('salesrule')->__('The rule deletion has been approved.')
                    );
                    $this->_redirect('*/*/');

                    return;
                }

                $approvedRule->addData($unapprovedRule->getData());
                $unApprovedCoupons = $unapprovedRule->getCoupons();

                if ($unapprovedRule->hasStoreLabels()) {
                    $approvedRule->setStoreLabels($unapprovedRule->getStoreLabels());
                }

                $approvedRule->save();

                if ($approvedRule->getCoupons()) {
                    foreach ($approvedRule->getCoupons() as $coupon) {
                        $coupon->delete();
                    }
                }

                foreach ($unApprovedCoupons as $unApprovedCoupon) {
                    $coupon->setData($unApprovedCoupon->getData());
                    $coupon->save();
                }

                $unapprovedRule->setData('pending_action', null)
                    ->setData('is_approved', true)
                    ->save();

                Mage::getModel('peppermint_catalogrule/rulesLog')->processPriceRuleLogSave(
                    $unapprovedRule,
                    Peppermint_CatalogRule_Model_RulesLog::ACTION_APPROVE,
                    Peppermint_CatalogRule_Model_RulesLog::TYPE_CART_RULE
                );

                $session->addSuccess(
                    Mage::helper('salesrule')->__('The rule has been approved.')
                );
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addError(
                    Mage::helper('salesrule')->__('An error occurred while approving the rule. Please review the log and try again.')
                    . $e->getMessage()
                );
                Mage::logException($e);
                $this->_redirect('*/*/edit', ['id' => $id]);

                return;
            }
        }

        $session->addError(
            Mage::helper('salesrule')->__('Unable to find a rule to approve.')
        );

        $this->_redirect('*/*/');
    }

    /**
     * Save pending rule
     *
     * @param $rule
     * @return void
     */
    protected function savePending($rule): void
    {
        $approvedRule = Mage::getModel('salesrule/rule')->load($rule->getId());
        $pendingAction = $approvedRule->getId()
            ? Peppermint_CatalogRule_Model_RulesLog::ACTION_UPDATE
            : Peppermint_CatalogRule_Model_RulesLog::ACTION_CREATE;

        $rule->setIsApproved(false)
            ->setPendingAction($pendingAction)
            ->save();

        $this->_getSession()->addNotice(
            Mage::helper('salesrule')->__('Rule is pending for %s approval.', $pendingAction));
    }
}
