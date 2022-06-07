<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Ivans Zuks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_Checkout_Helper_Delivery
 */
class Peppermint_Checkout_Helper_Delivery extends Rockar_Checkout_Helper_Delivery
{
    static protected $_leadtimeCache = [];

    /**
     * Returns stores with formatted data format
     *
     * @return mixed
     */
    public function getCheckoutStoresFormatted()
    {
        $storeCollection = Mage::getModel('rockar_localstores/stores')->getCollection();

        // Magento store
        $storeId = Mage::app()->getStore()
            ->getStoreId();
        $productClass = Mage::helper('peppermint_localstores')->getAvailableProductClass($storeId);

        $stateArr = [
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_COMPLETE
        ];

        // get dealer codes with active orders and active orders count
        $activeSalesOrderCollection = Mage::getResourceModel('sales/order_collection');
        $activeSalesOrderCollection->addFieldToSelect('dealer_code')
            ->addFieldToFilter('state', ['nin'  => $stateArr]);
        $activeSalesOrderCollection->getSelect()
            ->columns('COUNT(DISTINCT IFNULL(original_increment_id, increment_id)) AS activeOrdersCount')
            ->group('dealer_code');

        $selectOrders = $activeSalesOrderCollection->getSelect();

        // get all distinct dealer codes from orders table (with active orders or without them)
        $allDealerFromOrdersCollection = Mage::getResourceModel('sales/order_collection');
        $allDealerFromOrdersCollection->addFieldToSelect('dealer_code')
            ->getSelect()
            ->group('dealer_code')
            ->joinLeft(['orders_table'=> $selectOrders], 'main_table.dealer_code = orders_table.dealer_code', [new Zend_Db_Expr('MAX(orders_table.activeOrdersCount) AS activeOrdersCount')]);
        $allDealerFromOrders = $allDealerFromOrdersCollection->getSelect();

        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

        $userOrders = Mage::getResourceModel('sales/order_collection');
        $userOrders->addFieldToSelect('dealer_code')
            ->addFieldToSelect('entity_id', 'id')
            ->addFieldToFilter('customer_id', $customerId);

        $userTestDrives = Mage::getResourceModel('rockar_youdrive/booking_collection');
        $userTestDrives->addFieldToSelect('local_store_code', 'dealer_code')
            ->addFieldToSelect('id', 'id')
            ->addFieldToFilter('customer_id', $customerId);

        $readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $unionOrders = $readAdapter->select()
            ->union([$userOrders->getSelect(), $userTestDrives->getSelect()], Zend_Db_Select::SQL_UNION_ALL)
            ->group('dealer_code');

        $previousDealers = $readAdapter->select()
            ->from($unionOrders, ['dealer_code', new Zend_Db_Expr('COUNT(id) AS previousOrders')])
            ->group('dealer_code');

        $storeCollection->getSelect()
            ->joinLeft(
                ['order_dealers'=> $allDealerFromOrders],
                'main_table.code = order_dealers.dealer_code',
                []
            );

        $storeCollection->getSelect()
            ->joinLeft(
                ['previous_dealers'=> $previousDealers],
                'main_table.code = previous_dealers.dealer_code',
                ['previousOrders']
            );

        $storeCollection->join(
            ['address' => 'rockar_localstores/address'],
            'address.store_id = main_table.entity_id',
            [
                'latitude',
                'longitude',
                'city',
                'state',
                'postal_code',
                'region',
                'main_phone',
                'address_line_1',
                'address_line_2',
                'address_line_3'
            ]
        )->addFieldToFilter(
            'main_table.store_view',
            [
                ['eq' => $storeId],
                ['eq' => Peppermint_Localstores_Helper_Data::ALL_STORE_VIEWS_ID]
            ]
        )->addFieldToFilter('main_table.enable_checkout', ['eq' => 1])
        ->addFieldToFilter('main_table.status', ['eq' => 1])
        ->addFieldToFilter('associated_brand', ['like' => '%' . $productClass . '%'])
        ->getSelect()
            ->where('main_table.order_cap > order_dealers.activeOrdersCount OR (order_dealers.activeOrdersCount IS NULL AND main_table.order_cap <> 0)');

        $storeCollection->getSelect()
            ->group('main_table.entity_id');

        $helper = Mage::helper('rockar_localstores');
        $helper->setLocalStoreCollectionAvailability($storeCollection);

        return $helper->formatStoreData($storeCollection);
    }

    /**
     * {@inheritDoc}
     */
    public function getLeadTime()
    {
        $leadTime = 0;
        $product = Mage::helper('rockar_checkout/quote')->getFirstSimpleProduct(
            Mage::getSingleton('checkout/cart')->getQuote()
        );

        $productId = $product ? $product->getId() : null;

        if ($productId) {
            if (!array_key_exists($productId, static::$_leadtimeCache)) {
                $leadTime = Mage::helper('rockar_lead_time')->getLeadTime($product);
                static::$_leadtimeCache[$productId] = $leadTime;
            }

            $leadTime = static::$_leadtimeCache[$productId];
        }

        return (int) $leadTime;
    }
}
