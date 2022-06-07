<?php
require_once dirname(__FILE__) . '/../abstract.php';

/**
 * Script to import finance data and mapp with products
 *
 * @category     Scandiweb
 * @package      Rockar_Shell
 * @copyright    Copyright (c) 2016 Scandiweb, Inc (http://scandiweb.com)
 * @license      http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
class Rockar_Shell_Financing_Data_Import extends Rockar_Shell_Abstract
{
    /**
     *
     */
    const IMPORT_FILENAME = 'financial_options.csv';

    /**
     * Import data
     *
     * @var array
     */
    protected $_importData = [];
    /**
     * CapID map with Product SKU
     *
     * @var array
     */
    protected $_capIdProductIdMapping = [];

    /**
     * Run script
     */
    public function run()
    {
        $command = $this->_getCliCommand();

        switch ($command) {
            case 'import':
                $this->_showMessage('Collect CSV data', 'blue');
                $this->_getDataFromCsv();

                $this->_showMessage('Map CapID with Product IDs', 'blue');
                $this->_getProductIdByCapId();

                $this->_showMessage('Start Data Import Process', 'blue');
                if (!empty($this->_importData)) {

                    $this->_deleteAllFinancingData();

                    foreach ($this->_importData as $data) {
                        if (isset($this->_capIdProductIdMapping[$data['capid']])) {
                            $data['products'] = $this->_capIdProductIdMapping[$data['capid']];

                            if (empty($data['products'])) {
                                continue;
                            }
                            
                            $financingData = Mage::getModel('rockar_financingoptions/data');

                            try {
                                unset($data['data_id']);
                                $financingData->setData($data);
                                $financingData->save();

                                $this->_showMessage('Imported: ' . $financingData->getId(), 'green');
                            } catch (Mage_Exception $e) {
                                $this->_showMessage($e->getMessage(), 'red');
                            }
                        }
                    }
                }
                $this->_showMessage('Finish Data Import Process', 'blue');
                break;
            default:
                $this->_appendResponse('Invalid command', 'red');
            case 'help':
                /* Usage help or unrecognised command */
                $this->_appendResponse($this->usageHelp());
        }

        $this->_displayResponse();
    }

    /**
     * Delete all finance data
     *
     * @return $this
     * @throws Exception
     */
    protected function _deleteAllFinancingData()
    {
        $this->_showMessage('Delete all finance data', 'red');
        Mage::getModel('rockar_financingoptions/data')->getCollection()->walk('delete');

        return $this;
    }

    /**
     * Get import data from csv file
     *
     * @return $this
     */
    protected function _getDataFromCsv()
    {
        $this->_importData = [];
        if (($handleIn = fopen(dirname(__FILE__) . DS . self::IMPORT_FILENAME, 'r')) !== false) {
            $header = fgetcsv($handleIn, 0);

            while (($data = fgetcsv($handleIn)) !== false) {
                $this->_importData[] = array_combine($header, $data);
            }

            fclose($handleIn);
        }

        return $this;
    }

    /**
     * Map capID with product ID
     *
     * @return $this
     */
    protected function _getProductIdByCapId()
    {
        if (!empty($this->_importData)) {
            foreach ($this->_importData as $data) {
                if (!isset($this->_capIdProductIdMapping[$data['capid']])) {
                    $this->_capIdProductIdMapping[$data['capid']] = $this->_loadProductByCapId($data['capid']);
                }
            }
        }

        return $this;
    }

    /**
     * Return product IDs by CAP_CODE attribute
     *
     * @param int $capId
     *
     * @return array
     */
    protected function _loadProductByCapId($capId = 0)
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToFilter('cap_code', $capId);
        $IDs = [];
        foreach ($collection as $product) {
            $IDs[] = $product->getId();
        }

        return $IDs;
    }

    /**
     * Retrieve Usage Help Message
     *
     * @return  string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f financing_data_import.php -- [COMMAND] [OPTIONS]

import                                  Import Finance Data and map with products
help                                    This help
USAGE;
    }
}

$shell = new Rockar_Shell_Financing_Data_Import();
$shell->run();
