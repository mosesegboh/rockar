<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Ketevani Revazishvili <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_Checkout_Helper_CustomerGroups
 */
class Peppermint_Checkout_Helper_Customer extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CUSTOMER_ORDER_CAP_GROUP = 'rockar_customer/customer_order_cap/order_cap_groups';

    /**
     * Helper method to get order cap settings for active customer group
     *
     * @return array
     */
    public function getCustomerGroupOrderCap()
    {
        $result = null;
        $session = Mage::getSingleton('customer/session');
        $customerGroupId = null;

        if ($session->isLoggedIn()) {
            $customerGroupId = $session->getCustomerGroupId();
        }

        $customerGroupOrderCaps = unserialize(Mage::getStoreConfig(self::XML_PATH_CUSTOMER_ORDER_CAP_GROUP));

        if ($customerGroupOrderCaps) {
            $customerGroupOrderCaps = array_filter($customerGroupOrderCaps, function ($var) use ($customerGroupId) {
                return ($var['customer_group'] === $customerGroupId);
            });

            foreach ($customerGroupOrderCaps as $customerGroupOrderCap) {
                $result = [
                    'customer_group' => $customerGroupOrderCap['customer_group'],
                    'individual_cap' => $customerGroupOrderCap['individual_cap'] ?? 0,
                    'corporate_cap' => $customerGroupOrderCap['corporate_cap'] ?? 0
                ];
            }
        }

        return Mage::helper('rockar_all')->jsonEncode($result);

    }

    /**
     * Helper method to get active orders number for active user
     *
     * @return int
     */
    public function getCustomerActiveOrders()
    {
        $session = Mage::getSingleton('customer/session');
        $customerId = null;

        if ($session->isLoggedIn()) {
            $customerId = $session->getCustomerId();
        }

        $stateArr = [
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_COMPLETE
        ];

        $activeSalesOrderCollection = Mage::getResourceModel('sales/order_collection');
        $activeSalesOrderCollection->addFieldToFilter('state', ['nin'  => $stateArr])
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $activeSalesOrderCollection->addFieldToSelect('customer_id')
            ->getSelect()
            ->columns('COUNT(DISTINCT IFNULL(original_increment_id, increment_id)) AS activeOrdersCount')
            ->group('customer_id');

        $result = $activeSalesOrderCollection->getFirstItem()->toArray();

        if ($result) {
            return $result['activeOrdersCount'];
        }

        return 0;
    }
}
