<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Carfinder
 * @author    Sergejs Plisko <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Carfinder_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Step names
     */
    const CAR_FINDER_STEP_FINANCE = 'finance';
    const CAR_FINDER_STEP_LANDING = 'landing';
    const CAR_FINDER_STEP_PART_EXCHANGE = 'partExchange';
    const CAR_FINDER_STEP_MODEL_FILTER = 'modelFilter';
    const CAR_FINDER_STEP_CAR_FILTER = 'carFilter';

    /**
     * Step values
     */
    const CAR_FINDER_STEP_FINANCE_VALUE = 0;
    const CAR_FINDER_STEP_PART_EXCHANGE_VALUE = 10;
    const CAR_FINDER_STEP_PART_EXCHANGE_OUTSTANDING_FINANCE_VALUE = 12;
    const CAR_FINDER_STEP_MODEL_FILTER_VALUE = 20;
    const CAR_FINDER_STEP_CAR_FILTER_VALUE = 21;

    /**
     * Get step parameter from URL
     *
     * @return string
     */
    protected function getStepParam()
    {
        return Mage::app()->getRequest()->getParam('step') ?: self::CAR_FINDER_STEP_FINANCE;
    }

    /**
     * Getter for car filter step name
     *
     * @return string
     */
    public function getCarFilterStepName()
    {
        return self::CAR_FINDER_STEP_CAR_FILTER;
    }

    /**
     * Prepare step parameter for CarFinder from URL
     *
     * @return string
     */
    public function getStepParamValue()
    {
        if (Mage::helper('peppermint_all/newJourney')->isBrandSelected()) {
            return $this->getStepParam() === static::CAR_FINDER_STEP_CAR_FILTER
                ? static::CAR_FINDER_STEP_CAR_FILTER
                : static::CAR_FINDER_STEP_LANDING;
        }

        switch ($this->getStepParam()) {
            case self::CAR_FINDER_STEP_PART_EXCHANGE:
                $stepValue = self::CAR_FINDER_STEP_PART_EXCHANGE_VALUE;
                $partExchange = Mage::helper('rockar_partexchange')->loadPartExchangeFromSession();

                if (
                    (int) Mage::getSingleton('customer/session')->getData('current_step') === self::CAR_FINDER_STEP_PART_EXCHANGE_OUTSTANDING_FINANCE_VALUE
                    && !is_null($partExchange->getData('totals'))
                    && !is_null($partExchange->getData('px_id'))
                ) {
                    $stepValue = self::CAR_FINDER_STEP_PART_EXCHANGE_OUTSTANDING_FINANCE_VALUE;
                }

                break;
            case self::CAR_FINDER_STEP_MODEL_FILTER:
                $stepValue = self::CAR_FINDER_STEP_MODEL_FILTER_VALUE;
                break;
            case self::CAR_FINDER_STEP_CAR_FILTER:
                $stepValue = self::CAR_FINDER_STEP_CAR_FILTER_VALUE;
                break;
            default:
                $stepValue = self::CAR_FINDER_STEP_FINANCE_VALUE;
                break;
        }

        return $stepValue;
    }

    /**
     * Prepare model step and navigation titles for model select step
     *
     * @return string[]
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getModelStepTitles()
    {
        $pageTitle = 'Choose your ';

        switch (str_replace('_store_view', '', Mage::app()->getStore()->getCode())) {
            case 'mini':
                $result = [
                    'step_title' => $pageTitle . 'Mini',
                    'model_navigation_title' => ''
                ];
                break;
            case 'motorrad':
                $result = [
                    'step_title' => $pageTitle . 'Motorcycle',
                    'model_navigation_title' => ''
                ];
                break;
            default :
                $result = [
                    'step_title' => $pageTitle . 'BMW',
                    'model_navigation_title' => 'Series'
                ];
                break;
        }

        return $result;
    }
}
