<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Calculation_Type_Instalment extends Rockar_FinancingOptions_Model_Calculation_Type_Abstract
{
    const PERCENTAGE = 'Percentage';
    const BUSINESS = 'business';

    private $_helper;

    private $_balloonPercentage;

    /**
     * Construct.
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
     * Returns full product price, including accessories.
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
     * Returns RRP price.
     *
     * @return float
     */
    public function getRrp()
    {
        return $this->_parentClassInstance->getProduct()
            ->getRrpPrice();
    }

    /**
     * Returns $key for option and term from rockar_financing_options_terms table.
     *
     * @param mixed $key
     * @return mixed
     */
    private function _getValueFromOptionTerm($key)
    {
        return Mage::helper('peppermint_financingoptions/cache')->getFinancingOptions(
            $this->_option->getFinanceTermsModelAlias(),
            $this->_option->getData('options_id'),
            $this->_parentClassInstance->getTerm(),
            $key
        );
    }

    /**
     * Returns Individual or Corporate Fee Monthly for option and term from rockar_financing_options_terms table.
     *
     * @return float
     */
    public function getIndividualFeeMonthly()
    {
        return ($this->_isBusiness() === self::BUSINESS)
            ? $this->_getValueFromOptionTerm('corporate_fee_monthly')
            : $this->_getValueFromOptionTerm('individual_fee_monthly');
    }

    /**
     * Returns Individual or Business Fee Capitalised for option and term from rockar_financing_options_terms table.
     *
     * @return float
     */
    public function getIndividualFeeCapitalised()
    {
        return ($this->_isBusiness() === self::BUSINESS)
            ? $this->_getValueFromOptionTerm('corporate_fee_capitalised')
            : $this->_getValueFromOptionTerm('individual_fee_capitalised');
    }

    /**
     * Returns interest rate for option and term from rockar_financing_options_terms table or recalculated if
     * a subvention amount is applied.
     *
     * @return float
     */
    public function getInterestRate(): float
    {
        $cacheKey = 'interest_rate';

        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }
        $interestRate = $this->_getValueFromOptionTerm('interest_rate');
        $interestRateCalc = trim($this->_option->getData('interest_rate_calc'));

        if ($interestRateCalc && $interestRateCalc != '$result = $interestRate;') {
            $subventionAmount = $this->_parentClassInstance->getRateSubventionAmount();
            $subventionRate = $this->getSubventionRate($interestRate);

            $interestRate = $this->_customCalculation->interestRate($subventionAmount, $subventionRate, $interestRate);
        }
        $this->_cache[$cacheKey] = $interestRate;

        return $this->_cache[$cacheKey];
    }

    /**
     * Return Monthly price.
     *
     * @return float
     */
    public function getMonthlyCost()
    {
        $cacheKey = 'monthly_cost';

        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        if (
            !$this->_optionData || !$this->_optionData->getId()
            || !$this->_parentClassInstance->getMinDepositValidation()
        ) {
            $this->_cache[$cacheKey] = 0;

            return $this->_cache[$cacheKey];
        }

        $apr = $this->_optionData->getData('representative_apr');
        $finalPayment = (float) $this->_optionData->getData('optional_final_payment');
        $amountOfCredit = $this->_parentClassInstance->getBalanceToFinance();
        $term = $this->_parentClassInstance->getTerm();
        $fees = $this->_parentClassInstance->getFees();
        $leaseRental = (float) $this->_optionData->getData('lease_rental');
        $interestRate = $this->_getValueFromOptionTerm('interest_rate');
        $individualFeeMonthly = $this->_parentClassInstance->getIndividualFeeMonthly();
        $individualFeeCapitalised = $this->_parentClassInstance->getIndividualFeeCapitalised();
        $pxSettlementCreditamount = $this->_parentClassInstance->getPxSettlementCreditamount();
        $balloonAmount = $this->_parentClassInstance->getBalloonAmount();
        $subventionAmount = $this->_parentClassInstance->getRateSubventionAmount();

        $result = $this->_customCalculation->monthlyPrice(
            $apr,
            $finalPayment,
            $amountOfCredit,
            $subventionAmount,
            $term,
            $fees,
            $leaseRental,
            $interestRate,
            $individualFeeMonthly,
            $individualFeeCapitalised,
            $pxSettlementCreditamount,
            $balloonAmount
        );

        $this->_cache[$cacheKey] = round($result, 2);

        return $this->_cache[$cacheKey];
    }

    /**
     * Returns value of total amount payable | re-write of core functionality to add $depositBalance variable.
     *
     * @return float
     */
    public function getTotalAmountPayable()
    {
        $cacheKey = 'total_amount_payable';

        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        if (!$this->_validateTotalAmountPayable($cacheKey)) {
            return $this->_cache[$cacheKey];
        }

        $totalDeposit = $this->_parentClassInstance->getTotalDeposit();
        $finalPayment = (float) $this->_optionData->getData('optional_final_payment');
        $monthlyCost = $this->_parentClassInstance->getMonthlyCost();
        $term = $this->_parentClassInstance->getTerm();
        $amountOfCredit = $this->_parentClassInstance->getBalanceToFinance();
        $customerDeposit = $this->_parentClassInstance->getCustomerDeposit();
        $totalDepositContribution = $this->_parentClassInstance->getTotalDepositContribution();
        $individualFeeMonthly = $this->_parentClassInstance->getIndividualFeeMonthly();
        $depositBalance = $this->_parentClassInstance->getDepositBalance();
        $balloonAmount = $this->_parentClassInstance->getBalloonAmount();

        $calculationString = $this->_option->getData('total_amount_payable_calc');

        if ($calculationString) {
            $result = $this->_customCalculation->totalAmountPayable(
                $totalDeposit,
                $finalPayment,
                $monthlyCost,
                $term,
                $amountOfCredit,
                $customerDeposit,
                $totalDepositContribution,
                $individualFeeMonthly,
                $depositBalance,
                $balloonAmount
            );
        } else {
            $result = $totalDeposit + $finalPayment + $monthlyCost * $term;
        }

        $this->_cache[$cacheKey] = $result;

        return $this->_cache[$cacheKey];
    }

    /**
     * Return Customer Selected Balloon Percentage.
     *
     * @return float
     */
    public function getBalloonPercentage()
    {
        return min($this->_balloonPercentage, $this->_optionData->getData('max_balloon_percent'));
    }

    /**
     * Return Balloon Amount.
     *
     * @return float
     */
    public function getBalloonAmount()
    {
        return $this->getBalloonPercentage() / 100 * $this->getProductPrice();
    }

    /**
     * Sets Balloon Percentage.
     *
     * @param mixed $balloonPercentage
     * @return void
     */
    public function setBalloonPercentage($balloonPercentage)
    {
        $this->_balloonPercentage = $balloonPercentage;
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
     * Returns Trade-in Settlement Due.
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
     * total finance amount.
     *
     * @return float
     */
    public function getPxSettlementCreditamount(): float
    {
        return $this->_helper->getPxSettlementCreditamount();
    }

    /**
     * Validate minimum deposit.
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
     * Calculate Customer Deposit to Balance.
     *
     * @return float
     */
    public function getDepositBalance(): float
    {
        $depositBalance = $this->_parentClassInstance->getCashDeposit()
            + $this->_parentClassInstance->getPartExchangeValue()
            - $this->_parentClassInstance->getOutstandingFinance();

        return $depositBalance > 0 ? $depositBalance : 0;
    }

    /**
     * Calculate subvention rate.
     *
     * @param float $interestRate
     *
     * @return float
     */
    public function getSubventionRate($interestRate): float
    {
        $cacheKey = 'subvention_rate';

        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        $amountOfCredit = $this->_parentClassInstance->getBalanceToFinance();
        $subventionAmount = $this->_parentClassInstance->getRateSubventionAmount();
        $subventionRate = $this->_helper->calculateSubventionRate($amountOfCredit, $subventionAmount, $interestRate);

        $this->_cache[$cacheKey] = $subventionRate;

        return $this->_cache[$cacheKey];
    }

    /**
     * Return Rate Subvention Amount.
     *
     * @return float
     */
    public function getRateSubventionAmount(): float
    {
        $cacheKey = 'rate_subvention_amount';

        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        $subventionType = $this->_optionData->getData('rate_subvention_type');
        $subventionValue = $this->_optionData->getData('rate_subvention_value') ?: 0;
        $this->_cache[$cacheKey] = ($subventionType === self::PERCENTAGE)
            ? $this->getProductPrice() * ($subventionValue / 100)
            : $subventionValue;

        return $this->_cache[$cacheKey];
    }

    /**
     * Calculate monthly cost with dynamic variable for amount of credit, subvention and interest rate.
     *
     * @param float $amountOfCredit
     * @param float $subventionAmount
     * @param float $interestRate
     *
     * @return float
     */
    public function calculateMonthlyCost($amountOfCredit, $subventionAmount, $interestRate): float
    {
        $apr = $this->_optionData->getData('representative_apr');
        $finalPayment = (float) $this->_optionData->getData('optional_final_payment');
        $term = $this->_parentClassInstance->getTerm();
        $fees = $this->_parentClassInstance->getFees();
        $leaseRental = (float) $this->_optionData->getData('lease_rental');
        $individualFeeMonthly = $this->_parentClassInstance->getIndividualFeeMonthly();
        $individualFeeCapitalised = $this->_parentClassInstance->getIndividualFeeCapitalised();
        $pxSettlementCreditamount = $this->_parentClassInstance->getPxSettlementCreditamount();
        $balloonAmount = $this->_parentClassInstance->getBalloonAmount();

        $result = $this->_customCalculation->monthlyPrice(
            $apr,
            $finalPayment,
            $amountOfCredit,
            $subventionAmount,
            $term,
            $fees,
            $leaseRental,
            $interestRate,
            $individualFeeMonthly,
            $individualFeeCapitalised,
            $pxSettlementCreditamount,
            $balloonAmount
        );

        return round($result, 4);
    }

    /**
     * Return customer type
     *
     * @return boolean
     */
    protected function _isBusiness()
    {
        return Mage::getSingleton('customer/session')->getCustomerType();
    }

    /**
     * Return maximum deposit
     *
     * @return float
     */
    public function getCashDepositMaximum()
    {
        $cacheKey = 'cash_deposit_maximum';
        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        $productPrice = $this->_parentClassInstance->getProductPrice();
        $finalPayment = (float) $this->_optionData->getData('optional_final_payment');
        $depositMaximum = (float) $this->_optionData->getData('custom_cash_deposit_maximum');
        $totalDepositContribution = $this->_parentClassInstance->getTotalDepositContribution();
        $balloonAmount = $this->_parentClassInstance->getBalloonAmount();

        if ($productPrice / 100 * $depositMaximum + $finalPayment + $balloonAmount > $productPrice) {
            $depositMaximum = $productPrice - $finalPayment - $totalDepositContribution - $balloonAmount;
        } else {
            $depositMaximum = $productPrice / 100 * $depositMaximum - $totalDepositContribution;
        }

        $alternativeDepositMaximum = $this->_parentClassInstance->getAlternativeDepositMaximum();

        if ($alternativeDepositMaximum < $depositMaximum) {
            $depositMaximum = $alternativeDepositMaximum;
        }
        $this->_cache[$cacheKey] = $depositMaximum;

        return $this->_cache[$cacheKey];
    }

    /**
     * Get accessories array
     *
     * @return array
     */
    public function getAccessories()
    {
        $cacheKey = 'accessories';

        if (!isset($this->_cache[$cacheKey])) {
            $accessoriesArray = Mage::helper('rockar_accessories')->getSelectedAccessoriesPerProduct($this->getProduct()->getId());

            if (!empty($accessoriesArray)) {
                $this->_cache[$cacheKey] = $accessoriesArray;
            } else {
                $parentids = $this->getProduct()->getData('parent_id');

                if (!isset($parentids)) {
                    $parentids = Mage::helper('peppermint_financingoptions/cache')->getParentIdsByChild($this->getProduct()->getId());
                    $parentids = array_shift($parentids);
                }
                $this->_cache[$cacheKey] = Mage::helper('rockar_accessories')->getSelectedAccessoriesPerProduct($parentids);
            }
        }

        return $this->_cache[$cacheKey];
    }

    /**
     * check if customer have positive trade in equity
     *
     * @return bool
     */
    public function getIsTradeInSurplus()
    {
        return $this->_helper->getIsTradeInSurplus();
    }
}
