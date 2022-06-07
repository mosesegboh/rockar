<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Lucaci Stefan  <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Order extends Rockar_Customer_Block_Order
{
    /**
     * Pending amendment order status
     */
    const PENDING_AMENDMENT_ORDER_STATUS = 'pending_amendment';

    const HOME_DELIVERY_STATUS = 'Home Delivery';

    /**
     * Get order details.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    public function getOrderDetailsJson($order)
    {
        $helper = Mage::helper('rockar_all');
        $orderData = $helper->jsonDecode(parent::getOrderDetailsJson($order));
        $orderData['delivery_date'] = $this->formatDateForOrderSummary(
            $orderData['delivery_date'], $orderData['delivery']['type'] === self::HOME_DELIVERY_STATUS
        );
        $orderData['document'] = $this->_getDocuments($order);

        return $helper->jsonEncode($orderData);
    }

    /**
     * Get order documents.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return null|array
     */
    private function _getDocuments($order)
    {
        $orderIncrId = $order->getIncrementId();
        $helper = Mage::helper('peppermint_checkout/pdf');
        $file = file_exists($helper->getOtpFilePath($orderIncrId))
            ? $helper->getOtpFilePath($orderIncrId)
            : $helper->getOtpFilePath($orderIncrId, 'sent');
        $doc = null;

        if (file_exists($file)) {
            $doc = [
                'creation' => date('d/m/Y', filectime($file)),
                'document_name' => 'Offer to Purchase',
                'file_name' => $orderIncrId . '.pdf',
                'link' => $this->getUrl('customer/otp/download/id/' . $orderIncrId)
            ];
        }

        return $doc;
    }

    /**
     * Get payment data json.
     *
     * @param $order
     *
     * @return string
     */
    public function getPaymentJson($order)
    {
        $result = [];

        /** @var Mage_Sales_Model_Order_Item $orderItem */
        $orderItem = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order);
        $all = Mage::helper('rockar_all');

        if ($orderItem) {
            $financeData = $all->jsonDecode($orderItem->getFinanceData());
            $financeGroupId = $financeData['group_id'] ?? 0;

            if (isset($financeData[$financeGroupId])) {
                $financeData = $financeData[$financeGroupId];
            }

            $financeVariables = $all->jsonDecode($orderItem->getFinanceDataVariables());
            $payInFullMethod = Mage::helper('financing_options')->getDefaultPayInFullPayment();
            $isHire = isset($financeVariables['is_hire']) && $financeVariables['is_hire'];

            if (!empty($financeData)) {
                if (in_array($financeData['group_id'], array_column($payInFullMethod, 'group_id'))) {
                    $result = [
                        'payInFull' => 1,
                        'rockar_price' => $financeVariables['rockar_price'] ?? 0,
                        'total_amount_payable' => $financeVariables['total_amount_payable'] ?? 0,
                        'customer_deposit' => $financeVariables['customer_deposit'] ?? 0,
                        'balance_to_finance' => $financeVariables['balance_to_finance'] ?? 0
                    ];
                } else {
                    $result = [
                        'deposit' => $isHire
                            ? $financeVariables['cash_deposit'] ?? 0
                            : $financeVariables['total_deposit'] ?? 0,
                        'monthlyTerm' => $financeData['term'] ?? 0,
                        'monthlyRepayment' => $financeVariables['monthly_price'] ?? 0,
                        'mileage' => $financeData['mileage'] ?? 0,
                        'isHire' => $financeVariables['is_hire'] ?? 0
                    ];
                }
            }
        }

        return $all->jsonEncode($result);
    }

    /**
     * Check whether given order is pending amendment
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function isPendingAmendment(Mage_Sales_Model_Order $order)
    {
        return $order->getStatus() === self::PENDING_AMENDMENT_ORDER_STATUS;
    }

    /**
     * Get car or the only order item data
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    public function getCarDetailsJson($order)
    {
        $result = [];

        /** @var $orderItem Mage_Sales_Model_Order_Item */
        if ($orderItem = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order)) {
            $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());

            $carDetails = new Varien_Object([
                'title' => Mage::helper('rockar_checkout/order')->getOrderCarName($order),
                'short_title' => $product->getShortTitle(),
                'short_subtitle' => $product->getShortSubtitle(),
                'bodystyle' => $product->getAttributeText('bodystyle') ?: '',
                'short_description' => $product->getShortDescription(),
                'image' => Mage::helper('rockar_checkout/order')->getOrderImage($order),
                'inImg' => Mage::helper('rockar_checkout/order')->getInteriorImage($order),
                'exImg' => Mage::helper('rockar_checkout/order')->getExteriorImage($order),
                'license_plate' => $orderItem->getLicensePlateNumber(),
                'images' => [
                    'in' => $product->getDefaultInterior(),
                    'ex' => $product->getDefaultExterior(),
                    'bg' => $product->getBagCosyUrl()
                ]
            ]);
            Mage::dispatchEvent('rockar_my_account_prepare_car_details',
                ['order' => $orderItem, 'car_details' => $carDetails]);
            $result = $carDetails;
        }

        return Mage::helper('rockar_all')->jsonEncode($result);
    }

    /**
     * Format date for MY account order summary.
     *
     * @param string  $date           Date to format.
     * @param boolean $isHomeDelivery Whether shipping method was selected as home delivery or not.
     *
     * @return string
     */
    protected function formatDateForOrderSummary($date, $isHomeDelivery = false)
    {
        if (Mage::helper('peppermint_all/newJourney')->isBrandSelected()) {
            $format = $isHomeDelivery ? 'd/m/Y' : 'd/m/Y g:i A';
        } else {
            return $date;
        }

        return $date ? date($format, strtotime(str_replace('/', '-', $date))) : '';
    }
}
