<?php
require_once dirname(__FILE__) . '/../abstract.php';

/**
 * Script regenerate financing products calculations files
 *
 * @category  Rockar
 * @package   Rockar\Shell
 * @author    Valerijs Sceglovs <info@scandiweb.com>
 * @copyright Copyright (c) 2016 Scandiweb, Ltd (http://scandiweb.com)
 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
class Rockar_Shell_Financing_Calculation extends Rockar_Shell_Abstract
{
    /**
     * Remove all products from particular website.
     *
     * @param $websiteCode
     *
     * @return int
     */
    protected function _regenerate($websiteCode)
    {
        $currentStore = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $options = Mage::getModel('rockar_financingoptions/options')->getCollection();
        $size = $options->getSize();
        foreach ($options as $option) {
            /**
             * @var Rockar_FinancingOptions_Model_Options $option
             */
            $option = $option->load($option->getId());
            /**
             * Simulate option save to re-generate calculation custom file
             */
            Mage::dispatchEvent('adminhtml_financing_options_save_after', array('option' => $option));
        }

        Mage::app()->getStore()->setId($currentStore);

        $this->_showMessage(sprintf('%s financing products calculations files were regenerated', $size), 'green');
    }

    /**
     * Script execution method
     */
    public function run()
    {
        $command = $this->_getCliCommand();

        switch ($command) {
            case 'regenerate':
                $this->_showMessage('Starting Regeneration of Financing Products Calculations Files', 'magenta');

                $this->_regenerate($command);

                $this->_showMessage('==========================================', 'yellow');

                $this->_showMessage('DONE.', 'green');
                break;
            case 'help':
                /* Usage help or unrecognised command */
                $this->_appendResponse($this->usageHelp());
                break;
            default:
                $this->_appendResponse('Invalid command', 'red');
                $this->_appendResponse($this->usageHelp());
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
Usage:  php -f financing_calculation.php -- [COMMAND] --[OPTIONS]

Available commands:
regenerate                              Run Financing Products Calculations Files
help                                    This help

Example:
php -f financing_calculation.php -- regenerate

USAGE;
    }
}

$shell = new Rockar_Shell_Financing_Calculation();
$shell->run();
