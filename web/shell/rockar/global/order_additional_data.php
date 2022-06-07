<?php
require_once dirname(__FILE__) . '/../abstract.php';

/**
 * Script to populate fields for order additional data
 *
 * @category Rockar
 * @package Rockar\Shell
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2016 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
class Rockar_Shell_Order_Additional_Data extends Rockar_Shell_Abstract
{
    const INSTALL_ORDER_ADDITIONAL_DATA = 'install_data';
    const REINSTALL_ORDER_ADDITIONAL_DATA = 'reinstall_data';

    /**
     * Dropdown values for "Common Status Point" field
     *
     * @var array
     */
    protected $_factoryOrderDropdownValues = array(
        'common_status_point' => array(
            '30 - Order Confirmed',
            '35 - Submitted',
            '40 - Order Committed',
            '110 - Order Being Built',
            '112 - Order Built',
            '135 - Accepted by Sales',
            '140 - Despatched',
            '145 - Into Port of Exit or Local Compound',
            '155 - Into Port of Entry or Receiving Compound',
            '160 - In Transit to Dealer',
            '165 - Arrived at Dealer',
            '175 - Customer Handover',
        ),
        'order_origins' => array(
            'Affiliate',
            'Aggregator / Comparison Site',
            'ClubRockar',
            'Direct Marketing',
            'Employee Scheme',
            'Instore',
            'Manufacturer Lead',
            'Referall - Customer',
            'Referall - Manufacturer',
            'Referall - Rockar',
            'Social Media Campaign',
        ),
    );

    /**
     * Get "Order Origin" option values
     * @var array
     */
    protected $_orderStatusOptions = array(
        'order_status' => array(
            'Awaiting Production Date',
            'Unconfirmed Build',
            'Confirmed Build',
            'Factory Delay',
            'Vehicle Build Awaiting Dispatch',
            'Delay In Transit',
            'Dealer Transfer',
            'Dealership Stock',
            'Delivery Arranged',
            'Delivered',
            'Vehicle Rejected',
            'Cancelled',
        ),
    );

    /**
     * Get "Build Week" option values
     * @return array
     */
    protected function _getBuildWeeksOptions()
    {
        $result = array();

        for ($i = 1; $i < 53; $i++) {
            $result[] = sprintf('Week %s', $i);
        }

        return $result;
    }

    /**
     * Install field data for "Factory Order Details" form
     */
    protected function _installFactoryOrderDetails()
    {
        $this->_showMessage('Installing "Factory Order Details" form fields...', 'magenta');
        $fieldsTable = Mage::getModel('rockar_sales/order_additional_fields')->getResource()->getMainTable();
        $fieldOptionsTable = Mage::getModel('rockar_sales/order_additional_field_options')->getResource()->getMainTable();
        $formCode = Rockar_Sales_Block_Adminhtml_Order_View_Tab_Additional_Details::FORM_CODE;
        $writeAdapter = $this->_getWriteAdapter();
        $optionsToInsert = array();

        $fields = array(
            array(
                'form_code' => $formCode,
                'type' => 'text',
                'field_code' => 'factory_order_number',
                'position' => '0',
                'is_system' => '1',
                'label' => 'Factory Order Number',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'text',
                'field_code' => 'dealer_reference',
                'position' => '5',
                'is_system' => '1',
                'label' => 'Dealer Reference',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'text',
                'field_code' => 'vin_number',
                'position' => '10',
                'is_system' => '1',
                'label' => 'VIN Number',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'date_order_placed',
                'position' => '15',
                'is_system' => '1',
                'label' => 'Date Order Placed',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'last_amendant_date',
                'position' => '20',
                'is_system' => '1',
                'label' => 'Last Amendant Date',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'select',
                'field_code' => 'common_status_point',
                'position' => '25',
                'is_system' => '1',
                'label' => 'Common Status Point',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'select',
                'field_code' => 'order_origins',
                'position' => '30',
                'is_system' => '1',
                'label' => 'Order Origins',
            ),
        );

        /**
         * Insert factory order details fields
         */
        $fieldsCount = $writeAdapter->insertMultiple($fieldsTable, $fields);
        $this->_showMessage('==========================================', 'yellow');
        $this->_showMessage('DONE.', 'green');
        $this->_showMessage(sprintf('%s record(s) were added.', $fieldsCount));

        $this->_showMessage('Installing options for "Factory Order Details" dropdown fields...', 'magenta');
        $fieldsCount = 0;
        foreach ($this->_factoryOrderDropdownValues as $fieldCode => $values) {
            $fieldModel = Mage::getModel('rockar_sales/order_additional_fields')->load($fieldCode, 'field_code');

            if ($fieldModel->getId()) {
                foreach ($values as $value) {
                    $optionsToInsert[] = array(
                        'field_id' => $fieldModel->getId(),
                        'label' => $value,
                        'is_system' => 1,
                    );
                }
            }
        }

        if (!empty($optionsToInsert)) {
            $fieldsCount = $writeAdapter->insertMultiple($fieldOptionsTable, $optionsToInsert);
        }

        $this->_showMessage('==========================================', 'yellow');
        $this->_showMessage('DONE.', 'green');
        $this->_showMessage(sprintf('%s record(s) were added.', $fieldsCount));
    }

    /**
     * Install field data for "Delivery & Handover" form
     */
    protected function _installDeliveryHandoverData()
    {
        $this->_showMessage('Installing "Delivery & Handover" form fields...', 'magenta');
        $fieldsTable = Mage::getModel('rockar_sales/order_additional_fields')->getResource()->getMainTable();
        $formCode = Rockar_Sales_Block_Adminhtml_Order_View_Tab_Additional_Delivery::FORM_CODE;
        $writeAdapter = $this->_getWriteAdapter();

        $fields = array(
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'requested_handover_date',
                'position' => '0',
                'is_system' => '1',
                'label' => 'Requested Handover Date',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'confirmed_handover_date',
                'position' => '5',
                'is_system' => '1',
                'label' => 'Confirmed Handover Date',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'text',
                'field_code' => 'confirmed_handover_time',
                'position' => '10',
                'is_system' => '1',
                'label' => 'Confirmed Handover Time',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'textarea',
                'field_code' => 'handover_special_notes',
                'position' => '15',
                'is_system' => '1',
                'label' => 'Handover Special Notes',
            ),
        );

        /**
         * Insert "Delivery & Handover" fields
         */
        $fieldsCount = $writeAdapter->insertMultiple($fieldsTable, $fields);

        $this->_showMessage('==========================================', 'yellow');
        $this->_showMessage('DONE.', 'green');
        $this->_showMessage(sprintf('%s record(s) were added.', $fieldsCount));
    }

    /**
     * Install field data for "Order Status" form
     */
    protected function _installOrderStatusData()
    {
        $this->_showMessage('Installing "Order Status" form fields...', 'magenta');
        $fieldsTable = Mage::getModel('rockar_sales/order_additional_fields')->getResource()->getMainTable();
        $fieldOptionsTable = Mage::getModel('rockar_sales/order_additional_field_options')->getResource()->getMainTable();
        $formCode = Rockar_Sales_Block_Adminhtml_Order_View_Tab_Additional_Status::FORM_CODE;
        $writeAdapter = $this->_getWriteAdapter();

        $fields = array(
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'requested_build_date',
                'position' => '0',
                'is_system' => '1',
                'label' => 'Requested Build Date',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'accepted_build_date',
                'position' => '5',
                'is_system' => '1',
                'label' => 'Accepted Build Date',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'factory_release_date',
                'position' => '10',
                'is_system' => '1',
                'label' => 'Factory Release Date',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'compound_receipt_date',
                'position' => '15',
                'is_system' => '1',
                'label' => 'Compound Receipt Date',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'gate_release_date',
                'position' => '20',
                'is_system' => '1',
                'label' => 'Gate Release Date',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'a_d_t_c',
                'position' => '25',
                'is_system' => '1',
                'label' => 'A.D.T.C.',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'select',
                'field_code' => 'build_week',
                'position' => '35',
                'is_system' => '1',
                'label' => 'Build Week',
            ),
        );

        /**
         * Insert "Order Status" form fields
         */
        $fieldsCount = $writeAdapter->insertMultiple($fieldsTable, $fields);

        $this->_showMessage('==========================================', 'yellow');
        $this->_showMessage('DONE.', 'green');
        $this->_showMessage(sprintf('%s record(s) were added.', $fieldsCount));

        $this->_showMessage('Installing options for "Order Status" form dropdown fields...', 'magenta');
        $fieldsCount = 0;
        $optionsToInsert = array();
        $dropdownValues = array('build_week' => $this->_getBuildWeeksOptions());

        foreach ($dropdownValues as $fieldCode => $values) {
            $fieldModel = Mage::getModel('rockar_sales/order_additional_fields')->load($fieldCode, 'field_code');

            if ($fieldModel->getId()) {
                foreach ($values as $value) {
                    $optionsToInsert[] = array(
                        'field_id' => $fieldModel->getId(),
                        'label' => $value,
                        'is_system' => 1,
                    );
                }
            }
        }

        if (!empty($optionsToInsert)) {
            $fieldsCount = $writeAdapter->insertMultiple($fieldOptionsTable, $optionsToInsert);
        }

        $this->_showMessage('==========================================', 'yellow');
        $this->_showMessage('DONE.', 'green');
        $this->_showMessage(sprintf('%s record(s) were added.', $fieldsCount));
    }

    /**
     * Install field data for "Vehicle Documentation" form
     */
    protected function _installVehicleDocumentationData()
    {
        $this->_showMessage('Installing "Vehicle Documentation" form fields...', 'magenta');
        $fieldsTable = Mage::getModel('rockar_sales/order_additional_fields')->getResource()->getMainTable();
        $formCode = Rockar_Sales_Block_Adminhtml_Order_View_Tab_Additional_Documentation::FORM_CODE;
        $writeAdapter = $this->_getWriteAdapter();

        $fields = array(
            array(
                'form_code' => $formCode,
                'type' => 'date',
                'field_code' => 'first_registration_date',
                'position' => '0',
                'is_system' => '1',
                'label' => 'Date of First Registration',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'checkbox',
                'field_code' => 'cor_issued',
                'position' => '5',
                'is_system' => '1',
                'label' => 'COR Issued',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'checkbox',
                'field_code' => 'cor_received',
                'position' => '10',
                'is_system' => '1',
                'label' => 'COR Received',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'checkbox',
                'field_code' => 'afrl_issued',
                'position' => '15',
                'is_system' => '1',
                'label' => 'AFRL Issued',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'checkbox',
                'field_code' => 'afrl_received',
                'position' => '20',
                'is_system' => '1',
                'label' => 'AFRL Received',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'checkbox',
                'field_code' => 'cherished_plate',
                'position' => '25',
                'is_system' => '1',
                'label' => 'Cherished Plate',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'text',
                'field_code' => 'key_number',
                'position' => '0',
                'is_system' => '1',
                'label' => 'Key Number',
            ),
            array(
                'form_code' => $formCode,
                'type' => 'text',
                'field_code' => 'engine_number',
                'position' => '0',
                'is_system' => '1',
                'label' => 'Engine Number',
            ),
        );

        /**
         * Insert "Vehicle Documentation" fields
         */
        $fieldsCount = $writeAdapter->insertMultiple($fieldsTable, $fields);

        $this->_showMessage('==========================================', 'yellow');
        $this->_showMessage('DONE.', 'green');
        $this->_showMessage(sprintf('%s record(s) were added.', $fieldsCount));
    }

    /**
     * Remove all system attributes to run fresh reinstall process.
     */
    protected function _removeSystemData()
    {
        $this->_showMessage('Removing system attributes...', 'magenta');

        $fieldsTable = Mage::getModel('rockar_sales/order_additional_fields')->getResource()->getMainTable();
        $writeAdapter = $this->_getWriteAdapter();
        $recordsCount = $writeAdapter->delete($fieldsTable, 'is_system = 1');

        $this->_showMessage('==========================================', 'yellow');
        $this->_showMessage('DONE.', 'green');
        $this->_showMessage(sprintf('%s record(s) were deleted.', $recordsCount));
    }

    public function run()
    {
        $command = $this->_getCliCommand();

        switch ($command) {
            case self::INSTALL_ORDER_ADDITIONAL_DATA;
                $this->_installFactoryOrderDetails();
                $this->_installDeliveryHandoverData();
                $this->_installOrderStatusData();
                $this->_installVehicleDocumentationData();
                break;
            case self::REINSTALL_ORDER_ADDITIONAL_DATA:
                $this->_removeSystemData();

                $this->_installFactoryOrderDetails();
                $this->_installDeliveryHandoverData();
                $this->_installOrderStatusData();
                $this->_installVehicleDocumentationData();
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
Usage:  php -f data_fixes.php -- [COMMAND]

Available commands:
install_data                            Install initial additional order fields
reinstall_data                          Reinstall system order fields
help                                    This help

Example:
php -f order_additional_data.php -- install_data

USAGE;
    }
}

$shell = new Rockar_Shell_Order_Additional_Data();
$shell->run();
