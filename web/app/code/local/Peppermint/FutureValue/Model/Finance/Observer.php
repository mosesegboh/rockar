<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FutureValue_Model_Finance_Observer extends Rockar_FutureValue_Model_Finance_Observer
{
    /**
     * @var cache variable for px future value.
     */
    protected $_newPxValueCache;

    /**
     * @var cache variable for leadtime value.
     */
    protected $_leadTimeValues = [];

    /**
     * @var cache variable for px deduct value.
     */
    protected $_pxDeductedValues = [];

    /**
     * Based on lead time updates part exchange value.
     * Peppermint re-write to add cache variable and only set new Px value to session.
     * When there is a new value.
     *
     * Event: rockar_financing_options_get_px_value
     *
     * @param Varien_Event_Observer $observer
     */
    public function updatePxValue(Varien_Event_Observer $observer)
    {
        $partExchange = $observer->getEvent()
            ->getData('px');
        $product = $observer->getEvent()
            ->getData('product');

        $productLeadTime = $this->_getLeadTimeValue($product);

        if ($productLeadTime || Mage::registry('is_full_configurator')) {
            $newValue = $this->_getPxDeductedValue($partExchange, $productLeadTime);
        } else {
            $newValue = $partExchange->getData('totals')['total'];
        }

        if ($newValue !== $this->_newPxValueCache) {
            $partExchange->setUpdatedPartExchangeValue($newValue);
            Mage::helper('rockar_partexchange')->savePartExchangeToSession(
                $partExchange,
                [Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY]
            );

            $this->_newPxValueCache = $newValue;
        }
    }

    /**
     * Get Product LeadTime value depending on product type
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return mixed int|null
     */
    protected function _getLeadTimeValue($product)
    {
        $productId = $product->getId();

        if (!isset($this->_leadTimeValues[$productId])) {
            $this->_leadTimeValues[$productId] = ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                ? Mage::helper('peppermint_leadtime')->getCheapestProductLeadTime($product)
                : Mage::helper('rockar_lead_time')->getLeadTime($product);
        }

        return $this->_leadTimeValues[$productId];
    }

    /**
     * Get Deducted PX Value
     * @param  varien_Object $pxObject
     * @param  int|null $productLeadTime
     * @return int
     */
    protected function _getPxDeductedValue(Varien_Object $pxObject, $productLeadTime)
    {
        $pxValue = $pxObject->getData('totals')['total'] ?? 0;
        // Building cache key of "Trade in Value-leadTime Amount (days)"
        // If both of these are the same there is no need to call the deduct function
        $cacheKey = (string) $pxValue . '-' . (string) $productLeadTime;

        if (!isset($this->_pxDeductedValues[$cacheKey])) {
            $this->_pxDeductedValues[$cacheKey] =
                Mage::helper('rockar_futurevalue/deductions_partExchangeFuture_data')->deduct(
                    $pxObject,
                    false,
                    $productLeadTime
            );
        }

        return $this->_pxDeductedValues[$cacheKey];
    }
}
