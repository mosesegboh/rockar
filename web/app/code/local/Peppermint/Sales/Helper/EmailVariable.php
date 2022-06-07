<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Helper_EmailVariable extends Mage_Core_Helper_Abstract
{
    protected $_dateTimeFormat = 'd-m-Y H:i:s';

    /**
     * Add additional email varibles
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param Varien_Object $templateVars
     * @return void
     */
    public function addAdditionalEmailVariables(Mage_Sales_Model_Order_Item $orderItem, $templateVars)
    {
        $order = $orderItem->getOrder();
        $templateVars->addData(
            array_merge(
                $this->_getLocalStoreVariables($order),
                $this->_getCollectionDateTimeVariable($order)
            )
        );
    }

    /**
     * Compile localstore email varibles
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return array $result
     */
    protected function _getLocalStoreVariables(Peppermint_Sales_Model_Order $order)
    {
        $result = [];

        if ($order->getDealerCode()) {
            $localStore = Mage::getModel('rockar_localstores/stores')->load($order->getDealerCode(), 'code');
            $result['localStore'] = $localStore;

            if ($localStore->getEntityId()) {
                $result['dealerAddress'] = Mage::getModel('rockar_localstores/address')->load($localStore->getEntityId(), 'store_id');
            }
        }

        return $result;
    }

    /**
     * Compile collection date time email varibles
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return array $result
     */
    protected function _getCollectionDateTimeVariable(Peppermint_Sales_Model_Order $order)
    {
        $result = [];

        if ($order->getCollectionDate()) {
            $collectionDate = new \DateTime((string) $order->getCollectionDate());
            if ($collectionDate) {
                $result['bookedDateTime'] = $collectionDate->format($this->_dateTimeFormat);
            }
        }

        return $result;
    }
}
