<?php

/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_FinancingOptions_Model_Api_Sync
 */
class Peppermint_FinancingOptions_Model_Api_Sync extends Rockar_FinancingOptions_Model_Api_Sync
{
    public function sync($page)
    {
        $result = [];

        try {
            $this->_page = $page;
            $recordsLeft = 0;

            if ($this->_page == 1) {
                $recordsLeft = $this->_deleteDataCollection();
            }

            if ($recordsLeft) {
                $result['page'] = $this->_page;
                $result['status'] = self::HTTP_SUCCESS;
                $processedMessage = $this->_helper()->__('Deleting finance data. Records left: %s', $recordsLeft);
                $this->setFlag($processedMessage);
                $message[] = $processedMessage;
                $result['message'] = $message;

                return $result;
            }

            if ($this->_page == 1) {
                Mage::getModel('peppermint_financingoptions/api_financeOptions')->deleteFinanceOptions();
                $this->_beforeStart();
                // Sleep for 5 seconds to allow all DB instances to catch up (possible problem in write MASTER -> read SLAVE DB setups)
                sleep(5);
            }

            $message = [];
            $configHelper = Mage::helper('financing_options/config');

            $requestData = [
                'url' => $configHelper->getApiUrl(),
                'post' => [
                    'username' => $configHelper->getApiUsername(),
                    'password' => $configHelper->getApiPassword(),
                    'page' => $this->_page,
                ],
            ];
            $this->_validateRequest($requestData);
            $response = Mage::helper('core')->jsonDecode($this->_runRequest($requestData));
            $this->_validateResult($response);

            if (isset($response['count']) && isset($response['page_size'])) { // backward comatipility for old version
                $response['total'] = $response['count'];
                $response['per_page'] = $response['page_size'];
            }

            $this->_pageSize = $response['per_page'];
            $this->_count = $response['total'];

            $this->_lastPage = ceil($this->_count / $this->_pageSize);
            if ($this->_page > $this->_lastPage) {
                throw new Exception($this->_helper()->__('Not valid response. Please try again!'));
            }

            if ($this->_page == 1) {
                $message[] = $this->_helper()->__('Total number of records to import: %s', $this->_count);
            }
            try {
                $this->_saveData($response['data']);
            } catch (Exception $e) {
                $result['message'] = htmlentities($e->getMessage());
                $result['error'] = htmlentities($e->getMessage());
                $result['status'] = self::HTTP_ERROR;
                $this->_afterCompleteFinish();
                return $result;
            }

            /**
             * Prepare result
             */
            $processedMessage = $this->_getProcessedMessage($this->_page, $this->_lastPage);
            $message[] = $processedMessage;
            $this->setFlag($processedMessage);

            if ($this->_page < $this->_lastPage) {
                $result['page'] = $this->_page + 1;
            } else {
                $result['continue'] = 'beforeMap';
                $message[] = $this->_helper()->__('All data imported');
                $this->_afterCompleteFinish();
            }
            $result['status'] = self::HTTP_SUCCESS;
            $result['message'] = $message;
        } catch (Exception $e) {
            $result['message'] = htmlentities($e->getMessage());
            $result['error'] = htmlentities($e->getMessage());
            $result['status'] = self::HTTP_ERROR;
            $this->_afterCompleteFinish();
        }

        return $result;
    }

    /**
     * Save Data in Database
     *
     * @param array $data
     * @param int $isCustom
     *
     * @return $this
     */
    protected function _saveData($data, $isCustom = 0)
    {
        $date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $financeOptionsCollection = Mage::getModel('rockar_financingoptions/options')->getCollection()
            ->addFieldToSelect(['type', 'options_id']);
        $financeOptions = [];

        foreach ($financeOptionsCollection as $option) {
            $financeOptions[$option->getType()] = $option->getId();
        }

        $termsCollection = Mage::getModel('rockar_financingoptions/terms')->getCollection()
            ->addFieldToSelect(['term', 'option_id']);
        $terms = [];

        foreach ($termsCollection as $term) {
            $terms[$term->getOptionId()][] = $term->getTerm();
        }

        $insertData = [];
        foreach ($data as $item) {

            $item['payment_type'] = strtolower($item['payment_type']);

            $insertData[] = [
                'capid' => $item['asset_id'],
                'term' => $item['term'],
                'annual_mileage' => $this->_checkGetArrayKey('total_contract_distance', $item),
                'payment_type' => $item['payment_type'],
                'manufacture_deposit' => $item['manufacture_deposit'],
                'dealer_deposit' => $this->_checkGetArrayKey('dealer_deposit', $item),
                'optional_final_payment' => $item['optional_final_payment'],
                'created_at' => $date,
                'updated_at' => $date,
                'commercial' => $this->_checkGetArrayKey('commercial', $item),
                'excess_mileage' => $this->_checkGetArrayKey('excess_mileage', $item),
                'is_custom' => $isCustom,
                'rate_subvention_type' => $this->_checkGetArrayKey('rate_subvention_type', $item),
                'rate_subvention_value' => $this->_checkGetArrayKey('rate_subvention_value', $item),
                'applicable_stock_type' => $this->_checkGetArrayKey('applicable_stock_type', $item),
                'maximum_amount_of_finance' => $this->_checkGetArrayKey('maximum_amount_of_finance', $item),
                'minimum_amount_of_finance' => $this->_checkGetArrayKey('minimum_amount_of_finance', $item),
                'max_balloon_percent' => $this->_checkGetArrayKey('max_rv_percent', $item),
                'vehicle_type' => $item['vehicle_type'] ?? null,

                // removed
                'co2' => $this->_checkGetArrayKey('co2', $item),
                'p11d' => $this->_checkGetArrayKey('p11d', $item),
                'payment_plan' => $this->_checkGetArrayKey('payment_plan', $item),
                'fuel_type' => $this->_checkGetArrayKey('fuel_type', $item),
                'lease_rental' => $this->_checkGetArrayKey('lease_rental', $item),
                'service_rental' => $this->_checkGetArrayKey('service_rental', $item),
                'met_paint_rrp' => $this->_checkGetArrayKey('met_paint_rrp', $item),
                'met_paint_rental' => $this->_checkGetArrayKey('met_paint_rental', $item),
                'premium_paint_rrp' => $this->_checkGetArrayKey('premium_paint_rrp', $item),
                'premium_paint_rental' => $this->_checkGetArrayKey('premium_paint_rental', $item),
                'ultra_metallic_paint_rrp' => $this->_checkGetArrayKey('ultra_metallic_paint_rrp', $item),
                'ultimate_metallic_paint_rental' => $this->_checkGetArrayKey('ultimate_metallic_paint_rental', $item),
                'special_paints_rrp' => $this->_checkGetArrayKey('special_paints_rrp', $item),
                'special_paints_rental' => $this->_checkGetArrayKey('special_paints_rental', $item),
                'option_rental_price_pr1' => isset($item['option_rental_price_per_1'])
                    ? $this->_checkGetArrayKey('option_rental_price_per_1', $item)
                    : $this->_checkGetArrayKey('option_rental_price_pr_1', $item), // backward compatibility
                'finance_emc_ppm' => $this->_checkGetArrayKey('finance_emc_ppm', $item),
                'service_emc_ppm' => $this->_checkGetArrayKey('service_emc_ppm', $item),
            ];

            if (isset($financeOptions[$item['payment_type']])) {
                $financeOptionId = $financeOptions[$item['payment_type']];
            } else {
                $financeOptionId = Mage::getModel('peppermint_financingoptions/api_financeOptions')->createFinanceOption($item);
                $financeOptions[$item['payment_type']] = $financeOptionId;
            }

            if (!isset($terms[$financeOptionId]) || !in_array($item['term'], $terms[$financeOptionId])) {
                Mage::getModel('peppermint_financingoptions/api_financeOptions')->createTerm($financeOptionId, $item);
                $terms[$financeOptionId][] = $item['term'];
            }
        }

        $write = Mage::getSingleton("core/resource")->getConnection("core_write");
        $table = $this->_dataModel->getResource()->getMainTable();
        $write->insertMultiple($table, $insertData);

        return $this;
    }

    /**
     * Check array key exist
     *
     * @param string $key
     * @param array $array
     *
     * @return mixed
     */
    protected function _checkGetArrayKey($key, $array, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}
