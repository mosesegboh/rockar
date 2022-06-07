<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Helper_Product_Fail extends Mage_Core_Helper_Abstract
{
    /**
     * Wrapper method to save or delete data from product_importer_product_fail table
     *
     * @param string $vinNum
     * @param string jsonData
     * @param bool $isDelete
     *
     * @return Peppermint_Importer_Helper_Product_Fail
     */
    public function syncWithProductFailTable($vinNum, $jsonData = null, $isDelete = false)
    {
        try {
            $data = $this->_prepareProductFailLogData($vinNum, $jsonData, $isDelete);

            $isDelete
                ? Mage::getResourceModel('peppermint_importer/product_fail')->deleteEntry($data)
                : Mage::getResourceModel('peppermint_importer/product_fail')->insertEntry($data);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Prepare product failure log data to be inserted or delete
     *
     * @param string $vinNum
     * @param string jsonData
     * @param bool $isDelete
     *
     * @return array
     */
    protected function _prepareProductFailLogData($vinNum, $jsonData, $isDelete)
    {
        $result = ['vin' => $vinNum];

        if (!$isDelete) {
            $result['retry_count'] = $this->getProductLogFailureCount($vinNum) + 1;
            $result['product_data'] = $jsonData ?? '';
            $result['updated_at'] = Varien_Date::now();
        }

        return $result;
    }

    /**
     * Get vin failure count
     *
     * @param string $vinNum
     *
     * @return int
     */
    public function getProductLogFailureCount($vinNum)
    {
        return (int) Mage::getModel('peppermint_importer/product_fail')->getCollection()
            ->addFieldToFilter('vin', $vinNum)
            ->addFieldToSelect('retry_count')
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem()
            ->getRetryCount();
    }
}
