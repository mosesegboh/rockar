<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Order extends Mage_Core_Model_Abstract
{
    /**
     * @var string[]
     */
    protected $_status = [];

    /**
     * @var string
     */
    protected $_processStarted = '';

    /**
     * @var Sales_Model_orderItem|string
     */
    protected $_orderItem = '';

    /**
     * @var Catalog_Model_Product|string
     */
    protected $_productModel = '';

    /**
     * Constructor. Set basic parameters.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_orderItem = Mage::getModel('sales/order_item');
        $this->_productModel = Mage::getModel('catalog/product');
    }

    /**
     * Callback method that will receive queue message and sending to be processed.
     *
     * @param string $message
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function processOrders($message)
    {
        foreach (json_decode($message, true) as $key => $data) {
            if (!empty($data)) {
                switch ($key) {
                    case 'orderExpired':
                        echo "Running orders expired...\n";
                        $this->_processsExpiredOrders($data);
                        echo json_encode($this->_status['orderExpired'], true) . "\n";
                        break;
                    case 'updateAvailable':
                        echo "Running order updates...\n";
                        $this->_processUpdatedOrAddedOrders($data, $key);
                        echo json_encode($this->_status['updateAvailable'], true) . "\n";
                        break;
                    case 'orderExpiryDate':
                        echo "Running orders expiry date...\n";
                        $this->_processOrdersExpiryDate($data);
                        echo json_encode($this->_status['orderExpiryDate'], true) . "\n";
                        break;
                    case 'tracking':
                        echo "Running orders tracking...\n";
                        $this->_processTrackingOrders($data);
                        echo json_encode($this->_status['tracking'], true) . "\n";
                        break;
                    case 'deleteAvailable':
                        echo "Running order deletions...\n";
                        $this->_processDeletedOrders($data);
                        echo json_encode($this->_status['deleteAvailable'], true) . "\n";
                        break;
                    case 'addAvailable':
                        echo "Running order additions...\n";
                        $this->_processUpdatedOrAddedOrders($data, $key);
                        echo json_encode($this->_status['addAvailable'], true) . "\n";
                        break;
                    case 'orderAmendUnlock':
                        echo "Running order amend unlock...\n";
                        $this->_processUnlockAmendOrders($data);
                        echo json_encode($this->_status['orderAmendUnlock'] ?? [], true) . "\n";
                        break;
                    case 'orderPreRetail':
                        echo "Running order pre retail...\n";
                        $this->_processPreRetailedOrders($data);
                        echo json_encode($this->_status['orderPreRetail'], true) . "\n";
                        break;
                }
            }
        }
    }

    /**
     * Process expired orders.
     *
     * @param string[] $data
     * @return string[]
     */
    protected function _processsExpiredOrders($data)
    {
        $this->_processStarted = 'orderExpired';
        Mage::register('peppermint_order_expired_import', true, true);
        $cancelledVins = [];

        foreach ($data as $val) {
            $vinNumber = $val['vin'];
            // @todo Refactoring: extract productId not by getIdBySku
            $productId = $this->_productModel->getIdBySku($vinNumber);
            $this->_status[$this->_processStarted][$vinNumber] = 'order status processed to be Cancelled for: ' . $val['vin'];

            if ($productId) {
                $collectionData = $this->_orderItem->getCollection()
                    ->addFieldToSelect('order_id')
                    ->addFieldToFilter('product_id', $productId);

                foreach ($collectionData as $orderItem) {
                    /** @var Mage_Sales_Model_Order $order */
                    $order = Mage::getModel('sales/order')->load($orderItem->getOrderId());
                    $dspStatus = $val['dsp_status'] ?? false;

                    if ($order->getId()
                        && $dspStatus
                        && strtolower($dspStatus) === 'expired'
                        && $order->canCancel()
                    ) {
                        if ($order->getState() === $order::STATE_HOLDED && $order->canUnhold()) {
                            /* manually unhold order amendments before cancelling the order
                             * and don't send messages to avoid duplicates.
                             * set amendment_cancelled status for expired amendments
                             */
                            $order->setData('state', $order->getHoldBeforeState())
                                ->setStatus($order->getRelationChildId()
                                    ? $order->getHoldBeforeStatus()
                                    : Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED);
                        }
                        // set status to Order cancelled
                        $order->cancel()
                            ->save();

                        $this->_status[$this->_processStarted][$vinNumber] .= PHP_EOL . ' - found an order: ' . $order->getIncrementId();

                        if (!in_array($vinNumber, $cancelledVins)) {
                            $cancelledVins[] = $vinNumber;
                        }
                    }
                }
            }
        }

        unset($data); // clean php memory
        Mage::unregister('peppermint_order_expired_import');
        // Need to filter out all the vins not in the rockar_lead_time table
        // To avoid running unnecessary re-index and status update
        $cancelledVins = empty($cancelledVins) ? $cancelledVins : Mage::helper('peppermint_leadtime')->filterMissingVin($cancelledVins);

        if (!empty($cancelledVins)) {
            // appEmulation needed for product collection select in
            // Peppermint_LeadTime_Model_Observer_Product::updateProductStatusesAfterLeadTimeSave
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $adminEnvironmentEmulation = $appEmulation->startEnvironmentEmulation(Mage_Core_Model_App::ADMIN_STORE_ID);

            Mage::dispatchEvent('peppermint_order_expired_import_after', ['importedVins' => $cancelledVins]);

            $appEmulation->stopEnvironmentEmulation($adminEnvironmentEmulation);
        }

        return $this->_status;
    }

    /**
     * Process deleted orders.
     *
     * @param string[] $data
     * @return string[]
     */
    protected function _processDeletedOrders($data)
    {
        $this->_processStarted = 'deleteAvailable';
        $vinsList = [];

        foreach ($data as ['vin' => $vin]) {
            $this->_status[$this->_processStarted][$vin] = 'lead time deleted';
            $vinsList[] = $vin;
        }

        try {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $totalDeletions = $connection->delete(
                Mage::getModel('rockar_lead_time/lead_time')->getResource()->getMainTable(),
                $connection->quoteInto('identifier IN (?)', $vinsList)
            );

            $this->_status[$this->_processStarted]['total_deletions'] = $totalDeletions;

            if (!empty($vinsList)) {
                Mage::unregister('peppermint_import');

                $appEmulation = Mage::getSingleton('core/app_emulation');
                $adminEnvironmentEmulation
                    = $appEmulation->startEnvironmentEmulation(Mage_Core_Model_App::ADMIN_STORE_ID);

                Mage::dispatchEvent(
                    'peppermint_order_deleted_after',
                    ['importedVins' => $vinsList]
                );

                $appEmulation->stopEnvironmentEmulation($adminEnvironmentEmulation);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this->_status;
    }

    /**
     * Process Updated or Added orders.
     *
     * @param string[] $data
     * @param string $process
     *
     * @return string[]
     */
    protected function _processUpdatedOrAddedOrders($data, $process)
    {
        Mage::register('peppermint_import', true);

        $this->_processStarted = $process;
        $importedLeadTimeIds = [];

        foreach ($data as $val) {
            $vinNumber = $val['vin'];

            if (is_array($val['lead_time'] ?? false)) {
                $importedLeadTimeIds[] = $vinNumber;
                $this->_addOrUpdateProductLeadTime($val['lead_time']);
                $this->_status[$this->_processStarted][$vinNumber] = 'lead time updated_added';
            }
        }

        unset($data); // clean php memory
        Mage::unregister('peppermint_import');

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $adminEnvironmentEmulation = $appEmulation->startEnvironmentEmulation(Mage_Core_Model_App::ADMIN_STORE_ID);

        Mage::dispatchEvent(
            'peppermint_import_availability_after',
            ['importedVins' => $importedLeadTimeIds]
        );
        $appEmulation->stopEnvironmentEmulation($adminEnvironmentEmulation);

        return $this->_status;
    }

    /**
     * Add or update product lead time.
     *
     * @param string[] $leadTimeData
     *
     * @return void
     */
    protected function _addOrUpdateProductLeadTime($leadTimeData)
    {
        if (!empty($leadTimeData['identifier'])) {
            try {
                $query = '
                insert into ' .  Mage::getSingleton('core/resource')->getTableName('rockar_lead_time/lead_time') . ' (identifier, title, configuration, vista_order_number, available_on, available_in, minimum_days, custom_message, amount, `status`, hash)
                values (:leadtime, :leadtime, "", "", :availableon, :availablein, :minimumdays, NULL, :amount, :status, :hash)
                    on duplicate key update available_on = :availableon, available_in = :availablein, minimum_days = :minimumdays, hash = :hash;';

                $binds = [
                    'leadtime' => $leadTimeData['identifier'],
                    'availableon' => $leadTimeData['available_on'],
                    'availablein' => $leadTimeData['available_in'],
                    'minimumdays' => $leadTimeData['minimum_days'],
                    'amount' => $leadTimeData['amount'],
                    'status' => $leadTimeData['status'],
                    'hash' => $leadTimeData['hash']
                ];

                Mage::getSingleton('core/resource')->getConnection('core_write')->query($query, $binds);
            } catch (Exception $e) {
                Mage::log('Error creating/updating ' . $leadTimeData['identifer'] . " - " . $e->getMessage());
            }
        }
    }

    /**
     * Update expiry order date.
     *
     * @param string[] $data
     * @return string[]
     */
    protected function _processOrdersExpiryDate($data)
    {
        $this->_processStarted = 'orderExpiryDate';

        foreach ($data as $val) {
            $vinNumber = $val['vin'];
            // @todo Refactoring: extract productId not by getIdBySku
            $productId = $this->_productModel->getIdBySku($vinNumber);

            if ($productId) {
                $collectionData = $this->_orderItem->getCollection()
                    ->addFieldToSelect('order_id')
                    ->addFieldToFilter('product_id', $productId);

                foreach ($collectionData as $orderItem) {
                    $order = Mage::getModel('sales/order')->load($orderItem->getOrderId());

                    if ($order->getId()) {
                        $date = DateTime::createFromFormat('Ymd', $val['expired_date']);
                        $orderAdditional = Mage::getModel('rockar_checkout/order_additional')->load($order->getId(), 'order_id');

                        if ($date && $orderAdditional->getId()) {
                            $orderAdditional->addData(['expired_date' => $date->format('Y-m-d')])
                                ->save();
                            $this->_status[$this->_processStarted][$vinNumber] = 'order expiry date updated';
                        }
                    }
                }
            }
        }
        unset($data); // clean php memory

        return $this->_status;
    }

    /**
     * Process tracking orders.
     *
     * @param string[] $data
     * @return string[]
     */
    protected function _processTrackingOrders($data)
    {
        $this->_processStarted = 'tracking';

        foreach ($data as $val) {
            $vinNumber = $val['vin'];
            // @todo Refactoring: extract productId not by getIdBySku
            $productId = $this->_productModel->getIdBySku($vinNumber);

            if ($productId) {
                $collectionData = $this->_orderItem->getCollection()
                    ->addFieldToSelect('order_id')
                    ->addFieldToFilter('product_id', $productId);

                foreach ($collectionData as $orderItem) {
                    $order = Mage::getModel('sales/order')->load($orderItem->getOrderId());
                    $trackingStatus = $val['tracking'] ?? false;

                    if ($order->getId() && $trackingStatus) {
                        switch ($trackingStatus) {
                            case $order::STATUS_VEHICLE_IN_TRANSIT:
                                $order->setState($order::STATE_PROCESSING, $order::STATUS_VEHICLE_IN_TRANSIT)
                                    ->save();
                                $this->_status[$this->_processStarted][$vinNumber] = 'order status processed to Vehicle in Transit';
                                Mage::dispatchEvent('peppermint_order_tracking_in_transit', ['order' => $order]);
                                break;
                            case $order::STATUS_VEHICLE_ARRIVED:
                                $order->setState($order::STATE_PROCESSING, $order::STATUS_VEHICLE_ARRIVED)
                                    ->save();
                                $this->_status[$this->_processStarted][$vinNumber] = 'order status processed to Vehicle Arrived';
                                Mage::dispatchEvent('peppermint_order_tracking_arrived', ['order' => $order]);
                                break;
                        }
                    }
                }
            }
        }
        unset($data); // clean php memory

        return $this->_status;
    }

    /**
     * Process orders with unlocked amendment
     *
     * @param string[] $data
     * @return string[]
     */
    protected function _processUnlockAmendOrders($data)
    {
        if (Mage::helper('peppermint_orderamend')->getCanUnlockAmendEnabled()) {
            $this->_processStarted = 'orderAmendUnlock';
            $unlockAmendOrders = [];

            foreach ($data as $val) {
                if ($vinNumber = $val['vin']) {
                    $unlockAmendOrders[] = $vinNumber;
                    $this->_status[$this->_processStarted][$vinNumber] = 'unlock order amend';
                }
            }

            unset($data); // clean php memory

            Mage::helper('peppermint_sales/order')->setLockOrUnlockOrderAmend(
                $unlockAmendOrders,
                Peppermint_Sales_Model_AdditionalData::CAN_AMEND_UNLOCK
            );

            return $this->_status;
        }
    }

    /**
     * Invoice and Ship to pre retail order
     *
     * @param string[] $data
     * @return string[]
     */
    protected function _processPreRetailedOrders($data)
    {
        $this->_processStarted = 'orderPreRetail';
        $preRetailedOrders = [];

        foreach ($data as $val) {
            if ($vinNumber = $val['vin']) {
                $preRetailedOrders[] = $vinNumber;
                $this->_status[$this->_processStarted][$vinNumber] = 'order pre retailed';
            }
        }

        unset($data); // clean php memory

        Mage::helper('peppermint_importer/order')->invoiceAndShipOrder($preRetailedOrders);

        return $this->_status;
    }
}
