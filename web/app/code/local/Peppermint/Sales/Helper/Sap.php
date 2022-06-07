<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Helper_Sap extends Mage_Core_Helper_Abstract
{
    /**
     * @var int order id
     */
    protected $_orderId;

    /**
     * @var int count
     */
    protected $_count;

    /**
     * Wrapper method to save or delete data from sap fail table
     *
     * @param int $orderId
     * @param bool $isDelete
     *
     * @return Peppermint_Sales_Helper_Sap
     */
    public function syncWithOrderSapLog($orderId, $isDelete = false)
    {
        try {
            $isLimitReached = $this->_isRetryLimitExceeded($orderId);
            $isDelete = ($isDelete || $isLimitReached);

            $data = $this->_prepareSapLogData($orderId, $isDelete);

            $isDelete
                ? Mage::getResourceModel('peppermint_sales/sap_fail')->deleteEntry($data)
                : Mage::getResourceModel('peppermint_sales/sap_fail')->insertEntry($data);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Prepare sap failure log data to be inserted or delete
     *
     * @param int $orderId
     * @param bool $isDelete
     *
     * @return array
     */
    protected function _prepareSapLogData($orderId, $isDelete)
    {
        $result = ['order_id' => $orderId];

        if (!$isDelete) {
            $result['count'] = $this->getSapLogOrderFailureCount($orderId) + 1;
        }

        return $result;
    }

    /**
     * Check if retry limit has been exceeded
     *
     * @param int $orderId
     *
     * @return bool
     */
    protected function _isRetryLimitExceeded($orderId)
    {
        return $this->getSapLogOrderFailureCount($orderId) >=
            Mage::helper('peppermint_importer')->getSapRetryCronLimitConfig();
    }

    /**
     * Get orderId failure count
     *
     * @param int $orderId
     *
     * @return int
     */
    public function getSapLogOrderFailureCount($orderId)
    {
        if (!isset($this->_count) || $this->_orderId !== $orderId) {
            $this->_orderId = $orderId;

            $this->_count = (int) Mage::getModel('peppermint_sales/sap_fail')->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToSelect('count')
                ->setCurPage(1)
                ->setPageSize(1)
                ->getFirstItem()
                ->getCount();
        }

        return $this->_count;
    }
}
