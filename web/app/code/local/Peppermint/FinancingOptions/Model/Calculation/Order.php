<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Calculation_Order extends Rockar_FinancingOptions_Model_Calculation_Order
{
    /**
     * extend setOption with installment option.
     *
     * @param Rockar_FinancingOptions_Model_Options $option
     *
     * @return $this
     */
    public function setOption($option)
    {
        parent::setOption($option);

        switch ($option->getMethodType()) {
            case Peppermint_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_INSTALMENT:
                $class = Mage::getModel('peppermint_financingoptions/calculation_type_instalment');
                $this->setData('is_instalment', true);
                $this->_calculationClass = $class;
                break;
        }

        $this->_calculationClass->setParentClassInstance($this);
        $this->_options = $option;
        $this->_calculationClass->setOption($option);

        return $this;
    }

    /**
     * Set params.
     *
     * @param integer $terms
     * @param integer $mileage
     * @param integer $deposit
     * @param integer $depositMultiple
     * @param integer $balloonPercentage
     *
     * @return void
     */
    public function setParams($terms, $mileage, $deposit, $depositMultiple, $balloonPercentage = 0)
    {
        parent::setParams($terms, $mileage, $deposit, $depositMultiple);
        $this->_calculationClass->setBalloonPercentage($balloonPercentage);
    }

    /**
     * Returns individual fee monthly.
     *
     * @return float
     */
    public function getIndividualFeeMonthly()
    {
        return $this->_calculationClass->getIndividualFeeMonthly();
    }

    /**
     * Returns individual fee captalised.
     *
     * @return float
     */
    public function getIndividualFeeCapitalised()
    {
        return $this->_calculationClass->getIndividualFeeCapitalised();
    }

    /**
     * Returns interest rate.
     *
     * @return float
     */
    public function getInterestRate()
    {
        return $this->_calculationClass->getInterestRate();
    }

    /**
     * Returns outstanding finance value.
     *
     * @return float
     */
    public function getOutstandingFinance()
    {
        return $this->_calculationClass->getOutstandingFinance();
    }

    /**
     * Returns Trade-in Settlement Due.
     *
     * @return float
     */
    public function getPxSettlementPayment()
    {
        return $this->_calculationClass->getPxSettlementPayment();
    }

    /**
     * Returns Short Fall Support.
     *
     * @return float
     */
    public function getShortfallSupport(): float
    {
        return $this->_calculationClass->getShortfallSupport();
    }

    /**
     * Returns Equity Decision.
     *
     * @return integer
     */
    public function getEquityDecision()
    {
        return $this->_calculationClass->getEquityDecision();
    }

    /**
     * Return Short Fall Applied.
     *
     * @return float
     */
    public function getShortfallApplied(): float
    {
        return $this->_calculationClass->getShortfallApplied();
    }

    /**
     * Return Equity Position.
     *
     * @return float
     */
    public function getEquityPosition(): float
    {
        return $this->_calculationClass->getEquityPosition();
    }

    /**
     * Return Shortfall Finance Limit.
     *
     * @return float
     */
    public function getShortfallFinanceLimit(): float
    {
        return $this->_calculationClass->getShortfallFinanceLimit();
    }

    /**
     * Return Px Settlement Creditamount.
     *
     * @return float
     */
    public function getPxSettlementCreditamount(): float
    {
        return $this->_calculationClass->getPxSettlementCreditamount();
    }

    /**
     * Return Customer Deposit Balance.
     *
     * @return float
     */
    public function getDepositBalance(): float
    {
        return $this->_calculationClass->getDepositBalance();
    }

    /**
     * Return Balloon Amount.
     *
     * @return float
     */
    public function getBalloonAmount(): ?float
    {
        return $this->_calculationClass->getBalloonAmount();
    }

    /**
     * Return Customer Selected Balloon Percentage.
     *
     * @return float
     */
    public function getBalloonPercentage(): ?float
    {
        return $this->_calculationClass->getBalloonPercentage();
    }

    /**
     * Return Rate Subvention Amount.
     *
     * @return float
     */
    public function getRateSubventionAmount(): float
    {
        return $this->_calculationClass->getRateSubventionAmount();
    }

    /**
     * Returns part exchange value.
     *
     * @return float
     */
    public function getPartExchangeValue()
    {
        return $this->_calculationClass->getPartExchangeValue();
    }

    /**
     * Return Customer Deposit Trade In Surplus.
     *
     * @return bool
     */
    public function getIsTradeInSurplus()
    {
        return $this->_calculationClass->getIsTradeInSurplus();
    }

    /**
     * Return Trade In Settlement Due.
     *
     * @return float
     */
    public function getTradeInSettlementDue(): float
    {
        return $this->_calculationClass->getTradeInSettlementDue();
    }

    /**
     * Return Trade In Surplus.
     *
     * @return float
     */
    public function getTradeInSurplus(): float
    {
        return $this->_calculationClass->getTradeInSurplus();
    }
}
