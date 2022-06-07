<?php
/**
 * @category    Peppermint
 * @package     Peppermint\Orderamend
 * @author      Edgars Joja <techteam@rockar.com>
 * @copyright   Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_Orderamend_Helper_Data
 */
class Peppermint_Orderamend_Helper_Data extends Rockar_Orderamend_Helper_Data
{
    public const XML_PATH_ORDER_AMEND_GENERAL_CAN_UNLOCK_AMENDMENT = 'order_amend/general/can_unlock_amendment';

    /**
     * Get first simple order item
     *
     * @return bool|Mage_Sales_Model_Order_Item
     */
    public function getFirstSimpleOrderItem()
    {
        return Mage::helper('rockar_checkout/order')->getFirstSimpleOrderItem($this->getOrder());
    }

    /**
     * Get first quote item
     *
     * @return mixed
     */
    public function getFirstQuoteItem()
    {
        return Mage::helper('rockar_checkout/quote')->getFirstQuoteItem($this->getQuote());
    }

    /**
     * Add store attribute values
     */
    const GRID_HELPER_PROPERTY_STORE_MAPPING = [
        'bmw_store_view' => [
            '_engineAttribute' => 'bmw_engine'
        ],
        'mini_store_view' => [
            '_engineAttribute' => 'min_engine'
        ],
        'motorrad_store_view' => [
            '_engineAttribute' => 'mot_engine'
        ]
    ];

    /**
     * Prepare car details data for quote carDetails
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return array
     */
    protected function _prepareCarDetailsData(Mage_Catalog_Model_Product $product, Mage_Sales_Model_Quote_Item $quoteItem): array
    {

        return [
            'product_id'         => $product->getId(),
            'title'              => Mage::helper('rockar_catalog/vehicle')->getProductFullTitle($product),
            'sku'                => $product->getSku(),
            'interior_image'     => Mage::helper('rockar_catalog/images')->getFirstImage(
                $product,
                Rockar_Catalog_Helper_Images::IMAGE_TYPE_INTERIOR
            ),
            'exterior_image'     => Mage::helper('rockar_catalog/images')->getFirstImage(
                $product,
                Rockar_Catalog_Helper_Images::IMAGE_TYPE_EXTERIOR
            ),
            'license_plate'      => $quoteItem->getLicensePlateNumber(),
            'vin_number'         => $product->getData('vin_number'),
            'exterior'           => $this->_getCarData('Exterior'),
            'interior'           => $this->_getCarData('Interior'),
            'options'            => $this->_getCarData('Options'),
            'accessories'        => $this->_getCarData('Accessories'),
            'configured_options' => $this->_getCarData('Configured options'),
        ];
    }

    /**
     * @return mixed
     */
    protected function _getQuoteData()
    {
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        /** @var Rockar_Orderamend_Helper_CatalogRule $catalogRuleHelper */
        $catalogRuleHelper = Mage::helper('rockar_orderamend/catalogRule');

        /** @var Mage_Sales_Model_Order $order */
        $order = $this->getOrder();
        $quote = $this->getQuote();

        // to get correct totals on first page load (was issue using configurable + simple quote items)
        $quote->setTotalsCollectedFlag(false);

        // Updating quote shipping address data after shipping method change on order amendment page
        if ($quote->getShippingMethod()) {
            $quote->getShippingAddress()->setShippingMethod($quote->getShippingMethod());
        }

        if ($quote->getShippingDescription()) {
            $quote->getShippingAddress()->setShippingDescription($quote->getShippingDescription());
        }

        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals()->save();

        $this->_calculateQuoteTotals($quote, (float) $order->getCcCharges());
        $quoteItem = Mage::helper('rockar_checkout/quote')->getFirstQuoteItem($this->getQuote());

        // Add data that is stored only in order and won't change during amend
        $quoteData = array_merge(
            $quote->getData(), [
                'order_date' => $coreHelper->formatDate(
                    $order->getCreatedAtDate(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true
                ),
                'order_status' => $order->getStatus(),
                'total_paid' => (float) $order->getTotalPaid(),
                'total_refunded' => (float) $order->getTotalRefunded(),
                'total_due' => (float) $order->getTotalDue(),
                'cc_charges' => (float) $order->getCcCharges(),
                'discount' => (float) $quoteItem->getDiscountAmount(),
                'discount_description' => $quote->getCouponCode(),
                'catalog_rule_have_changed' => $catalogRuleHelper->hasAppliedRuleIdsChanged($quote),
            ]
        );

        // if quotes product has either saved catalog rule or active catalog rule
        if ($priceDiff = $catalogRuleHelper->getPriceDifference($quote)) {
            $quoteData['catalog_rule_difference'] = $priceDiff;
            $quoteData['catalog_rule_data'] = [
                'rule_price' => $catalogRuleHelper->getRulePrice($quote),
                'product_price' => $catalogRuleHelper->getCurrentProductPrice($quote),
            ];
        }

        $this->_formatDataPrices($quoteData);

        return $quoteData;
    }

    /**
     * Can an amendment be unlocked
     *
     * @return int
     */
    public function getCanUnlockAmendEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ORDER_AMEND_GENERAL_CAN_UNLOCK_AMENDMENT);
    }
}
