<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Checkout_Helper_Confirmation extends Rockar_Checkout_Helper_Confirmation
{
    /**
     * @var null|Varien_Object
     */
    protected $_orderData;

    /**
     * Getting necessary order data for checkout success page by order increment ID.
     *
     * @param boolean|integer $orderId Order increment ID whose data is requested
     *
     * @return string
     */
    public function getOrderData($orderId = false)
    {
        if (!$orderId) {
            $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        }

        /** @var Rockar_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        if ($order && !$this->_orderData) {
            $this->_orderData = new Varien_Object();
            // As for now there is only one order item
            /** @var Mage_Sales_Model_Order_Item $orderItem */
            $orderItem = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order);
            $product = Mage::helper('rockar_checkout/order')->getFirstSimpleProduct($order);

            if ($orderItem) {
                $deliveryDetails = $this->prepareData($order->getDeliveryDetails(), ['store', 'date']);
                $deliveryType = Mage::helper('rockar_checkout/delivery')->getActiveDelivery($order);
                $deposit = self::getDepositForOrderSuccessPage($order);
                $deliveryCharge = $order->getDeliveryCharge($deliveryType);
                $isHomeDelivery = boolval($order->isHomeDelivery($deliveryType));
                $orderDate = date('j/m/Y', strtotime($order->getData('created_at')));

                $surchargeData = ['total' => 0];

                if ($card = Mage::getSingleton('checkout/session')->getChosenCreditCardType()) {
                    $surcharge = Mage::helper('rockar_surcharges');
                    $surchargeData = $surcharge->getSurchargeByCardName($card, $deposit);
                }

                $exteriorImage = $order->getExteriorImage();

                if ($exteriorImage) {
                    $exteriorImage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $exteriorImage;
                } else {
                    $exteriorImage = $this->getExteriorImage($product);
                }

                $interiorImage = $order->getInteriorImage();

                if ($interiorImage) {
                    $interiorImage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $interiorImage;
                } else {
                    $interiorImage = $this->getInteriorImage($product);
                }

                // Necessary to provide categories for google analytics
                $loadedProduct = Mage::getModel('catalog/product')->load($orderItem->getProductId());
                $this->_orderData->setData([
                    'orderNumber' => $orderId,
                    'orderDate' => $orderDate,
                    'customerEmail' => $order->getData('customer_email'),
                    'deliveryDate' => self::formatDateForSuccess($deliveryDetails['date'], $isHomeDelivery),
                    'deliveryStore' => $deliveryDetails['store'],
                    'isHomeDelivery' => $isHomeDelivery,
                    'depositTakes' => $deposit,
                    'fullDeposit' => $deposit + $deliveryCharge + $surchargeData['total'],
                    'surcharge' => $surchargeData['total'],
                    'deliveryCharge' => $deliveryCharge,
                    'carType' => self::getBoughtCarType(),
                    'couponCode' => $order->getData('coupon_code'),
                    'staticBlock' => Mage::helper('rockar_all')->checkIsInStore() ? 'checkout_success_in_store' : 'checkout_success_not_in_store',
                    'images' => [
                        'in' => $interiorImage,
                        'ex' => $exteriorImage
                    ],
                    'product' => [
                        'title' => $loadedProduct->getShortTitle(),
                        'id' => $orderItem->getProductId(),
                        'sku' => $orderItem->getSku(),
                        'revenue' => $orderItem->getPriceInclTax(),
                        'price' => $orderItem->getPrice(),
                        'tax' => $orderItem->getTaxAmount(),
                        'quantity' => (int) $orderItem->getQtyOrdered(),
                        'variant' => $loadedProduct->getShortSubtitle(),
                        'category' => $order->getIsFullConfigurator() ? Mage::helper('rockar2_catalog/configurator')->getCategoryName('you-build') : Mage::helper('rockar_catalog')->getProductCategory($loadedProduct)
                    ],
                    'affiliation' => (int) Mage::helper('rockar_all')->checkIsInStore() ? $this->__('In Store') : $this->__('Online Store')
                ]);
            }
            Mage::dispatchEvent(
                'rockar_order_confirmation_prepare_order_data',
                [
                    'order' => $order,
                    'order_data' => $this->_orderData
                ]
            );
        }

        return $this->_orderData;
    }

    /**
     * Format date for checkout order success page.
     *
     * @param string  $date           Date to format.
     * @param boolean $isHomeDelivery Whether shipping method was selected as home delivery or not.
     *
     * @return string
     */
    public function formatDateForSuccess($date, $isHomeDelivery = false)
    {
        if (Mage::helper('peppermint_all/newJourney')->isBrandSelected()) {
            $format = $isHomeDelivery ? 'j/m/Y' : 'j/m/Y g:i A';
        } else {
            $format = $isHomeDelivery ? 'jS F Y' : 'jS F Y g:i A';
        }

        return $date ? date($format, strtotime($date)) : '';
    }
}
