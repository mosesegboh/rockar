<?php
/**
 * @category  Peppermint
 * @package   Peppermint\OrderCap
 * @author    Lika Sikharulia <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_OrderCap_Helper_Data
 */
class Peppermint_OrderCap_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path for checking if localstore user is enabled
     */
    const LOCALSTORES_USER_ENABLED = 'rockar_localstores/rockar_general/user_localstore_status';

    /**
     * Order states array to get active orders
     */
    const ORDER_STATES = [
        Mage_Sales_Model_Order::STATE_CANCELED,
        Mage_Sales_Model_Order::STATE_CLOSED,
        Mage_Sales_Model_Order::STATE_COMPLETE
    ];

    /**
     * Returns array of brands values, ready to be injected as source for multiselect field
     *
     * @return array
     */
    public function getBrandValuesArray()
    {
        $attributes = Mage::getModel('eav/config')->getAttribute('catalog_product', 'manufacturer');
        $options = $attributes->getSource()->getAllOptions();
        $values = [['value' => -1, 'label' => $this->__('No manufacturer')]];

        foreach ($options as $option) {
            if ($option['value'] && $option['label']) {
                $values[] = $option;
            }
        }

        return $values;
    }

    /**
     * Returns array of order cap percentages
     *
     * @return array
     */
    public function getOrderCapPercentages($item)
    {
        $configValue = Mage::getStoreConfig($item);

        return $configValue;
    }

    /**
     * Returns number of active orders per dealer
     *
     * @return integer
     */
    public function getActiveOrders($code)
    {
        $activeOrders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('state', ['nin'  => self::ORDER_STATES])
            ->addFieldToFilter('dealer_code', $code)
            ->addExpressionFieldToSelect(
                'activeOrdersCount',
                'count(distinct ifnull({{original_increment_id}}, {{increment_id}}))',
                [
                    'original_increment_id' => 'original_increment_id',
                    'increment_id' => 'increment_id'
                ]
            )
            ->setCurPage(1)
            ->setPageSize(1);

        return $activeOrders->getFirstItem()
            ->getData('activeOrdersCount');
    }

    /**
     * Get assigned local stores to current admin user
     *
     * @return array|null
     */
    public function getAdminUserLocalStores()
    {
        $adminUserLocalStores = Mage::getSingleton('admin/session')->getUser()->getData('local_stores');

        return $adminUserLocalStores ? explode(',', $adminUserLocalStores) : null;
    }

    /**
     * Get the localstore user current status
     *
     * @return bool
     */
    public function getLocalStoreUserStatus()
    {
        return Mage::getStoreConfigFlag(self::LOCALSTORES_USER_ENABLED);
    }
}
