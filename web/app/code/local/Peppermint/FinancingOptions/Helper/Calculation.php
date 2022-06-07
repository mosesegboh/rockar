<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Helper_Calculation
{
    const EQUITY_DESCISION_ADD_TO_MONTHLY_PAYMENT = 1;
    const EQUITY_DESCISION_ONCE_OFF_PAYMENT = 2;
    const EQUITY_DESCISION_NO_PART_EXCHANGE = 3;

    private $_paymentTypeReference;

    /**
     * Cached calculated values
     *
     * @var array
     */
    protected $_cache = [];

    /**
     * Construct
     *
     * @param float $_paymentTypeReference
     */
    public function __construct($_paymentTypeReference)
    {
        $this->_paymentTypeReference = $_paymentTypeReference;
    }

    /**
     * Returns
     *      Include Outstanding amount into your next vehicle finance agreement = 1
     *      Make a one off payment to settle your outstanding finance = 2
     *
     * @return integer
     */
    public function getEquityDecision(): int
    {
        $cacheKey = 'outstanding_finance_settlement';

        if (!isset($this->_cache[$cacheKey])) {
            $financeStatement = $this->_paymentTypeReference->getPartExchange()->getData('outstanding_finance_settlement')
                ?: ($this->_paymentTypeReference->getPartExchange()->getData('negative_equity')
                    ? self::EQUITY_DESCISION_ONCE_OFF_PAYMENT
                    : 0
                );

            $this->_cache[$cacheKey] = (int) $financeStatement;
        }

        return $this->_cache[$cacheKey];
    }

    /**
     * Shortfall Limit
     *
     * @return float
     */
    public function getShortfallFinanceLimit(): float
    {
        $cacheKey = 'shortFall_limit';

        if (!isset($this->_cache[$cacheKey])) {
            $paymentMethod = $this->_paymentTypeReference->getPaymentMethod();
            $paymentTypeModel = Mage::helper('peppermint_financingoptions/cache')->getType($paymentMethod);
            $percentageOfVehicleFinance = $paymentTypeModel->getPercentageOfVehicleFinance() / 100.0;
            $valueCapLimit = $paymentTypeModel->getValueCapLimit();

            $this->_cache[$cacheKey] = min(
                $this->_paymentTypeReference->getProductPrice() * (float) $percentageOfVehicleFinance,
                (float) $valueCapLimit
            );
        }

        return $this->_cache[$cacheKey];
    }

    /**
     * Get the shortfall applied amount.
     *
     * @return float|int
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getShortfallApplied()
    {
        if ($this->getEquityDecision() !== self::EQUITY_DESCISION_NO_PART_EXCHANGE) {
            $shortFallSupport = $this->getShortFallSupport();

            if ($shortFallSupport != 0) {
                $equityPosition = $this->getEquityPosition();

                return $equityPosition < 0 ? min($shortFallSupport, $equityPosition * -1) : 0;
            }
        }

        return 0;
    }

    /**
     * Get the shortfall support amount.
     *
     * @return float|int
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getShortfallSupport()
    {
        return $this->_paymentTypeReference->getParentClassInstance()
            ->getPartExchangeDiscountValueShortfallLimit();
    }

    /**
     * Negative Equity occurs when the Outstanding Finance on a Trade-In vehicle
     * is greater than the fair market value of the asset. As no deposit is
     * required at BMW, any deposit is taken into the negative equity calculation
     * to provide the equity position
     *
     * @return float
     */
    public function getEquityPosition(): float
    {
        $cacheKey = 'equity_position';

        if (!isset($this->_cache[$cacheKey])) {
            // Cash calculations do not take into account a deposit amount
            $pxValue = $this->_paymentTypeReference->getPartExchangeValue() - $this->_paymentTypeReference->getOutstandingFinance();

            $this->_cache[$cacheKey] = $this->IsPayinfull()
                ? $pxValue
                : $pxValue + $this->_paymentTypeReference->getCashDeposit();
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
        $result = $this->getEquityPosition() + $this->getShortFallApplied();
        $amount = $result < 0 ? $result * -1 : 0;

        // For Pay in Full payment negative eq decision should always once off payment
        $equityDecision = $this->IsPayinfull()
            ? self::EQUITY_DESCISION_ONCE_OFF_PAYMENT
            : $this->getEquityDecision();

        switch ($equityDecision) {
            case self::EQUITY_DESCISION_ONCE_OFF_PAYMENT:
                return $amount;
            case self::EQUITY_DESCISION_ADD_TO_MONTHLY_PAYMENT:
                return $amount - $this->getPxSettlementCreditamount();
            default:
                return 0;
        }
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
        $cacheKey = 'px_settlement_credit';

        if (!isset($this->_cache[$cacheKey])) {
            $result = $this->getEquityPosition() + $this->getShortFallApplied();

            $this->_cache[$cacheKey] = min(
                ($this->getEquityDecision() === self::EQUITY_DESCISION_ADD_TO_MONTHLY_PAYMENT
                    ? ($result < 0 ? $result * -1 : 0)
                    : 0
                ),
                $this->getShortfallFinanceLimit()
            );
        }

        return $this->_cache[$cacheKey];
    }

    /**
     * Calculate subvention rate using binary search
     *
     * @param float $amountOfCredit
     * @param float $subventionAmount
     * @param float $interestRate
     *
     * @return float
     */
    public function calculateSubventionRate($amountOfCredit, $subventionAmount, $interestRate): float
    {
        if ($interestRate < 1) {
            return 0;
        }

        $cacheKey = 'subvention_rate';

        if (!isset($this->_cache[$cacheKey])) {
            $calculatedPayment = $this->_paymentTypeReference->calculateMonthlyCost($amountOfCredit, 0, $interestRate);
            $subventionPayment = $this->_paymentTypeReference->calculateMonthlyCost($amountOfCredit, $subventionAmount, $interestRate);

            $subventionRate = 0;
            // lowest value -1 to cover negative rate possibility when subvention is grater than amount of credit
            $lowest = -1;
            $highest = $interestRate;
            while ($calculatedPayment !== $subventionPayment) {
                $subventionRate = ($lowest + $highest) / 2;
                // cannot have a negative interest rate (%)
                if ($subventionRate < 0) {
                    $subventionRate = 0;
                    break;
                }

                $calculatedPayment = $this->_paymentTypeReference->calculateMonthlyCost($amountOfCredit, 0, $subventionRate);
                // continue search in appropriate half
                if ($calculatedPayment < $subventionPayment) {
                    $lowest = $subventionRate;
                } else {
                    $highest = $subventionRate;
                }
            }

            $this->_cache[$cacheKey] = $subventionRate;
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
        $cacheKey = 'is_trade_in_surplus';

        if (!isset($this->_cache[$cacheKey])) {
            $outstandingFinance = $this->_paymentTypeReference->getOutstandingFinance();

            $this->_cache[$cacheKey] = ($outstandingFinance != 0
                && ($this->_paymentTypeReference->getPartExchangeValue() - $outstandingFinance) > 0
            );
        }

        return $this->_cache[$cacheKey];
    }

    /**
     * Check if re-order
     *
     * @return bool
     */
    public function isReorder()
    {
        return $this->_paymentTypeReference->getParentClassInstance()
            instanceof Rockar_FinancingOptions_Model_Calculation_Reorder;
    }

    /**
     * Check if pay in full payment
     *
     * @return bool|null
     */
    public function IsPayinfull()
    {
        return $this->_paymentTypeReference->getParentClassInstance()
            ->getIsPayinfull();
    }
}
