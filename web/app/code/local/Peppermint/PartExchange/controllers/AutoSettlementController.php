<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_PartExchange_AutoSettlementController
 */
class Peppermint_PartExchange_AutoSettlementController extends Mage_Core_Controller_Front_Action
{
    const HTTP_CODE_BAD_REQUEST = 400;

    /**
     * Get settlement quotes from api
     *
     * @return Mage_Core_Controller_Response_Http|Zend_Controller_Response_Abstract
     */
    public function getSettlementQuotesAction()
    {
        try {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $partExchange = Mage::helper('rockar_partexchange')->loadPartExchangeFromSession();

            if (!$customer->getId()) {
                Mage::throwException($this->__('User is not logged in.'));
            }

            if (!isset($partExchange->getData()['cap']['capid'])) {
                Mage::throwException($this->__('There is no Part Exchange data.'));
            }

            $capId = $partExchange->getData()['cap']['capid'];

            $response = Mage::helper('peppermint_partexchange/autoSettlement')->getSettlementQuotes(
                $customer->getSouthAfricanIdNumber(),
                $customer->getPrimaryNumber(),
                $customer->getEmail(),
                $capId
            );

            $settlementQuote = $response['settlementQuotes'] ?? [];

            $response = [
                'success' => !empty($settlementQuote),
                'settlementQuotes' => $settlementQuote,
                'error' => empty($settlementQuote) ? $this->__('No matches found.') : '',
                'errorCode' => empty($settlementQuote) ? 'no_matches' : '',
                'currentPxCapId' => $capId,
                'currentPxPlateYear' => $partExchange->getData('plate_year')
            ];
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            $this->getResponse()->setHeader('HTTP/1.1', self::HTTP_CODE_BAD_REQUEST, true);
        }

        return $this->_sendJsonResponse($response);
    }

    /**
     * Prepare and send data in JSON format
     *
     * @param [] $response
     * @return Mage_Core_Controller_Response_Http|Zend_Controller_Response_Abstract
     */
    protected function _sendJsonResponse($response)
    {
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('rockar_all')->jsonEncode($response));
    }
}

