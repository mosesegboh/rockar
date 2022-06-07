<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

require_once(Mage::getModuleDir('controllers','Rockar2_PartExchange').DS.'CrudController.php');

/**
 * Class Peppermint_PartExchange_CrudController
 */
class Peppermint_PartExchange_CrudController extends Rockar2_PartExchange_CrudController
{
    /**
     * saveAction
     *
     * {@inheritDoc}
     */
    public function saveAction()
    {
        if (!$this->checkFormKey()) {
            return;
        }

        $params = $this->getRequest()->getParams();
        $pxSession = $this->_helper->loadPartExchangeFromSession();
        $partExchange = $this->_handlePartExchangeParams($pxSession, $params);

        $pxKeys = [
            Rockar_PartExchange_Helper_Data::PART_EXCHANGE_SESSION_KEY,
            Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY,
            Rockar_PartExchange_Helper_Data::RUNNING_COSTS_PART_EXCHANGE_KEY
        ];

        $reqPxId = $params['px_id'] ?? null;

        // Invalidate, invalid requests return stored value
        if (
            $pxSession &&
            $pxSession->getData('cap') &&
            isset($pxSession->getData('cap')['capid']) &&
            $this->getRequest()->getParam('cap_id') === 'NaN'
        ) {
            $partExchange = $pxSession;
        }

        if (!$reqPxId) {
            $this->_helper->removeCustomerPartExchange();
            $pxId = $this->_helper->saveCustomerPartExchange($partExchange);
            $partExchange->addData(['px_id' => $pxId]);
            Mage::dispatchEvent('peppermint_part_exchange_customer_tradein_save', [
                'part_exchange' => $partExchange
            ]);
        } else {
            $partExchange->addData(['px_id' => $reqPxId]);
            $pxId = $this->_helper->updateCustomerPartExchange($partExchange);
            Mage::dispatchEvent('peppermint_part_exchange_customer_tradein_edit', [
                'part_exchange' => $partExchange
            ]);
        }

        // Saving to session to reuse in other parts of website
        $this->_helper->savePartExchangeToSession($partExchange, $pxKeys);
        $this->_helper->updateCheckout($partExchange->getData());

        $expireDate = date("Y-m-d H:i:s", strtotime(
            $partExchange->getUpdatedAt()
                . ' + '
                . Mage::getStoreConfig(Rockar_PartExchange_Helper_PartExchangeExpiry::PART_EXCHANGE_EXPIRY_TIME_PATH)
                . ' day'
        ));

        Mage::dispatchEvent('rockar_partexchange_report_valuation', [
            'part_exchange' => $partExchange,
            'status' => Rockar_PartExchange_Model_Reports::STATUS_ACCEPTED
        ]);

        $this->sendJson(
            [
                'px_id' => $pxId,
                'total' => $partExchange->getPartExchangeValue(),
                'expire' => Mage::helper('core')->formatDate($expireDate, 'short', false)
            ]
        );
    }

    /**
     * Handles PX data and updates PX object
     *
     * @param varien_object $pxSession
     * @param array $params
     *
     * @return varien_object
     */
    protected function _handlePartExchangeParams($pxSession, $params)
    {
        return $pxSession->addData([
            'part_exchange_value' => $params['part_exchange_value'] ?? false,
            'updated_part_exchange_value' => $params['updated_part_exchange_value'] ?? false,
            'outstanding_finance' => $params['outstanding_finance'] ?? false,
            'outstanding_finance_settlement' => $params['outstanding_finance_settlement'] ?? false,
            'manual_outstanding_finance' => $params['manual_outstanding_finance'] ?? false,
            'selected_settlement_option' => $params['selected_settlement_option'] ?? false,
            'selected_settlement_quote_id' => $params['selected_settlement_quote_id'] ?? ''
        ]);
    }

    /**
     * Rewrites parent function to check form key
     *
     * {@inheritDoc}
     */
    public function resetAction()
    {
        if (!$this->checkFormKey()) {
            return;
        }

        parent::resetAction();
    }

    /**
     * Check if form key matches the one generated in session
     * and show error if it does not.
     *
     * @return bool
     */
    protected function checkFormKey()
    {
        if (!$this->_validateFormKey()) {
            $result = [
                'error' => true,
                'message' => $this->__('There was an error with form submit.')
            ];

            $this->sendJson($result);

            return false;
        }

        return true;
    }
}
