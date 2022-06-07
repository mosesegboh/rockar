<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Reports_Model_Observer
{
    /**
     * Log records on products import
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function logPricesOnProductsImport(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()
            ->getImportedSimples();

        $helper = Mage::helper('peppermint_reports');
        $helper->logVinPrice($productIds, $helper::ACTION_IMPORT);
    }

    /**
     * Log records on product delete
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function logPricesOnProductDelete(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()
            ->getProduct();

        $productId = (int) $product->getId();

        if (!$productId) {
            return;
        }

        $helper = Mage::helper('peppermint_reports');
        $helper->logVinPrice([$productId], $helper::ACTION_UNPUBLISH);
    }

    /**
     * Log records on order status change
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function logPricesOnOrderStatusChange(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()
            ->getOrder();
        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getData('status');

        if ($oldStatus === $newStatus) {
            return;
        }

        $helper = Mage::helper('peppermint_reports');
        $statuses = $helper->getOrderStatuses();

        if ($statuses && in_array($newStatus, $statuses)) {
            $helper->logVinPrice(
                [Mage::helper('rockar_checkout/order')->getFirstSimpleProduct($order)->getId()],
                $helper::ACTION_ORDER_STATUS_CHANGE,
                $order->getStatusLabel($newStatus)
            );
        }
    }

    /**
     * Log records on price rules apply
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function logPricesOnRulesApply(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('peppermint_reports');
        $helper->logVinPrice(null, $helper::ACTION_PRICE_RULE_APPLIED);
    }
}
