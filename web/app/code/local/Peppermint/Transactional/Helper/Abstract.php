<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transactional
 * @author    Donald Mailula <mailula.donald@partner.bmw.co.za>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Helper_Abstract extends Varien_Object
{
    /**
     * Check if the customer is logged in
     * @return boolean
    */
    public function getIsLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Make calls to child helper classes instantiated on the model observer
     * @return string
    */
    public function sendApi()
    {
        $this->_passCrmTransactionalData(json_encode(array_merge($this->getSapData(), $this->_getDealerFromCookie())));

        return $this;
    }

    /**
     * Make API call to APIGee
     * @param string $payload
     * @return string
    */
    protected function _passCrmTransactionalData($payload)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(['timeout' => 60])
            ->write(
                Zend_Http_Client::POST,
                Mage::getStoreConfig('peppermint_transactional/general/apigee_base_url'),
                '1.1',
                [
                    $this->_getApigeeApiKey(),
                    'Accept: application/json',
                    'Content-Type: application/json'
                ],
                $payload
            );
        $returnData = Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        return $this->$returnData;
    }

    /**
     * Convert error into JSON object when a user is not logged when doing transactions
     * @param string $message
     * @return array
    */
    protected function _getSapError($message)
    {
        return ['error' => $message];
    }

    /**
     * Get the APIGee API Key from Backend
     * @return string
    */
    private function _getApigeeApiKey()
    {
        return 'apikey: ' . Mage::getStoreConfig('peppermint_transactional/general/apigee_api_key');
    }

    /**
     * Retrieve the campaign cookie from the request object.
     * This is for user tracking and measuring campaign success.
     * @return null|string
     */
    protected function _getCampaignCookie()
    {
        $campaignCookie = Mage::getSingleton('core/cookie')->get('campaign_cookie');

        if (isset($campaignCookie) && $campaignCookie = json_decode($campaignCookie)) {
            return $campaignCookie->campaignId;
        }

        return null;
    }

    /**
     * Helper method to load the vista order number given the product entity id.
     *
     * @param integer|string $productId
     * @return string the simple product vista order number.
     */
    protected function _getVistaOrderNumber($productId)
    {
        return Mage::getResourceModel('catalog/product')->getAttributeRawValue(
            $productId,
            'vista_order_number',
            Mage::app()->getStore()->getId()
        );
    }

    /**
     * when the in store indicator is set the dealer code exists within the cookie
     * this function performs a check is the cookie available and returns the relevant
     * data
     *
     * @return array description of the data to be sent to sap.
     */
    protected function _getDealerFromCookie()
    {
        $dealer = Mage::getSingleton('core/cookie')->get(Rockar_All_Helper_Data::IN_STORE_COOKIE_NAME);

        if ($dealer) {
            $dealer = strpos($dealer, '_') !== false ? strstr($dealer, '_', true) : $dealer;

            return [
                'dspStore' => '1',
                'dspStoreDealer' => is_numeric($dealer) ? $dealer : '99999'
            ];
        }

        return [
            'dspStore' => '0',
            'dspStoreDealer' => ''
        ];
    }
}
