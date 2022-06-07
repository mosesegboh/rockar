<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Osama Ahmed <osama.ahmed@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Helper_QuoteMail extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SALES_EMAIL_QUOTE_ENABLED = 'sales_email/peppermint_quote/enabled';
    const XML_PATH_SALES_EMAIL_QUOTE_IDENTITY = 'sales_email/peppermint_quote/identity';
    const XML_PATH_SALES_EMAIL_QUOTE_EMAIL = 'sales_email/peppermint_quote/email';
    const XML_PATH_SALES_EMAIL_QUOTE_COPY_TO = 'sales_email/peppermint_quote/copy_to';
    const XML_PATH_SALES_EMAIL_QUOTE_COPY_METHOD = 'sales_email/peppermint_quote/copy_method';
    const XML_PATH_SALES_EMAIL_QUOTE_SEND_COPY_TO_DEALER = 'sales_email/peppermint_quote/send_copy_to_dealer';

    const EVENT_BEOFRE_SEND_QUOTE = 'peppermint_sales_before_send_quote_mail';
    const EVENT_AFTER_SEND_QUOTE = 'peppermint_sales_after_send_quote_mail';

    /**
     * Get Is Quote Mail enabled ?
     * @param integer $storeId - optional
     * @return bool
     */
    public function getConfigEnabled($storeId = 0): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SALES_EMAIL_QUOTE_ENABLED, $storeId);
    }

    /**
     * Get Quote Mail Sender Identity
     * @param integer $storeId - optional
     * @return array [ name, email ]
     */
    public function getConfigIdentity($storeId = 0): array
    {
        $configValue = Mage::getStoreConfig(self::XML_PATH_SALES_EMAIL_QUOTE_IDENTITY, $storeId);

        // First option is default value
        if (!$configValue) {
            $configValue = Mage::getModel('adminhtml/system_config_source_email_identity')->toOptionArray()[0]['value'];
        }

        return [
            'name'  => Mage::getStoreConfig("trans_email/ident_${configValue}/name", $storeId),
            'email' => Mage::getStoreConfig("trans_email/ident_${configValue}/email", $storeId)
        ];
    }

    /**
     * Get Quote Mail Sender Identity
     * @param integer $storeId - optional
     * @return Mage_Core_Model_Email_Template
     */
    public function getConfigEmailTemplate($storeId = 0): Mage_Core_Model_Email_Template
    {
        $configValue = Mage::getStoreConfig(self::XML_PATH_SALES_EMAIL_QUOTE_EMAIL, $storeId);

        // First option is default value
        if (!$configValue) {
            $configValue = Mage::getModel('adminhtml/system_config_source_email_template')->toOptionArray()[0]['value'];
        }

        return Mage::getModel('core/email_template')->load($configValue);
    }

    /**
     * Get Copy to list
     * @param integer $storeId - optional
     * @return string[]
     */
    public function getConfigCopyTo($storeId = 0): array
    {
        $configValue = Mage::getStoreConfig(self::XML_PATH_SALES_EMAIL_QUOTE_COPY_TO, $storeId);

        return !empty($configValue)
            ? explode(',', $configValue)
            : [];
    }

    /**
     * Get Copy Method
     * @param integer $storeId - optional
     * @return string
     */
    public function getConfigCopyMethod($storeId = 0): string
    {
        $configValue = Mage::getStoreConfig(self::XML_PATH_SALES_EMAIL_QUOTE_COPY_METHOD, $storeId);

        if (!$configValue) {
            $configValue = Mage::getModel('adminhtml/system_config_source_email_method')->toOptionArray()[0]['value'];
        }

        return $configValue;
    }

    /**
     * Get whether to send an email copy to dealer
     * @param integer $storeId - optional
     * @return bool
     */
    public function getSendCopyToDealer($storeId = 0): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SALES_EMAIL_QUOTE_SEND_COPY_TO_DEALER, $storeId);
    }

    /**
     * Get Store Id
     * @return integer
     */
    public function getStoreId()
    {
        return Mage::app()->getStore()->getStoreId();
    }

    /**
     * Get Quote Name
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getQuoteName($customer, $product)
    {
        return "{$customer->getPrefix()} {$customer->getFirstname()} {$customer->getLastname()}'s {$product->getTitle()}";
    }

    /**
     * Send quote email
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Wishlist_Model_Item|false $wishlist
     * @param Varien_Object $quote
     * @return $this
     */
    public function sendQuote($customer, $product, $quote, $wishlist)
    {
        $storeId = $this->getStoreId();

        if (!$this->getConfigEnabled($storeId)) {
            return $this;
        }

        Mage::dispatchEvent(
            self::EVENT_BEOFRE_SEND_QUOTE,
            [
                'customer' => $customer,
                'product'  => $product,
                'quote'    => $quote
            ]
        );

        $emailTemplate = $this->getConfigEmailTemplate($storeId);
        $copyToList = $this->getConfigCopyTo($storeId);
        $sendTo = [
            [
                'name'  => $customer->getName(),
                'email' => $customer->getEmail()
            ]
        ];
        $emailVariables = [
            'product'  => $product,
            'customer' => $customer,
            'quote'    => $quote,
            'wishlist' => $wishlist
        ];

        if ($this->getSendCopyToDealer()) {
            $this->_sendBccCopyToDealer($emailTemplate, $product);
        }

        switch ($this->getConfigCopyMethod()) {
            case 'bcc':
                foreach ($copyToList as $copyTo) {
                    $emailTemplate->addBcc($copyTo);
                }
                break;
            case 'copy':
                foreach ($copyToList as $copyTo) {
                    $sendTo[] = ['name' => null, 'email' => $copyTo];
                }
                break;
            default:
                break;
        }

        [ 'name' => $senderName, 'email' => $senderEmail ] = $this->getConfigIdentity();

        $emailTemplate->addData([
            'sender_email' => $senderEmail,
            'sender_name'  => $senderName
        ]);

        foreach ($sendTo as ['name' => $name, 'email' => $email]) {
            $emailTemplate->send($email, $name, $emailVariables);
        }

        Mage::dispatchEvent(
            self::EVENT_AFTER_SEND_QUOTE,
            [
                'customer' => $customer,
                'product'  => $product,
                'quote'    => $quote
            ]
        );

        return $this;
    }

    /**
     * _send BCC Copy To Dealers
     * @param Mage_Core_Model_Email_Template $emailTemplate
     * @param Mage_Catalog_Model_Product $product
     * @return void
     */
    protected function _sendBccCopyToDealer($emailTemplate, $product): void
    {
        $dealerEmails = Mage::helper('peppermint_dealeremail/dealer')->getDealerEmails(
            $product->getData('bag_vehicle_location_dlr_code')
        );

        if (!empty($dealerEmails)) {
            $dealersEmails = explode(',', $dealerEmails);

            foreach ($dealersEmails as $dealerEmail) {
                $emailTemplate->addBcc($dealerEmail);
            }
        }
    }

    /**
     * Add Product To Wishlist
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Catalog_Model_Product $product - visible in catalog
     * @param Varien_Object $buyRequest
     * @return Mage_Wishlist_Model_Item|false
     */
    public function addProductToWishlist($customer, $product, $buyRequest)
    {
        try {
            $helper = Mage::helper('rockar_mysavedcars');
            $wishlist = $helper->getWishlist();

            $name = $this->getQuoteName($customer, $product);

            if (!$product->getId() || !$product->isVisibleInCatalog()) {
                throw new Exception('Cannot find product to save to wishlist.');
            }

            $item = $wishlist->addNewItem($product, $buyRequest);

            if (is_string($item)) {
                throw new Exception($item);
            }

            Mage::dispatchEvent(
                'wishlist_add_product',
                [
                    'wishlist' => $wishlist,
                    'product'  => $product,
                    'item'     => $item
                ]
            );

            $item->setDescription($name)
                ->setProductPrice($product->getFinalPrice())
                ->save();

            $wishlist->save();

            Mage::helper('wishlist')->calculate();

            return $item;
        } catch (Exception $e) {
            Mage::logException($e);
            throw $e;
        }

        return false;
    }

    /**
     * Get Quote Url
     * @param null|Mage_Catalog_Model_Product $product - optional - configurable product
     * @param null|Mage_Catalog_Model_Product $vehicle - optional - simple product
     * @return string
     */
    public function getQuoteUrl($product = null, $vehicle = null): string
    {
        $params = [
            Mage_Core_Model_Url::FORM_KEY => Mage::getSingleton('core/session')->getFormKey()
        ];

        if ($product && ($id = $product->getId())) {
            $params['product_id'] = $id;
        }

        if ($vehicle && ($id = $vehicle->getId())) {
            $params['vehicle_id'] = $id;
        }

        return Mage::getUrl('sales/quote/getQuote', $params);
    }
}
