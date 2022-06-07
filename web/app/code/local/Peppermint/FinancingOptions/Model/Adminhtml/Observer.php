<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Adminhtml_Observer extends Rockar_FinancingOptions_Model_Adminhtml_Observer
{
    // Prefix for payment type beginning with a number
    const PREFIX = 'f';

    /**
     * @param string $type
     *
     * @return string
     */
    public static function getClassName($type = '')
    {
        return self::_getClassName($type);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function getFileName($type = '')
    {
        return self::snakeToCamel(self::_getClassName($type)) . '.php';
    }

    /**
     * Method to return custom calculation class name.
     *
     * @param string $type
     *
     * @return string
     */
    protected static function _getClassName($type)
    {
        return (is_numeric(substr($type, 0, 1)) ? self::PREFIX : '') . $type . self::SUFFIX;
    }

    /**
     * Save custom calculation fields outside root folder.
     *
     * @param Varien_Event_Observer $observer
     *
     * @throws Exception
     * @return $this
     */
    public function exportCalculationFormulas(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        /**
         * @var Rockar_FinancingOptions_Model_Options
         */
        $option = $event->getData('option');

        $type = $option->getType();
        $monthlyPrice = $option->getData('monthly_price_calc');
        $totalAmountPayable = $option->getData('total_amount_payable_calc');
        $interestCharges = $option->getData('interest_charges_calc');
        $interestRate = $option->getData('interest_rate_calc');

        $ioFile = new Varien_Io_File();
        $path = self::getPath();
        $name = self::getFileName($type);
        $className = self::getClassName($type);

        $ioFile->setAllowCreateFolders(true);
        $ioFile->open(['path' => $path]);
        $ioFile->streamOpen($name, 'w+');
        $ioFile->streamLock(true);

        $ioFile->streamWrite('<?php' . PHP_EOL);

        $ioFile->streamWrite('include_once \'CalculationAbstract.php\';' . PHP_EOL);

        $ioFile->streamWrite("class {$className} extends CalculationAbstract {" . PHP_EOL);

        $ioFile->streamWrite('public function monthlyPrice($apr, $finalPayment, $amountOfCredit, $rateSubventionAmount, 
        $term, $fees, $leaseRental, $interestRate, $individualFeeMonthly ,$individualFeeCapitalised, 
        $pxSettlementCreditamount, $balloonAmount = 0) { ' . PHP_EOL . '$result = 0;' . PHP_EOL . "{$monthlyPrice} "
        . PHP_EOL . 'return $result; }' . PHP_EOL);

        $ioFile->streamWrite('public function totalAmountPayable($totalDeposit, $finalPayment, $monthlyCost, $term, 
        $amountOfCredit, $customerDeposit, $manufactureDeposit, $individualFeeMonthly, $depositBalance, 
        $balloonAmount = 0) { ' . PHP_EOL . '$result = 0;' . PHP_EOL . "{$totalAmountPayable} " . PHP_EOL
        . 'return $result; }' . PHP_EOL);

        $ioFile->streamWrite('public function interestCharges($totalAmountPayable, $productPrice, $fees, $finalPayment, 
        $individualFeeMonthly, $term) { ' . PHP_EOL
        . '$result = 0;' . PHP_EOL . "{$interestCharges} " . PHP_EOL . 'return $result; }' . PHP_EOL);

        $ioFile->streamWrite('public function interestRate($subventionAmount, $subventionRate, $interestRate) { '
        . PHP_EOL . '$result = 0;' . PHP_EOL . "{$interestRate} " . PHP_EOL . 'return $result; }' . PHP_EOL);

        $ioFile->streamWrite('}' . PHP_EOL);

        $ioFile->streamUnlock();
        $ioFile->close();

        if (strpos(exec('php -l ' . $path . $name), 'Errors parsing') !== false) {
            throw new Exception(Mage::helper('financing_options')
            ->__('Error on Calculation fields validation. Please re-check php code for finance option %s', $type));
        }

        return $this;
    }

    /**
     * Change validation for the Option Type field to allow numbers in a first place
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function changeFinanceOptionTypeValidation(Varien_Event_Observer $observer)
    {
        /** @var Varien_Data_Form $form */
        $form = $observer->getEvent()->getForm();

        $element = $form->getElement('type');
        $element->setClass('validate-alphanum validate-length minimum-length-2 maximum-length-5');

        return $this;
    }
}
