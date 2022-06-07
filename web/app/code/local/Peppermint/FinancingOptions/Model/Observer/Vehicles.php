<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Observer_Vehicles extends Rockar_FinancingOptions_Model_Observer_Vehicles
{
    /**
     * Added finance data to the vehicles collection.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function addFinanceToCollection(Varien_Event_Observer $observer)
    {
        $vehicles = $observer->getEvent()->getData('vehicles');

        foreach ($vehicles as $vehicle) {
            $product = $vehicle;
            $savedFinanceData = Mage::helper('financing_options')->getSavedFinanceData(true);
            $accessories = [];

            $functionParams = Peppermint_FinancingOptions_Helper_Interfaces_QuoteData::prepareParams(
                $product,
                (int) $savedFinanceData['mileage'],
                (int) $savedFinanceData['term'],
                (int) $savedFinanceData['deposit'],
                (int) $savedFinanceData['depositMultiple'],
                (int) $savedFinanceData['maintenance'],
                Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_PDP,
                $accessories,
                $savedFinanceData['payment_type'],
                $savedFinanceData['group_id'],
                false,
                $savedFinanceData['balloonPercentage'] ?? 0
            );
            $financeData = Mage::helper('financing_options')->getFinanceQuoteData($functionParams);

            $product->setData('base_price', (float) $financeData['rockar_price']);
            $product->setData('month_price', (float) $financeData['monthly_price']);
            $product->setData('rrp_price', (float) $financeData['save_off_rrp']);
            $product->setData('cash_deposit', (float) $financeData['cash_deposit']);
            $product->setData('cashback', (float) $financeData['cashback']);
            $product->setData('is_hire', (int) $financeData['is_hire']);
            $product->setData('lead_time', 0);
        }

        return $this;
    }

    /**
     * Get Finance Data for product.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function getFinanceQuoteData(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        /**
         * @var Mage_Catalog_Model_Product
         */
        $vehicle = $event->getData('vehicle');
        $accessories = $event->getData('accessories');

        /**
         * @var Varien_Object
         */
        $eventReturn = $event->getData('event_return');

        $savedFinanceData = Mage::helper('financing_options')->getSavedFinanceData(true);
        $functionParams = Peppermint_FinancingOptions_Helper_Interfaces_QuoteData::prepareParams(
            $vehicle,
            (int) $savedFinanceData['mileage'],
            (int) $savedFinanceData['term'],
            (int) $savedFinanceData['deposit'],
            (int) $savedFinanceData['depositMultiple'],
            (int) $savedFinanceData['maintenance'],
            Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_PDP,
            $accessories,
            $savedFinanceData['payment_type'],
            $savedFinanceData['group_id'],
            false,
            $savedFinanceData['balloonPercentage'] ?? 0
        );

        $eventReturn->setData(Mage::helper('financing_options')->getFinanceQuoteData($functionParams));

        return $this;
    }

    /**
     * Rewrite parent function to add option_code to accessories\
     *
     * Overridden to add highligted & cosy_url
     *
     * {@inheritDoc}
     */
    public function extendCarData(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $carData = $event->getCarData();
        $accessories = $event->getAccessories();
        $calculations = $event->getCalculations();

        /**
         * @var Mage_Catalog_Model_Product $product
         */
        $product = $event->getProduct();

        /**
         * @var Rockar_FinancingOptions_Model_Options $financeOption
         */
        $financeOption = $event->getFinanceOption();
        $financeHelper = Mage::helper('financing_options');
        $vehicleHelper = Mage::helper('rockar_catalog/vehicle');
        $productTitle = $vehicleHelper->getTitle($product);

        if ($financeOption->isHirePayment()) {
            $result = [
                [
                    'label' => $productTitle,
                    'price' => (float) (isset($calculations['car_rental']) ? $calculations['car_rental']['value']: 0)
                ]
            ];
        } else {
            $result = [
                [
                    'label' => $productTitle,
                    'price' => (float) $product->getFinalPriceWithSaveOfRrp()
                ]
            ];
        }

        $options = $vehicleHelper->getOptionsData($product);

        foreach ($options['options'] as $groupTitle => $group) {
            $optionGroup = [];
            $optionPrice = 0;

            foreach ($group as $title => $option) {
                if (isset($option['price'])) {
                    $price = $financeHelper->calculateAccessoriesPricePerOption(
                        $financeOption,
                        $calculations,
                        $product,
                        $option['sku'],
                        $option['price']
                    );
                    $optionPrice += $price;
                    $optionGroup[] = [
                        'label' => $option['text'],
                        'price' => $price,
                        'id' => $option['sku'],
                        'highligted' => $option['highligted'],
                        'cosy_url' => $option['cosy_url']
                    ];
                }
            }

            $result[] = [
                'group' => $groupTitle,
                'items' => $optionGroup,
                'price' => $optionPrice,
                'open' => 0
            ];
        }

        $accessoriesGroup = [];
        $accessoriesPrice = 0;

        foreach ($accessories as $accessoryKey => $accessory) {
            $price = (float) $accessory['custom_price'] > 0
                ? (float) $accessory['custom_price']
                : (float) $accessory['price'];
            $name = $accessory['custom_name'] ?: $accessory['name'];
            $accessoryPrice = $financeHelper->calculateAccessoriesPricePerOption(
                $financeOption,
                $calculations,
                $product,
                $accessoryKey,
                $price
            );
            $accessoriesGroup[] = [
                'label' => $name,
                'price' => $accessoryPrice,
                'remove' => $accessory['id'],
                'id' => $accessory['identifier'] ?? '',
                'material_number' => $accessory['material_number'] ?? '',
                'option_code' => $accessory['option_code'] ?? ''
            ];
            $accessoriesPrice += $accessoryPrice;
        }

        $result[] = [
            'group' => Mage::helper('rockar_all')->__('Accessories'),
            'items' => $accessoriesGroup,
            'price' => $accessoriesPrice,
            'open' => 0
        ];

        $carData->setCarData($result);
    }
}
