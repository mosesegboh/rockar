<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>, Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Helper_Order extends Mage_Core_Helper_Abstract
{
    // Timestamp date format specific to DSP YYYYMMDDHHMMSSsss - ZendDateString yyyyMMddHHmmss
    protected const DSP_TIMESTAMP_DATE_FORMAT = 'yyyyMMddHHmmss';

    /**
     * @var string ACL_PATH_OTP_DOWNLOAD
     * Path for the ACL permission for whether admin user is allowed
     * to download sales documents
     */
    const ACL_PATH_OTP_DOWNLOAD = 'admin/sales/order/download_otp';

    /**
     * @var string event name
     */
    protected $_event;

    /**
     * @var string order cancel event name
     */
    protected $_orderCancel = 'order_cancel_after';

    protected $_usefulFinanceVariables = [
        'balloon_amount',
        'shortfall_applied',
        'outstanding_finance',
        'part_exchange',
        'amount_of_credit',
        'px_settlement_payment',
        'balance_to_finance',
        'coupon_discount'
    ];

    /**
     * @var Peppermint_Importer_Helper_Mq_PushOrders
     */
    protected $_messageHelper;

    /**
     * @var Mage_Core_Helper_Abstract|null
     */
    protected $allHelper;

    /**
     * Peppermint_Sales_Helper_Order constructor.
     */
    public function __construct()
    {
        $this->allHelper = Mage::helper('rockar_all');
    }

    /**
     * Generic method that prepares the data and pushes it to DS.
     *
     * @param Peppermint_Sales_Model_Order $order
     * @param string                       $event
     * @return Peppermint_Sales_Helper_Order
     */
    public function prepareAndPushOrderToDs(Peppermint_Sales_Model_Order $order, $event = null)
    {
        try {
            if ($event) {
                $this->_setEvent($event);
            }

            if (!$order->getId()) {
                Mage::throwException($this->__('Order was not yet saved properly.'));
            }

            $sapHelper = Mage::helper('peppermint_sales/sap');
            $sapHelper->syncWithOrderSapLog($order->getId());

            $data = $this->_prepareFullData($order);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_handleException('Message assembling exception encountered: ', $order, $e);
        }

        if (!empty($data)) {
            try {
                $this->_getMessageSendingHelper()->writeMessage($data);

                $order->addStatusHistoryComment('Order submitted to SAP')
                    ->setIsVisibleOnFront(false)
                    ->setIsCustomerNotified(false)
                    ->save();

                $sapHelper->syncWithOrderSapLog($order->getId(), true);
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_handleException('Message push exception encountered!', $order);
            }
        }

        return $this;
    }

    /**
     * Prepares the whole dataset that DS expects.
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return []
     * @throws Mage_Core_Exception
     */
    protected function _prepareFullData(Peppermint_Sales_Model_Order $order)
    {
        $checkoutHelper = Mage::helper('rockar_checkout/order');

        /** @var Mage_Sales_Model_Order_Item $orderItem */
        $orderItem = $checkoutHelper->getFirstSimpleOrderItem($order);

        if (!$orderItem) {
            Mage::throwException('Order item associated to this order has not been found. Please check order simple product');
        }

        /** @var Peppermint_Catalog_Model_Product $orderProduct */
        $orderProduct = $orderItem->getProduct();

        if (!$orderProduct->getId()) {
            Mage::throwException('Product associated to this order has not been found in catalog.');
        }

        $pricingSnapshot = $this->_getOrderPricingSnapShot($checkoutHelper->getFirstVisibleItem($order));

        /**
         * @var  Mage_Sales_Model_Order_Item $orderItemData
         */
        $orderItemData = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order)
            ->getData();

        return array_merge_recursive(
            $this->_prepareBaseData($order),
            $this->_prepareProductData($orderProduct, $pricingSnapshot),
            $this->_prepareTradeInData($order->getId()),
            $this->_prepareFinanceData($orderProduct, $orderItemData, $pricingSnapshot),
            $this->_prepareOptionsData($orderProduct, $pricingSnapshot),
            $this->_prepareAccessoriesData($orderItemData)
        );
    }

    /**
     * Prepares all finance related variables.
     *
     * @param Peppermint_Catalog_Model_Product $product
     * @param Mage_Sales_Model_Order_Item      $orderItemData
     * @param array                            $pricingSnapshot
     * @return []
     */
    protected function _prepareFinanceData(Peppermint_Catalog_Model_Product $product, $orderItemData, $pricingSnapshot)
    {
        $financeOverlayFull = $this->allHelper->jsonDecode($orderItemData['finance_overlay']);
        $financeOverlayOptions = $financeOverlayFull['options'] ?? [];
        $financeOverlay = reset($financeOverlayOptions);

        foreach ($financeOverlay['calc'] ?? [] as $variableName => $valuesArray) {
            $financeOverlayCalculated[$variableName] = $valuesArray['value'];
        }

        $financeDataVariables = $this->allHelper->jsonDecode($orderItemData['finance_data_variables']);

        foreach ($financeDataVariables['finance_variables'] as $financeVariable) {
            if (in_array($financeVariable['variable'], $this->_usefulFinanceVariables)) {
                $financeVariablesValues[$financeVariable['variable']] = $financeVariable['value'];
            }
        }

        $accessories = $this->allHelper->jsonDecode($orderItemData['accessories']);
        $accessoriesPrice = 0;

        if ($accessories) {
            foreach ($accessories as $accessory) {
                $accessoriesPrice += $accessory['price'];
            }
        }

        $equityAmount = ($financeVariablesValues['part_exchange'] ?? 0) - ($financeVariablesValues['outstanding_finance'] ?? 0);

        return [
            'balloonAmount' => $financeVariablesValues['balloon_amount'] ?? 0,
            'financeAmount' => $this->_getFinanceAmount($financeDataVariables, $financeVariablesValues, $equityAmount),
            'depositBalance' => $financeOverlayCalculated['customer_deposit'] ?? 0,
            'cashPrice' => $financeOverlayCalculated['product_price'] ?? 0,
            'customerAmount' => $financeOverlayCalculated['cashback'] ?? 0,
            'depositAmount' => $financeOverlayCalculated['total_deposit'] ?? 0,
            'equityAmount' => $equityAmount,
            'financeHouse' => '', // @todo mapping not provided yet
            'handlingFee' => '', // @todo mapping not provided yet
            'invoiceAmount' => $financeOverlayCalculated['total_amount_payable'] ?? 0,
            'manufacturerSupportAmount' => (float) ($pricingSnapshot['price'] ?? $product->getPrice())
                + Mage::helper('peppermint_financingoptions/otp')->getAccessoriesTotal($financeDataVariables)
                - ($orderItemData['price'] ?? 0)
                + ($orderItemData['discount_amount'] ?? 0),
            'negativeEquityAmount' => $equityAmount < 0 ? abs($equityAmount) : 0,
            'newListPrice' => (float) ($pricingSnapshot['price'] ?? $product->getPrice()),
            'secondaryDepositAmount' => $this->_calculateSecondDepositAmount($financeOverlayCalculated, $financeVariablesValues),
            'shortfallAllowance' => $financeVariablesValues['shortfall_applied'] ?? 0,
            'vehicleOfferPrice' => $financeOverlayCalculated['product_price'] ?? 0,
            'mplanAmount' => (float) ($pricingSnapshot['mplan_amount'] ?? $product->getMplanPrice()) ?: 0,
            'totalAdditionalExtras' => (float) ($pricingSnapshot['options_only_price'] ?? $product->getOptionsOnlyPrice()),
            'totalAccessories' => $accessoriesPrice
        ];
    }

    /**
     * Calculate Secondary Deposite Amount for DS.
     *
     * @param array $financeOverlayCalculated
     * @param array $financeVariablesValues
     * @return float
     */
    protected function _calculateSecondDepositAmount($financeOverlayCalculated, $financeVariablesValues)
    {
        return ($financeOverlayCalculated['cash_deposit'] ?? 0) + ($financeVariablesValues['px_settlement_payment'] ?? 0);
    }

    /**
     * Prepares product data.
     *
     * @param Peppermint_Catalog_Model_Product $orderProduct
     * @param array                            $pricingSnapshot
     * @return []
     */
    protected function _prepareProductData(Peppermint_Catalog_Model_Product $orderProduct, $pricingSnapshot)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $adminEnvironmentEmulation = $appEmulation->startEnvironmentEmulation(Mage_Core_Model_App::ADMIN_STORE_ID);
        $resourceModel = Mage::getResourceModel('catalog/product');
        $optionId = $resourceModel->getAttributeRawValue(
            $orderProduct->getId(),
            'vehicle_condition',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );
        $newUsed = $resourceModel->getAttribute('vehicle_condition')
            ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->getSource()
            ->getOptionText($optionId);

        $data = [
            'co2Tax' => $pricingSnapshot['co2_tax'] ?? $orderProduct->getCo2Tax(),
            'km' => $orderProduct->getKm(),
            'newUsed' => $newUsed,
            'vin' => $orderProduct->getData('vin_number'),
            'dateOfFirstRegistration' => $orderProduct->getRegistrationDate() ?: '',
            'registrationNumber' => $orderProduct->getRegistrationNumber() ?: '',
            'stockOwner' => '' // mapping not available yet
        ];

        $appEmulation->stopEnvironmentEmulation($adminEnvironmentEmulation);

        return $data;
    }

    /**
     * Prepares base data which applies to all status updates.
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    protected function _prepareBaseData(Peppermint_Sales_Model_Order $order)
    {
        /** @var Rockar_Localstores_Model_Stores $dealer */
        $dealer = Mage::getModel('rockar_localstores/stores')->load($order->getDealerCode(), 'code');

        if (!$dealer) {
            Mage::throwException('Dealer associated to this order has not been found in local stores.');
        }

        $dspOrderVersionTokens = explode('-', $order->getIncrementId());

        return [
            'billingDealerCode' => $dealer ? $dealer->getDealerCode() : '',
            'dspOrderNumber' => $order->getIncrementId(),
            'dspOrderTimestamp' => $this->_formatDate($order->getCreatedAt()),
            'dspOrderUpdateTimestamp' => $this->_formatDate($this->_getUpdatedAt($order->getUpdatedAt())),
            'dspOrderVersion' => $dspOrderVersionTokens[2] ?? '',
            'gcdmId' => Mage::getModel('peppermint_gcdm/customer_access')->load($order->getCustomerId())
                ->getGcid(),
            'orderStatusCode' => $order->getStatus(),
            'orderStatusDescription' => Mage::getModel('sales/order_status')->load($order->getStatus())
                ->getStoreLabel(),
            'customerCompanyRegistrationNumber' => $this->_getRegistrationNumberByOrderId($order->getId())
        ];
    }

    /**
     * Method return Compny Registration Number from Order Additional table.
     *
     * @param integer $orderId
     * @return mixed
     */
    protected function _getRegistrationNumberByOrderId(int $orderId)
    {
        return Mage::getModel('rockar_checkout/order_additional')->load($orderId, 'order_id')
            ->getRegistrationNumber() ?? '';
    }

    /**
     * Prepares Accessories data.
     *
     * @param Mage_Sales_Model_Order_Item $orderItemData
     * @return array
     */
    protected function _prepareAccessoriesData($orderItemData)
    {
        $accessories = [];
        $financeDataVariables = Mage::helper('rockar_all')->jsonDecode($orderItemData['finance_data_variables']);

        $accessoryData = [];
        foreach ($financeDataVariables['car_data'] as $data) {
            if (isset($data['group'], $data['items']) && $data['group'] === 'Accessories') {
                $accessoryData = $data;
                break;
            }
        }

        foreach ($accessoryData['items'] as $item) {
            $accessories[] = [
                'code' => $item['material_number'] ?? $item['id'] ?? '',
                'indicator' => $item['option_code'] ?? '',
                'description' => $item['label'] ?? '',
                'price' => $item['price'] ?? 0
            ];
        }

        return [
            'options' => $accessories
        ];
    }

    /**
     * Prepares Options data.
     *
     * @param Peppermint_Catalog_Model_Product $orderProduct
     * @param array                            $pricingSnapshot
     * @return []
     * @throws Mage_Core_Exception
     */
    protected function _prepareOptionsData($orderProduct, $pricingSnapshot)
    {
        return ['options' => $pricingSnapshot['options'] ?? $this->prepareOptionsData($orderProduct)];
    }

    /**
     * Prepares Options data.
     *
     * @param Peppermint_Catalog_Model_Product $orderProduct
     * @return []
     * @throws Mage_Core_Exception
     */
    public function prepareOptionsData(Peppermint_Catalog_Model_Product $orderProduct)
    {
        $options = [
            [
                'code' => $orderProduct->getAttributeText('model_code'),
                'description' => $orderProduct->getName(),
                'indicator' => 'B',
                'price' => $orderProduct->getTotalPriceExclMplan()
            ]
        ];
        $optionsPrices = [];

        try {
            $optionsPrices = $this->allHelper->jsonDecode($orderProduct->getCustomOptionsPrices());
        } catch (Exception $e) {
            Mage::throwException('Product options prices are not properly encoded: ' . $e->getMessage());
        }

        foreach ($optionsPrices as $option) {
            $indicator = str_replace(
                [
                    'L', 'P'
                ],
                [
                    'E', 'I'
                ],
                $option['optionIndicator']
            );
            $options[] = [
                'code' => $option['optionCode'],
                'description' => $option['optionDescription'],
                'indicator' => $indicator,
                'packCode' => $option['packCode'],
                'packInd' => $option['packInd'],
                'price' => $option['optionPrice']
            ];
        }

        return $options;
    }

    /**
     * Prepares tradeIn data.
     *
     * @param integer $orderId
     * @return []
     */
    protected function _prepareTradeInData($orderId)
    {
        $partExchangeData = Mage::getModel('rockar_partexchange/order')->load($orderId, 'order_id')
            ->getData();

        if (empty($partExchangeData)) {
            return [];
        }
        $conditionSlider = Mage::getModel('rockar_partexchange/conditions_slider')->load($partExchangeData['car_condition']);

        return [
            'tradeIns' => [
                [
                    'condition' => $conditionSlider ? $conditionSlider->getTitle() : '',
                    'description' => $partExchangeData['car_model'] ?? '',
                    'finalOffer' => $partExchangeData['part_exchange_value'] ?? '',
                    'firstDateOfRegistration' => $partExchangeData['first_date_of_registration'] ?? '',
                    'make' => $partExchangeData['make_name'] ?? '',
                    'makeCode' => $partExchangeData['make_code'] ?? '',
                    'milage' => $partExchangeData['car_mileage'] ?? '',
                    'mmCode' => $partExchangeData['cap_id'] ?? '',
                    'offset' => (float) $partExchangeData['part_exchange_value'] - (float) $partExchangeData['outstanding_finance'],
                    'regNo' => $partExchangeData['license_plate'] ?? '',
                    'settlementAmount' => $partExchangeData['outstanding_finance'] ?? '',
                    'valuation' => $partExchangeData['original_valuation'] ?? '',
                    'vin' => $partExchangeData['vin'] ?? '',
                    'yearModel' => $partExchangeData['car_year'] ?? ''
                ]
            ]
        ];
    }

    /**
     * Transforms date string into desired format.
     *
     * @param string $dateString
     * @return string
     */
    protected function _formatDate($dateString)
    {
        return Mage::app()->getLocale()
            ->storeDate(
                Mage::app()->getStore(),
                Varien_Date::toTimestamp($dateString),
                true
            )->toString($this::DSP_TIMESTAMP_DATE_FORMAT);
    }

    /**
     * Handles the exceptions without stopping the actions.
     *
     * @param string                       $message
     * @param Peppermint_Sales_Model_Order $order
     * @param null|Exception               $e
     * @return $this
     */
    protected function _handleException($message, Peppermint_Sales_Model_Order $order, Exception $e = null)
    {
        $order->addStatusHistoryComment('Order workflow update has failed with message: ' . $message . ($e ? $e->getMessage() : ''))
            ->setIsVisibleOnFront(false)
            ->setIsCustomerNotified(false)
            ->save();

        return $this;
    }

    /**
     * Inits the communication helper.
     *
     * @return Peppermint_Importer_Helper_Mq_PushOrders
     */
    protected function _getMessageSendingHelper()
    {
        if (!$this->_messageHelper) {
            $this->_messageHelper = Mage::helper(
                Mage::registry('peppermint_importer/mq_orders_batch') ?
                    'peppermint_importer/mq_pushOrdersBatch' :
                    'peppermint_importer/mq_pushOrders'
            );
        }

        return $this->_messageHelper;
    }

    /**
     * Event name setter
     *
     * @param string $eventName
     * @return void
     */
    protected function _setEvent($eventName)
    {
        $this->_event = $eventName;
    }

    /**
     * Event name getter
     *
     * @return string|null
     */
    protected function _getEvent()
    {
        return $this->_event;
    }

    /**
     * Get updated at dateTime string
     * Due to order cancel event running before order save the updatedAt timestamp is incorrect for this event
     * Hence the needs to get the current time here for this event instead of timestamp from the order object
     *
     * @param string $orderUpdatedAt
     * @return string|null
     */
    protected function _getUpdatedAt($orderUpdatedAt)
    {
        return $this->_getEvent() === $this->_orderCancel ? Varien_Date::now() : $orderUpdatedAt;
    }

    /**
     * Get Finance Amount
     *
     * @param array $financeData
     * @param array $variableData
     * @param float $pxValue
     * @return int|float
     */
    protected function _getFinanceAmount($financeData, $variableData, $pxValue)
    {
        $helper = Mage::helper('peppermint_financingoptions/otp');

        return $helper->isPayInFull($financeData)
            ? $helper->getPayInFullFinanceAmount($financeData, $pxValue)
            : $variableData['amount_of_credit'] ?? 0;
    }

    /**
     * Update order Item product id and product id store in product option
     * Due to vehicle being deleted and re-imported
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return void
     * @throws Mage_Core_Exception
     */
    public function refreshOrderProductData(Peppermint_Sales_Model_Order $order)
    {
        $helper = Mage::helper('rockar_checkout/order');
        $parentOrderItem = $helper->getFirstVisibleItem($order);
        $simpleOrderItem = $helper->getFirstSimpleOrderItem($order);

        // Update parent item info_buyRequest
        $parentProductId = $parentOrderItem ? $parentOrderItem->getProduct()->getId() : null;
        $simpleProductId = $simpleOrderItem ? $simpleOrderItem->getProduct()->getId() : null;
        // re-imported product sometime have different group key coming from digital showroom API hence the need
        // to check for new parent here as different group key will create a new configurable product base on that key
        $parentId = Mage::getResourceSingleton('catalog/product_type_configurable')
                ->getParentIdsByChild($simpleProductId)[0] ?? null;
        $parentProductId = ($parentProductId !== $parentId) ? $parentId : $parentProductId;

        if (!$parentProductId || !$simpleProductId) {
            Mage::throwException($this->__('Product associated to this order has not been found in catalog.'));
        }

        $parentProductOptions = $parentOrderItem ? $parentOrderItem->getProductOptions() : null;
        $simpleProductOptions = $simpleOrderItem ? $simpleOrderItem->getProductOptions() : null;

        if (!isset($parentProductOptions['info_buyRequest'], $simpleProductOptions['info_buyRequest'])) {
            Mage::throwException($this->__('Product options missing from the order item.'));
        }

        $buyRequests = [
            'parent' => $parentProductOptions,
            'simple' => $simpleProductOptions
        ];
        // Update orderItem product options specifically the info_buyRequest store inside product_options column
        foreach ($buyRequests as $key => $productOption) {
            $productOption['info_buyRequest'] = array_replace(
                $productOption['info_buyRequest'],
                [
                    'product' => $parentProductId,
                    'vehicleId' => $simpleProductId
                ]
            );

            if ($key === 'parent') {
                $parentOrderItem->setProductOptions($productOption)
                    ->setProductId($parentProductId)
                    ->save();
            }

            $simpleOrderItem->setProductOptions($productOption)
                ->setProductId($simpleProductId)
                ->save();
        }
    }

    /**
     * Get Acl Path for OTP Download permissions
     *
     * @return string
     */
    public function getAclPathOtpDownload(): string
    {
        return self::ACL_PATH_OTP_DOWNLOAD;
    }

    /**
     * Check if admin user has ACL permissions to download OTP documents
     *
     * @return bool
     */
    public function isAdminUserAllowedToDownloadOtp(): bool
    {
        return (bool) Mage::getSingleton('admin/session')->isAllowed(
            $this->getAclPathOtpDownload()
        );
    }

    /**
     * Lock or Unlock amendment for arrays of vins
     *
     * @param array   $vinsArray
     * @param integer $lockOrUnlock
     * @return void
     */
    public function setLockOrUnlockOrderAmend(array $vinsArray, int $lockOrUnlock)
    {
        $salesAdditional = Mage::getModel('peppermint_sales/additionalData');

        foreach ($vinsArray as $vin) {
            try {
                $salesAdditional->setData([
                    'vin' => $vin,
                    'can_amend' => $lockOrUnlock
                ])->save();

                $salesAdditional->unsetData();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Extract vin number from an order
     *
     * @param integer|Mage_Sales_Model_Order $order
     * @return string|null $vinNum
     */
    public function getOrderVinNumber($order)
    {
        if (!($order instanceof Mage_Sales_Model_Order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        $vinNum = $order->getVinNumber();

        if (
            !$vinNum
            && $orderItem = Mage::helper('rockar_checkout/order')->getFirstSimpleOrderItem($order)
        ) {
            // For the older orders we may need to extract VIN | This does not affect new orders
            $vinNum = (explode('-', $orderItem->getSku()))[0];
        }

        return $vinNum;
    }

    /**
     * Get order pricing snap shot array
     *
     * @param Mage_Sales_Model_Order_Item|false $orderItem
     * @return array
     */
    protected function _getOrderPricingSnapShot($orderItem)
    {
        $pricingSnapshot = $orderItem
            ? ($orderItem->getPricingDetailsSnapshot() ?: [])
            : [];

        // If it is not empty an array decode it
        return $pricingSnapshot
            ? $this->allHelper->jsonDecode($pricingSnapshot)
            : $pricingSnapshot;
    }
}
