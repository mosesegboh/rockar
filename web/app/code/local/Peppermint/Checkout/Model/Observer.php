<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Checkout_Model_Observer extends Rockar_Checkout_Model_Observer
{
    /**
     * Validate current product in cart after selecting local store and replace it if needed
     *
     * @param Varien_Event_Observer $observer
     */
    public function validateAndReplaceOutOfStockProduct(Varien_Event_Observer $observer)
    {
        $productReplacementHelper = Mage::helper('peppermint_checkout/ReplacementProduct');
        /** @var Mage_Sales_Model_Quote $quote */
        $result = $observer->getResult();
        $quote = $observer->getQuote();
        $quoteHelper = Mage::helper('rockar_checkout/quote');
        $hasToReplace = !$productReplacementHelper->checkProductLeadTime() || $result->getVinReserved();

        $parentItem = null;
        $childItem = null;

        foreach ($quote->getItemsCollection()->getItems() as $item) {
            if ($item->getData('product_type') === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $parentItem = $item;
            }

            if ($item->getData('product_type') === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                $childItem = $item;
            }
        }

        $financeVariables = Mage::helper('rockar_all')->jsonDecode($childItem->getData('finance_data_variables'));
        $leadTimeBefore = $financeVariables['lead_time_numeric'] * Rockar_LeadTime_Helper_Data::LEAD_TIME_DAYS_PER_WEEK;
        $priceBefore = $financeVariables['rockar_price'];
        $productChanged = false;
        $canReplace = $productReplacementHelper->canProductBeReplaced($parentItem, $childItem);
        $financeOverlay = $parentItem->getData('finance_overlay');

        if ($canReplace) {
            $productChanged = $productReplacementHelper->replaceProduct($parentItem, $childItem, $hasToReplace);
            // get fresh quote from session
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $item = $quoteHelper->getFirstQuoteItem($quote);
            $simpleItem = $quoteHelper->getFirstVisibleQuoteItem($quote);
            $item->setData('finance_overlay', $financeOverlay)
                ->save();
            $newFinanceVariables = Mage::helper('rockar_all')->jsonDecode($simpleItem->getData('finance_data_variables'));
            $priceAfter = $newFinanceVariables['rockar_price'];

            $leadTimeAfter = $productReplacementHelper->getProductLeadTime();
            $helper = Mage::helper('core');
            $result->setProductSku($item->getSku())
                ->setProductPrice($priceAfter)
                ->setProductLeadTime($newFinanceVariables['lead_time'] ?? $leadTimeAfter);

            /**
             * Need to show notification if prices or leadtime don't match
             */
            if ($priceBefore !== $priceAfter) {
                $priceBefore = $helper->currency($priceBefore, true, false);
                $priceAfter = $helper->currency($priceAfter, true, false);
                $result->setPriceChanged(true)
                    ->setChangedValueBefore($priceBefore)
                    ->setChangedValueAfter($priceAfter)
                    ->setAllocateProductNotice(true);
            } else if ($leadTimeBefore !== $leadTimeAfter) {
                $result->setLeadTimeChanged(true)
                    ->setChangedValueBefore($leadTimeBefore)
                    ->setChangedValueAfter($leadTimeAfter)
                    ->setAllocateProductNotice(true);
            }
        }

        $result->setSuccess($productChanged || !$hasToReplace);
    }

    /**
     * When a new product is added to the cart, we should refresh form key to avoid issues with parallel
     * tabs in browser or similar
     *
     * @param Varien_Event_Observer $observer
     */
    public function processAddToCart(Varien_Event_Observer $observer)
    {
        Mage::getSingleton('core/session')->renewFormKey();
    }

    /**
     * Pass customer GCID to API after successfully placed order.
     *
     */

    public function gcidSplCheck()
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $customerAccess = Mage::getModel('peppermint_gcdm/customer_access')->load($customerId);
        $gcid = $customerAccess->getGcid();

        if ($gcid) {
            Mage::helper('peppermint_checkout/splCheck')->getGcidCheck(
                $gcid
            );
        }
    }

    /**
     * Rewrite parent function to make sure duplicate addresses are not created
     *
     * {@inheritDoc}
     */
    public function setCustomerAddress($observer)
    {
        $session = Mage::getSingleton('customer/session');

        $hasShipping = false;

        /**
         * @var Mage_Customer_Model_Customer $customer
         */
        $customer = $observer->getCustomer();
        $customerId = $customer->getEntityId();
        $savedAddress = $session->getRegistrationAddress();

        if ($savedAddress && $customerId) {
            $defaultAddress = $customer->getDefaultBillingAddress();
            $defaultShippingAddress = $customer->getDefaultShippingAddress();

            $address = ($defaultAddress && $defaultAddress->getId()) ? $defaultAddress : Mage::getModel('customer/address');
            $shippingAddress = null;

            $customerData = $this->_getCustomerData($customer);
            $address->setCustomerId($customerId)->addData($customerData);

            foreach ($savedAddress as $key => $value) {
                $address->setData($key, $value);
            }

            $address->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');

            if (isset($savedAddress['additional']) && Mage::app()->getRequest()->getParam('set_additional_address') == 1) {
                /* split addresses if billing and shipping was the same before
                and separate billing and shipping is now allowed */
                if ($defaultAddress && $defaultShippingAddress
                    && $defaultAddress->getId() === $defaultShippingAddress->getId()) {
                    $shippingAddress = Mage::getModel('customer/address');
                } else {
                    $shippingAddress = ($defaultShippingAddress && $defaultShippingAddress->getId())
                        ? $defaultShippingAddress : Mage::getModel('customer/address');
                }

                $hasShipping = true;

                $shippingAddress->setCustomerId($customerId)
                    ->addData($customerData)
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
                $address->unsIsDefaultShipping();

                foreach ($savedAddress['additional'] as $key => $value) {
                    $shippingAddress->setData($key, $value);
                }
            }

            try {
                $session->unsRegistrationAddress();
                $address->save();

                $quoteAddress = Mage::getModel('sales/quote_address');
                $quoteBillingAddress = $quoteAddress->importCustomerAddress($address);

                $quoteShippingAddress = $quoteBillingAddress;

                if ($hasShipping) {
                    $shippingAddress->save();
                    $quoteShippingAddress = $quoteAddress->importCustomerAddress($shippingAddress);
                }

                Mage::getSingleton('checkout/session')->getQuote()
                    ->setBillingAddress($quoteBillingAddress)
                    ->setShippingAddress($quoteShippingAddress)
                    ->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }
}
