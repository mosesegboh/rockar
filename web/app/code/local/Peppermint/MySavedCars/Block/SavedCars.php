<?php
/**
 * @category  Peppermint
 * @package   Peppermint_MySavedCars
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_MySavedCars_Block_SavedCars extends Rockar_Configurator_Block_SavedCars
{
    /**
     * Registry keys to check and clear|from core function
     *
     * @var array
     */
    protected $_registryKeys = [
        'is_wishlist_full_configurator',
        'is_full_configurator',
        'current_wishlist_item'
    ];

    /**
     * Get Saved WishList Item Price
     *
     * @param  Mage_Wishlist_Model_Item $wishlistItem
     * @return int
     */
    public function getSavedWishlistItemPrice($wishlistItem)
    {
        return (int) $wishlistItem->getProductPrice();
    }

    /**
     * Rewrite to add balloon percentage for finance calc
     *
     * {@inheritDoc}
     */
    public function getFinanceQuoteData($wishlistItem)
    {
        if ($wishlistItem->getIsFullConfigurator()) {
            return parent::getFinanceQuoteData($wishlistItem);
        }

        foreach ($this->_registryKeys as $key) {
            if (Mage::registry($key)) {
                Mage::unregister($key);
            }
        }

        Mage::register('current_wishlist_item', $wishlistItem);

        $request = $wishlistItem->getBuyRequest();
        $tmpData = [
            'car_data' => [],
            'finance_variables' => [],
            'monthly_price' => 0,
            'rockar_price' => 0,
            'is_hire' => 0,
            'cash_deposit' => 0,
            'cashback' => 0,
            'save_off_rrp' => 0,
            'lead_time' => 0
        ];
        $vehicleId = $request->getData('vehicleId');

        if (!$request && !((int) $vehicleId)) {
            return $tmpData;
        }

        $product = Mage::getModel('catalog/product')->load((int) $vehicleId);
        $allHelper = Mage::helper('rockar_all');
        $financeHelper = Mage::helper('financing_options');
        $fullFinanceData = $allHelper->jsonDecode($wishlistItem->getData('financing_data'));
        $savedFinanceData = $fullFinanceData;

        // Fix for passing product_id data further to approved used functionality,
        // which determines if must switch finance tables to AU
        Mage::app()->getRequest()
            ->setParam('product_id', $product->getId());

        $methodId = $fullFinanceData['method'] ?? $fullFinanceData['group_id'] ?? 0;

        if (isset($fullFinanceData[$methodId])) {
            $savedFinanceData = array_merge($fullFinanceData[$methodId], array_filter($fullFinanceData, 'is_scalar'));
        }

        $accessories = $allHelper->jsonDecode($wishlistItem->getData('accessories'));
        $product->setAccessories($accessories);

        // Rewrite here to used Peppermint_FinancingOptions_Helper_Interfaces_QuoteData::prepareParams for balloonPercentage finance variables
        if ($product->getId()) {
            $functionParams = Peppermint_FinancingOptions_Helper_Interfaces_QuoteData::prepareParams(
                $product,
                $savedFinanceData['mileage'] ?? 0,
                $savedFinanceData['term'] ?? 0,
                $savedFinanceData['deposit'] ?? 0,
                $savedFinanceData['depositMultiple'] ?? 0,
                $savedFinanceData['maintenance'] ?? 0,
                Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_WISHLIST,
                $accessories,
                $savedFinanceData['payment_type'] ?? '',
                $savedFinanceData['group_id'] ?? '',
                false,
                $savedFinanceData['balloonPercentage'] ?? 0
            );

            $tmpData = $financeHelper->getFinanceQuoteData($functionParams);
            $tmpData['lead_time'] = 0;
        }
        // Rewrite end

        return $allHelper->jsonEncode([
            'productId' => (int)$wishlistItem->getId(),
            'carData' => $tmpData['car_data'],
            'leadTime' => $tmpData['lead_time'],
            'financeVariables' => $tmpData['finance_variables'],
            'financeParams' => $fullFinanceData,
            'financeSliderSteps' => $allHelper->jsonDecode(Mage::helper('financing_options/config')->getAllSliderSteps()),
            'payInFullPayment' => $financeHelper->getDefaultPayInFullPayment(),
            'hirePayments' => $financeHelper->getHirePayments(),
            'activePayment' => $savedFinanceData,
            'rockarPrice' => $tmpData['rockar_price'],
            'saveOffRrp' => $tmpData['save_off_rrp'],
            'monthlyPrice' => $tmpData['monthly_price'],
            'isHire' => $financeHelper->isHirePaymentGroup($savedFinanceData['group_id']),
            'cashDeposit' => $tmpData['cash_deposit'],
            'cashback' => $tmpData['cashback'],
            'financeUrl' => $this->getUrl('rockar_savedcars/financing_ajax/options', ['_secure' => true]),
            'paymentSaveUrl' => $this->getUrl('financing/ajax/saveOnPdp', ['_secure' => true]),
            'calcType' => Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_WISHLIST
        ]);
    }
}
