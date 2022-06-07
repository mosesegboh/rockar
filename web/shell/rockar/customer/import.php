<?php
/**
 * Script imports all customers from the CSV file
 *
 * @category  Rockar
 * @package   Rockar\Shell
 * @author    Taras Kapushchak <info@scandiweb.com>
 * @copyright Copyright (c) 2016 Scandiweb, Ltd (http://scandiweb.com)
 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
require_once dirname(__FILE__) . '/../abstract.php';

class Rockar_Shell_Customer_Import extends Rockar_Shell_Abstract
{
    /**
     * Parses customers' CSV data into array
     *
     * @param $file
     * @return array
     */
    protected function _getCsvToArray($file)
    {

        $row = 0;
        $columnNames = array();
        $items = array();

        if (($fp = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($fp, 1024 * 8, ',')) !== false) {
                $rowData = array();

                for ($c = 0; $c < count($data); $c++) {
                    if ($row == 0) {
                        $columnNames[$c] = $data[$c];
                    } else {
                        $rowData[$columnNames[$c]] = $data[$c];
                    }
                }

                if ($row > 0) {
                    $items[] = $rowData;
                }

                $row++;
            }

            fclose($fp);
        }

        return $items;
    }

    /**
     * Import all customers' data from the CSV file
     *
     * @param $storeCode
     */
    protected function _import($storeCode)
    {
        umask(0);

        $currentStore = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        try {
            $storeId = Mage::app()->getStore($storeCode)->getId();
        } catch (Mage_Core_Model_Store_Exception $e) {
            try {
                $storeId = Mage::app()->getStore($storeCode . '_store_view')->getId();
            } catch (Mage_Core_Model_Store_Exception $e) {
                $this->_appendResponse(sprintf('Invalid store code "%s"', $storeCode), 'red');
                $this->_appendResponse($this->usageHelp());

                return;
            }
        }

        $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();

        $customersSaved = array();
        $customersNotSaved = array();
        $customersAddressNotSaved = array();
        $customers = $this->_getCsvToArray('customers.csv');

        $progress = 0;

        foreach ($customers as $customerInfo) {
            $addresses = array_pop($customerInfo);
            $nzCustomerId = array_pop($customerInfo);

            $customer = Mage::getModel('customer/customer');

            $customerInfo['store_id'] = $storeId;
            $customerInfo['website_id'] = $websiteId;

            $customer->setData($customerInfo);

            try {
                $customer->save();
                $customerId = $customer->getId();
            } catch (Exception $error) {
                if (empty($nzCustomerId)) {
                    $nzCustomerId = json_encode($customerInfo);
                }
                $customersNotSaved[] = 'Row ' . $progress . ' - ' . $error->getMessage() . ' :    ' . $nzCustomerId;
                continue;
            }

            if ($addresses) {

                $addressData = json_decode($addresses, true);

                foreach ($addressData as $address) {
                    $isDefaultShipping = array_pop($address);
                    $isDefaultBilling = array_pop($address);
                    $customerAddress = Mage::getModel('customer/address');
                    $customerAddress->setData($address)->setCustomerId($customerId);

                    if ($isDefaultBilling == true) {
                        $customerAddress->setIsDefaultBilling(1)->setSaveInAddressBook('1');
                    }

                    if ($isDefaultShipping == true) {
                        $customerAddress->setIsDefaultShipping(1)->setSaveInAddressBook('1');
                    }

                    try {
                        $customerAddress->save();
                    } catch (Exception $error) {
                        $customersAddressNotSaved[] = $error->getMessage() . ' :    ' . $nzCustomerId;
                    }
                }
            }

            $customersSaved[] = $nzCustomerId . ' => ' . $customerId;

            if (++$progress % 500 === 0) {
                $this->_showMessage(sprintf('... %s customers were processed ...', $progress), 'green');
            }
        }

        Mage::app()->getStore()->setId($currentStore);

        $this->_saveLog('customers-saved.log', $customersSaved, 'Total %s customers were imported');
        $this->_saveLog('customers-not-saved.log', $customersNotSaved, 'Total %s customers were not imported', 'red');
        $this->_saveLog('customers-address-not-saved.log', $customersAddressNotSaved, 'Total %s customers\' addresses were not imported', 'red');
    }

    /**
     * Creates log files
     *
     * @param $fileName
     * @param $messages
     * @param $msgTemplate
     * @param string $msgColor
     */
    protected function _saveLog($fileName, $messages, $msgTemplate, $msgColor = 'green')
    {
        @unlink($fileName);
        if (count($messages) > 0) {
            $fp = fopen($fileName, 'w');
            foreach ($messages as $msg) {
                fputs($fp, $msg . PHP_EOL);
            }
            fclose($fp);

            $this->_showMessage(sprintf($msgTemplate, count($messages)), $msgColor);
        }
    }

    /**
     * Script execution method
     */
    public function run()
    {
        $command = $this->_getCliCommand();

        if (empty($command)) {

            $this->_appendResponse($this->usageHelp());

        } else {

            $this->_showMessage('Starting Customers\' Data Import', 'magenta');
            $this->_import($command);
            $this->_showMessage('==========================================', 'yellow');
            $this->_showMessage('DONE.', 'green');

        }

        $this->_displayResponse();
    }

    /**
     * Retrieve Usage Help Message
     *
     * @return  string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f import.php -- [STORE_CODE]

Example:
php -f import.php -- hyundai
USAGE;
    }
}

$shell = new Rockar_Shell_Customer_Import();
$shell->run();
