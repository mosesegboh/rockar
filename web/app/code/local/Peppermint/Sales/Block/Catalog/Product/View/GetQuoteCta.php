<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Catalog_Product_View_GetQuoteCta extends Mage_Core_Block_Template
{
    /**
     * @var null|Peppermint_Sales_Helper_QuoteMail
     */
    protected $_helper = null;

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('peppermint_sales/quoteMail');
    }

    /**
     * Should show the button
     * @return bool
     */
    public function shouldShow(): bool
    {
        return (bool) $this->_helper->getConfigEnabled();
    }

    /**
     * Get name for slotted template section
     * @return string
     */
    public function getSlotName()
    {
        return 'productActions';
    }

    /**
     * Get Product for the block
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Get Product Id
     * @return string|integer
     */
    public function getProductId()
    {
        return $this->getProduct()
            ->getId() ?? 0;
    }

    /**
     * Get Customer
     * @return null|Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Get Sales Representative Quote object
     * @return Varien_Object
     */
    public function getSalesQuote()
    {
        return new Varien_Object([
            'name' => $this->_helper->getQuoteName(
                $this->getCustomer(),
                $this->getProduct()
            )
        ]);
    }

    /**
     * Get Quote Name
     * @return string
     */
    public function getSalesQuoteName()
    {
        return $this->getSalesQuote()
            ->getName();
    }

    /**
     * Should show the "Continue Shopping" Cta?
     * @return bool
     */
    public function getShowContinueShoppingCta(): bool
    {
        return true;
    }

    /**
     * Get Customer Is Logged In?
     * @return bool
     */
    public function getCustomerIsLoggedIn(): bool
    {
        return (bool) Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Get Customer's e-mail
     * @return string
     */
    public function getCustomerEmail(): string
    {
        if ($this->getCustomerIsLoggedIn()) {
            return Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        }

        return '';
    }

    /**
     * Get Quote Url
     * @return string
     */
    public function getQuoteUrl(): string
    {
        return $this->_helper->getQuoteUrl($this->getProduct());
    }

    /**
     * My Account Url
     * @return string
     */
    public function myAccountUrl(): string
    {
        return $this->getUrl('customer/account/index#my-saved-cars');
    }

    /**
     * Get Continue Shopping Url
     * @return string
     */
    public function getContinueShoppingUrl(): string
    {
        // If no url given then the CTA attempts to use value from session Storage
        return '';
    }

    /**
     * Get Customer Login Url
     * @return string
     */
    public function getCustomerLoginUrl(): string
    {
        return $this->getUrl('customer/account/login');
    }

    /**
     * Whether or not to redirect the user when they select the
     * "continue shopping" call to action
     * @return bool
     */
    public function getRedirectToContinueShopping(): bool
    {
        return false;
    }
}
