<?php
/**
 * Script exports all customers into a CSV file
 *
 * @category  Rockar
 * @package   Rockar\Shell
 * @author    Taras Kapushchak <info@scandiweb.com>
 * @copyright Copyright (c) 2016 Scandiweb, Ltd (http://scandiweb.com)
 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
require_once dirname(__FILE__) . '/../abstract.php';

class Rockar_Shell_Customer_Export extends Rockar_Shell_Abstract
{
    /**
     * Get Attributes
     *
     * @param integer $typeId
     * @param string $extraCondition
     * @return array $fieldnames
     */
    protected function _getAttributes($typeId = 1, $extraCondition = '')
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');

        /* eav attributes */
        $query = 'SELECT attribute_code FROM eav_attribute WHERE entity_type_id = ' . $typeId;

        $query .= $extraCondition;

        $attributes = $read->fetchAll($query);

        $fieldNames = array();

        foreach ($attributes as $attribute) {
            $fieldNames[] = $attribute['attribute_code'];
        }

        return $fieldNames;
    }

    /**
     * Export all customers' data to a CSV file
     *
     * @param $websiteCode
     */
    protected function _export($websiteCode)
    {
        umask(0);

        $currentStore = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        try {
            $websiteId = Mage::app()->getWebsite($websiteCode)->getId();
        } catch (Mage_Core_Exception $e) {
            $this->_appendResponse(sprintf('Invalid website code "%s"', $websiteCode), 'red');
            $this->_appendResponse($this->usageHelp());

            return;
        }

        $addressFields = $this->_getAttributes(2);

        $fieldNames = $this->_getAttributes(1, " AND attribute_code !='default_billing' AND attribute_code !='default_shipping'");
        $fieldNames[] = 'current_server_entity_id';
        $fieldNames[] = 'addresses';
        $fieldsCount = count($fieldNames);


        $fp = fopen('customers.csv', 'w');

        fputcsv($fp, $fieldNames);

        $customerCollection = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('website_id', array('eq' => $websiteId));

        $size = $customerCollection->getSize();
        $progress = 0;

        foreach ($customerCollection as $customer) {

            $data = array();
            $addressData = array();

            foreach ($customer->getAddresses() as $address) {
                foreach ($addressFields as $field) {
                    $data[$field] = $address->getData($field);
                }

                $data['default_billing'] = ($customer->getDefaultBilling() == $address->getId());
                $data['default_shipping'] = ($customer->getDefaultShipping() == $address->getId());

                $addressData[] = $data;
            }

            $data = array();

            foreach ($fieldNames as $field) {
                $data[] = $customer->getData($field);
            }

            $data[$fieldsCount - 2] = $customer->getId();

            //address data will be stored in the CSV as json
            if (count($addressData) > 0) {
                $data[$fieldsCount - 1] = json_encode($addressData);
            }

            fputcsv($fp, $data);

            if (++$progress % 500 === 0) {
                $this->_showMessage(sprintf('... %s customers were processed ...', $progress), 'green');
            }
        }

        fclose($fp);

        Mage::app()->getStore()->setId($currentStore);

        $this->_showMessage(sprintf('Total %s customers\' data were exported', $size), 'green');
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

            $this->_showMessage('Starting Customers\' Data Export', 'magenta');
            $this->_export($command);
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
Usage:  php -f export.php -- [WEBSITE_CODE]

Example:
php -f export.php -- base
USAGE;
    }
}

$shell = new Rockar_Shell_Customer_Export();
$shell->run();
