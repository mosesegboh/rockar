<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Calculation_Type_PayInFull extends Rockar_FinancingOptions_Model_Calculation_Type_PayInFull
{
    private $_helper;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_helper = new Peppermint_FinancingOptions_Helper_Calculation($this);
    }

    /**
     * @return Rockar_FinancingOptions_Model_Calculation
     */
    public function getParentClassInstance()
    {
        return $this->_parentClassInstance;
    }

    /**
     * Returns full product price, including accessories
     *
     * @return float
     */
    public function getProductPrice()
    {
        $cacheKey = 'product_price';
        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        $product = $this->_parentClassInstance->getProduct();
        $productPrice = $product->getCustomPrice();
        if (!$productPrice) {
            $finalPrice = $product->getFinalPrice();
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $finalPrice -= $this->_parentClassInstance->getOptionsTotal();
            }
            $productPrice = $finalPrice;
        }
        $this->_cache[$cacheKey] = $productPrice
            + $this->_parentClassInstance->getAccessoriesTotal()
            + $this->_parentClassInstance->getOptionsTotal()
            - $this->_parentClassInstance->getDiscountValue();

        if (!Mage::helper('rockar_approvedused')->isApprovedUsedCategory()) {
            $this->_cache[$cacheKey] += $product->getFirstRegistrationFee();
        }

        return $this->_cache[$cacheKey];
    }

    /**
     * Returns RRP price
     *
     * @return float
     */
    public function getRrp()
    {
        return $this->_parentClassInstance->getProduct()
            ->getRrpPrice();
    }


    /**
     * Returns part exchange data from session
     *
     * @return Varien_Object
     */
    public function getPartExchange()
    {
        $cacheKey = 'px_session';

        if (!isset($this->_cache[$cacheKey])) {
            $this->_cache[$cacheKey] = $this->_helper->isReorder()
                ? (Mage::getSingleton('adminhtml/session_quote')->getPartExchange(true) ?: new Varien_Object())
                :  Mage::helper('rockar_partexchange')->loadPartExchangeFromSession(
                    Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY
                );
        }

        return $this->_cache[$cacheKey];
    }

    /**
     * Returns part exchange value.
     *
     * @return float
     */
    public function getPartExchangeValue()
    {
        $cacheKey = 'px_value';

        if (!isset($this->_cache[$cacheKey])) {
            $partExchange = $this->getPartExchange();
            $pxValue = $partExchange->getPartExchangeValue();
            // Send out an event to adjust PX value for quote
            if ($partExchange->getData('totals') !== null) {
                Mage::dispatchEvent(
                    'rockar_financing_options_get_px_value',
                    [
                        'px' => $partExchange,
                        'product' => $this->getProduct()
                    ]
                );

                if (is_numeric($partExchange->getUpdatedPartExchangeValue())) {
                    $pxValue = $partExchange->getUpdatedPartExchangeValue();
                }
            }
            $this->_cache[$cacheKey] = (float) $pxValue;
        }

        return $this->_cache[$cacheKey];
    }

    /**
     * Return 0 as pay in full has no balloon data.
     *
     * @return float
     */
    public function getBalloonPercentage()
    {
        return 0;
    }

    /**
     * Return 0 as pay in full has no balloon data.
     *
     * @return float
     */
    public function getBalloonAmount()
    {
        return 0;
    }

    /**
     * Returns outstanding finance value.
     *
     * @return float
     */
    public function getOutstandingFinance()
    {
        $cacheKey = 'outstanding_finance';

        if (!isset($this->_cache[$cacheKey])) {
            $this->_cache[$cacheKey] = (float) $this->getPartExchange()->getData('outstanding_finance');
        }

        return $this->_cache[$cacheKey];
    }

    /**
     * Returns Trade-in Settlement Due
     *
     * @return float
     */
    public function getPxSettlementPayment(): float
    {
        return $this->_helper->getPxSettlementPayment();
    }

    /**
     * Where a customer has opted to pay their negative equity through
     * their credit agreement by adding the negative equity amount to their
     * total finance amount
     *
     * @return float
     */
    public function getPxSettlementCreditamount(): float
    {
        return $this->_helper->getPxSettlementCreditamount();
    }

    /**
     * Validate minimum deposit
     *
     * @return boolean
     */
    public function getMinDepositValidation(): bool
    {
        return true;
    }

    /**
     * Get the shortfall applied amount.
     *
     * @return float
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getShortfallApplied()
    {
        return $this->_helper->getShortfallApplied();
    }

    /**
     * Get the Trade In Settlement Due amount
     *
     * @return float
     */
    public function getTradeInSettlementDue(): float
    {
        $amount = (float) ($this->getPartExchangeValue() - $this->getOutstandingFinance());

        return $amount < 0 ? - $amount : 0;
    }

    /**
     * Get the Trade In Surplus amount
     *
     * @return float
     */
    public function getTradeInSurplus(): float
    {
        $amount = (float) ($this->getPartExchangeValue() - $this->getOutstandingFinance());

        return $amount > 0 ? $amount : 0;
    }
}
