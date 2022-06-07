<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_CreditApp extends Mage_Core_Model_Abstract
{
    /**
     * Callback method that will receive queue message and sending to be processed.
     *
     * @param string|array $msg
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    public function processCreditAppData($msg)
    {
        try {
            $creditAppData = is_array($msg)
                ? $msg
                : (Mage::helper('rockar_all')->jsonDecode($msg) ?: []);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        foreach ($creditAppData as $creditAppItem) {
            if ($this->_validateMessageItem($creditAppItem)) {
                $this->_updateOrderCreditApp(
                    $creditAppItem['orderId'],
                    $creditAppItem['creditAppId'],
                    $creditAppItem['creditAppStatus']
                );
            }
        }
    }

    /**
     * Check whether message item contains all needed fields of valid type
     *
     * @param array $messageItem
     * @return bool
     */
    protected function _validateMessageItem($messageItem)
    {
        $requiredFields = ['orderId', 'creditAppId', 'creditAppStatus'];

        foreach ($requiredFields as $field) {
            if (empty($messageItem[$field]) || !preg_match('/^[A-Za-z0-9_-]+$/', $messageItem[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update order and child orders with the provided credit app data
     *
     * @param string $incrementId
     * @param string $creditAppId
     * @param string $status
     * @return $this
     */
    protected function _updateOrderCreditApp($incrementId, $creditAppId, $status)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId, 'increment_id');

        if ($order->getId()) {
            try {
                $incrementBase = $order->getOriginalIncrementId() ?: $order->getIncrementId();

                $collection = $order->getCollection()
                    ->addFieldToFilter(
                        ['increment_id', 'original_increment_id'],
                        [
                            ['eq' => $incrementBase],
                            ['eq' => $incrementBase]
                        ]
                    );

                foreach ($collection as $orderToUpdate) {
                    $this->_saveOrderCreditAppData($orderToUpdate, $creditAppId, $status);
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $this;
    }

    /**
     * Save credit app data
     *
     * @param $order
     * @param string $creditAppId
     * @param string $status
     * @return void
     */
    protected function _saveOrderCreditAppData($order, $creditAppId, $status) {
        $order->setCreditAppId($creditAppId);
        $order->setCreditAppStatus($status);
        $order->save();

        $order->addStatusHistoryComment(
            Mage::helper('peppermint_importer')->__(
                'Credit Application "%s" status was updated: "%s"',
                $creditAppId,
                $status
            ),
            false
        )
            ->setIsVisibleOnFront(false)
            ->setIsCustomerNotified(false)
            ->save();
    }
}
