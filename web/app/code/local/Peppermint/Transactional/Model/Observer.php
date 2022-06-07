<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Transactional
 * @author       Donald Mailula  <mailula.donald@partner.bmw.co.za>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Model_Observer
{
    private $_helper;

    /**
     * Push regstration details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactRegistration(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $event = $observer->getEvent();
            $customer = $event->getCustomer();
            $gcdm = $event->getGcdm();
            $customer->setGcid($gcdm->businessPartner->gcid);
            Mage::helper('peppermint_transactional/Registration')->setCustomerData($customer)
                ->sendApi();
        }

        return $this;
    }

    /**
     * Push login details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactLogin(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            Mage::helper('peppermint_transactional/Login')
                ->setCustomerData($observer->getEvent()->getCustomer())
                ->sendApi();
        }

        return $this;
    }

    /**
     * Push trade in details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactTradeInSave(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $partExchange = $observer->getEvent()
                ->getPartExchange();

            return $this->_setTradeIn(
                $partExchange,
                'A',
                'E0001'
            );
        }

        return $this;
    }

    /**
     * Push tradeIn edit details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactTradeInEdit(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $partExchange = $observer->getEvent()
                ->getPartExchange();

            return $this->_setTradeIn(
                $partExchange,
                'B',
                'E0001'
            );
        }

        return $this;
    }

    /**
     * Push tradein details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactTradeInRemove(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $partExchange = $observer->getEvent()
                ->getPartExchange();

            return $this->_setTradeIn(
                $partExchange,
                'B',
                'E0003'
            );
        }

        return $this;
    }

    /**
     * Push tradein details to helper
     * @param string $partExchange
     * @return $this
    */
    private function _setTradeIn($partExchange, $processingMode, $crmStatus)
    {
        //Make local copy of px session data to not change the original px session data
        $object = new Varien_Object();
        Mage::helper('peppermint_transactional/TradeIn')->setPartExchangeData($object->setData(
            array_merge(
                $partExchange->getData(),
                [
                    'processing_mode' => $processingMode,
                    'crm_status' => $crmStatus
                ]
            )
        ))->sendApi();

        return $this;
    }

    /**
     * Push wishlist details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactWishlistSave(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $wishlist = $observer->getEvent();

            return $this->_setWishlist(
                $wishlist,
                'A',
                'E0001'
            );
        }

        return $this;
    }

    /**
     * Push wishlist edit details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactWishlistEdit(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $wishlist = $observer->getEvent();

            return $this->_setWishlist(
                $wishlist,
                'B',
                'E0001'
            );
        }

        return $this;
    }

    /**
     * Push wishlist delete details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactWishlistRemove(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $wishlist = $observer->getEvent();

            return $this->_setWishlist(
                $wishlist,
                'B',
                'E0003'
            );
        }

        return $this;
    }

    /**
     * Push wishlist event details to helper
     * @param string $partExchange
     * @return $this
    */
    private function _setWishlist($wishlist, $processingMode, $crmStatus)
    {
        $wishlist->setProcessingMode($processingMode);
        $wishlist->setCrmStatus($crmStatus);
        Mage::helper('peppermint_transactional/Wishlist')->setWishlistData($wishlist)
            ->sendApi();

        return $this;
    }

    /**
     * Push finance details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactFinanceSave(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $finance = $observer->getEvent();
            $paymentType = $finance->getItem()
                ->getQuote()
                ->getData()['finance_payment_type'];
            if ($this->_getHelper()->isFinanced($paymentType)) {
                return $this->_setFinance(
                    $finance,
                    'A'
                );
            }
        }

        return $this;
    }

    /**
     * Push finance details to helper
     * @param string $finance
     * @return $this
    */
    private function _setFinance($finance, $processingMode)
    {
        $finance->setProcessingMode($processingMode);
        Mage::helper('peppermint_transactional/Finance')->setFinanceData($finance)
            ->sendApi();

        return $this;
    }


    /**
     * Push checkout details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactCheckout(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $checkout = $observer->getEvent();
            $orderItem = $checkout->getOrderItem();

            if ($orderItem->getData('product_type') === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                Mage::helper('peppermint_transactional/checkout')->setCheckoutData($checkout)
                    ->sendApi();
            }
        }

        return $this;
    }

    /**
     * Push order details to helper
     * @param Varien_Event_Observer $observer
     * @return $this
    */
    public function transactOrder(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $order = $observer->getEvent()
                ->getOrder();
            if ($order->getStatus() == 'order_complete') {
                $order->setProcessingMode('A');
                Mage::helper('peppermint_transactional/Order')->setOrderData($order)
                    ->sendApi();
            }
        }

        return $this;
    }

    /**
     * Send transactional data through to SAP
     *
     * @param Varien_Event_Observer $observer
     * @returns $this
     */
    public function transactTestDrive(Varien_Event_Observer $observer)
    {
        if($this->_getHelper()->isEnabled()) {
            Mage::helper('peppermint_transactional/testDrive')->setTestDriveData($observer->getData('booking'))
                ->sendApi();
        }

        return $this;
    }

    /**
     * Save CampaignId to the session on first load
     * @return $this
    */
    public function transactSaveCampaignId()
    {
        if (
            $this->_getHelper()->isEnabled()
            && $campaignId = Mage::app()->getRequest()->getParam('campaignId')
        ) {
            Mage::getSingleton('core/session')->setCampaignId($campaignId);
        }

        return $this;
    }

    /**
     * Return copy of module helper saved against class
     * @return Peppermint_Transactional_Helper_Data
     */
    private function _getHelper()
    {
        if (!$this->_helper || !$this->_helper instanceof Peppermint_Transactional_Helper_Data) {
            $this->_helper = Mage::helper('peppermint_transactional');
        }

        return $this->_helper;
    }
}
