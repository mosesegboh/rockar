<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Api_FinanceOptions extends Mage_Core_Model_Abstract
{
    /**
     * Create array with all finance variables unique names and ids.
     *
     * @return array
     */
    private function _getAllFinanceVariables()
    {
        $allVariables = Mage::getModel('rockar_financingoptions/variables')->getCollection()
            ->addFieldToSelect('variable_id')
            ->addFieldToSelect('variable');
        $financeVariables = [];

        foreach ($allVariables as ['variable_id' => $variableId, 'variable' => $variableName]) {
            $financeVariables[$variableName] = $variableId;
        }

        return $financeVariables;
    }

    /**
     * Create array with all finance groups websites and ids.
     *
     * @return array
     */
    private function _getAllFinanceGroups()
    {
        $allGroups = Mage::getModel('rockar_financingoptions/group')->getCollection()
            ->addFieldToSelect([
                'group_id',
                'method_type',
                'website'
            ]);
        $financeGroups = [];

        foreach ($allGroups as ['group_id' => $groupId, 'method_type' => $methodType, 'website' => $website]) {
            $financeGroups[$website][$methodType] = $groupId;
        }

        return $financeGroups;
    }

    /**
     * Create array with stores
     *
     * @return array
     */
    private function _getStores()
    {
        $prefixes = [
            'bmw',
            'mini',
            'motorrad'
        ];
        $storeViewModel = Mage::getModel('core/store');
        $storeIds = [];
        foreach ($prefixes as $prefix) {
            $storeIds[$prefix] = $storeViewModel->load($prefix . '_store_view', 'code')->getId();
        }

        return $storeIds;
    }

    /**
     * From DLM Brand Code to DSP website.
     *
     * @param integer $brandCode
     *
     * @return integer
     */
    private function _fromBrandCodeToWebsite($brandCode)
    {
        switch ($brandCode) {
            case 'PBBMW':
                $website = 'Bmw';
                break;
            case 'PBMIN':
                $website = 'Mini';
                break;
            case 'PBBMC':
                $website = 'Motorrad';
                break;
            default:
                $website = 0;
                break;
        }

        return $website;
    }

    /**
     * Create finance option.
     *
     * @param array $financeData
     * 
     * @return mixed
     */
    public function createFinanceOption($financeData)
    {
        $websiteField = 'website' . $this->_fromBrandCodeToWebsite($financeData['brand_code']);

        $financeVariables = $this->_getAllFinanceVariables();
        $financeGroups = $this->_getAllFinanceGroups();
        $storesIds = $this->_getStores();

        $record = [
            'title' => $financeData['title'],
            'type' => $financeData['payment_type'],
            'position' => $financeData['position'],
            'minimum_amount_of_finance' => $financeData['minimum_amount_of_finance'],
            'finance_type' => $financeData['finance_type'],
            'pay_in_full' => 0,
            'customer_groups' => implode(
                ',',
                Mage::getModel('customer/group')->getCollection()
                    ->getAllIds()
            )
        ];

        switch ($financeData['option_group_id']) {
            case 1:
                $xmlTemplate = 'select_default.xml';
                break;
            case 3:
                $xmlTemplate = 'instalment_default.xml';
                break;
            default:
                $xmlTemplate = 'select_default.xml';
                break;
        }

        $filePath = Mage::getConfig()->getModuleDir('', 'Peppermint_FinancingOptions') . DS . 'templates' . DS . $xmlTemplate;
        $xml = simplexml_load_string(file_get_contents($filePath), 'Varien_Simplexml_Element');

        foreach ($xml->fields->children() as $data) {
            $name = $data->getAttribute('name');

            if (!isset($record[$name])) {
                if (isset($data->{$websiteField})) {
                    $record[$name] = trim((string) $data->{$websiteField});
                } else {
                    $record[$name] = trim((string) $data->default);
                }
            }
        }

        $record['stores'] = $storesIds[$record['stores']];
        $record['group_id'] = $financeGroups[$record['stores']][$record['method_type']];

        $newVariablesFO = [];

        foreach ($xml->financeOverlay->children() as $data) {
            if (isset($data->sort->{$websiteField})) {
                $newVariablesFO[$financeVariables[(string) $data->name]]['sort_order'] = (int) trim($data->sort->{$websiteField});
            } else {
                $newVariablesFO[$financeVariables[(string) $data->name]]['sort_order'] = (int) trim($data->sort);
            }

            if (isset($data->title->{$websiteField})) {
                $newVariablesFO[$financeVariables[(string) $data->name]]['variable_title'] = trim((string) $data->title->{$websiteField});
            } else {
                $newVariablesFO[$financeVariables[(string) $data->name]]['variable_title'] = trim((string) $data->title);
            }
        }

        $newVariablesFQ = [];

        foreach ($xml->financeQuote->children() as $data) {
            $newVariablesFQ[$financeVariables[(string) $data->name]]['sort_order'] = (int) trim($data->sort);
            $newVariablesFQ[$financeVariables[(string) $data->name]]['variable_title'] = trim((string) $data->title);
        }

        $record['variables_link_data'] = $newVariablesFO;
        $record['pdp_variables_link_data'] = $newVariablesFQ;

        $option = Mage::getModel('rockar_financingoptions/options')->setData($record)
            ->save();

        Mage::dispatchEvent('adminhtml_financing_options_save_after', ['option' => $option]);

        return $option->getId();
    }

    /**
     * Create term for finance option.
     *
     * @param integer $financeOptionId
     * @param array $financeDataRecord
     *
     * @return void
     */
    public function createTerm($financeOptionId, $financeDataRecord)
    {
        Mage::getModel('rockar_financingoptions/terms')->setData(
            [
                'term' => $financeDataRecord['term'],
                'option_id' => $financeOptionId,
                'custom_cash_deposit_minimum' => $this->_checkGetArrayKey('minimum_deposit', $financeDataRecord, 0),
                'custom_cash_deposit_maximum' => $this->_checkGetArrayKey('maximum_deposit', $financeDataRecord, 0),

                'individual_fee_monthly' => $this->_checkGetArrayKey('individual_fee_monthly', $financeDataRecord),
                'individual_fee_capitalised' => $this->_checkGetArrayKey('individual_fee_capitalised', $financeDataRecord),
                'corporate_fee_monthly' => $this->_checkGetArrayKey('corporate_fee_monthly', $financeDataRecord),
                'corporate_fee_capitalised' => $this->_checkGetArrayKey('corporate_fee_capitalised', $financeDataRecord),

                'interest_rate' => $this->_checkGetArrayKey('interest_rate', $financeDataRecord),
                'excess_mileage_charge' => $this->_checkGetArrayKey('excess_mileage', $financeDataRecord),

                // removed
                'finance_facility_fee' => $this->_checkGetArrayKey('facility_fee', $financeDataRecord),
                'purchase_fee' => $this->_checkGetArrayKey('purchase_fee', $financeDataRecord),
                'representative_apr' => $this->_checkGetArrayKey('representative_apr', $financeDataRecord),
                'mileage_over_term_of_contract' => null
            ]
        )->save();
    }

    /**
     * Delete all finance options except CASH.
     *
     * @return void
     */
    public function deleteFinanceOptions()
    {
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('rockar_financingoptions/options');
        $resource->getConnection('core_write')
            ->delete($tableName, ['pay_in_full != 1 OR pay_in_full IS NULL']);
    }

    /**
     * Check array key exist.
     *
     * @param string $key
     * @param mixed $arrayData
     * @param mixed $default
     *
     * @return mixed
     */
    protected function _checkGetArrayKey($key, $arrayData, $default = null)
    {
        return $arrayData[$key] ?? $default;
    }
}
