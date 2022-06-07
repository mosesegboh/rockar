<?php
require_once dirname(__FILE__) . '/../abstract.php';

/**
 * Script to update vehicle_condition column for older orders with default value
 *
 * @category Rockar
 * @package Rockar\Shell
 * @author Taras Kapushchak <info@scandiweb.com>
 * @copyright Copyright (c) 2016 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
class Rockar_Shell_Update_Vehicle_Condition extends Rockar_Shell_Abstract
{
    const UPDATE_DATA = 'update_data';
    const REINSTALL_ORDER_ADDITIONAL_DATA = 'reinstall_data';

    protected $_attributeCode = 'vehicle_condition';
    protected $_attributeDefaultValue = 'New';

    /**
     * Tables where values should be updated
     * @var array
     */
    protected $_tables = array(
        'sales/order',
        'sales/order_grid',
        'sales/order_item',
        'sales/quote_item',
    );

    /**
     * Update values for needed tables
     *
     * @return $this
     */
    protected function _updateValues()
    {
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $this->_getWriteAdapter();

        $defaultVehicleCondition = $this->_getDefaultValue();

        $this->_showMessage('Starting update', 'magenta');
        $this->_showMessage('==========================================', 'yellow');

        foreach ($this->_tables as $tableName) {
            $table = $resource->getTableName($tableName);

            $rowsCount = $writeAdapter->update(
                $table,
                array($this->_attributeCode => $defaultVehicleCondition),
                "{$this->_attributeCode} IS NULL"
            );

            $this->_showMessage(sprintf('%s records for "%s" were updated.', $rowsCount, $tableName));
        }

        $this->_showMessage('==========================================', 'yellow');
        $this->_showMessage('DONE.', 'green');

        return $this;
    }

    /**
     * Find needed option ID
     *
     * @return null
     */
    protected function _getDefaultValue()
    {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $this->_attributeCode);
        foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
            if ($option['label'] === $this->_attributeDefaultValue) {
                return $option['value'];
            }
        }

        return null;
    }

    public function run()
    {
        $command = $this->_getCliCommand();

        switch ($command) {
            case self::UPDATE_DATA;
                $this->_updateValues();
                break;
            case 'help':
                /* Usage help or unrecognised command */
                $this->_appendResponse($this->usageHelp());
                break;
            default:
                $this->_appendResponse('Invalid command', 'red');
                $this->_appendResponse('Please add "-- help" parameter to show script usage.', 'green');
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
Usage:  php -f update_vehicle_condition.php -- [COMMAND]

Available commands:
update_data                             Update empty values to the default one (New)
help                                    This help

Example:
php -f update_vehicle_condition.php -- update_data

USAGE;
    }
}

$shell = new Rockar_Shell_Update_Vehicle_Condition();
$shell->run();

