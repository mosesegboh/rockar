<?php
/**
 * @category Peppermint
 * @package Peppermint\Catalog
 * @author Jevgenijs <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_Catalog_Block_Product_List
 */
class Peppermint_Catalog_Block_Product_List extends Rockar2_Catalog_Block_Product_List
{
    /**
     * Get products data
     * Override parent method to adjust product pod attributes
     *
     * @return array
     */
    protected function _getProductsData()
    {
        $result = [];

        /** @var Rockar_Catalog_Helper_CarFinder $helper */
        $helper = Mage::helper('rockar_catalog/carFinder');
        /** @var Rockar_MySavedCars_Helper_Data $wishlistHelper */
        $wishlistHelper = Mage::helper('rockar_mysavedcars');
        /** @var Rockar_Compare_Helper_Data $compareHelper */
        $compareHelper = Mage::helper('rockar_compare');
        $allHelper = Mage::helper('rockar_all');
        $vehicleHelper = Mage::helper('rockar_catalog/vehicle');

        $isRunningCostsEnabled = $helper->isModuleEnabled('Rockar_RunningCosts');
        $displayAnnual = !empty(
            Mage::helper('rockar_partexchange')
                ->loadPartExchangeFromSession(Rockar_PartExchange_Helper_Data::RUNNING_COSTS_PART_EXCHANGE_KEY)
                ->getData()
        );

        $productCollection = $this->getLoadedProductCollection();

        foreach ($productCollection as $product) {
            $carAttributes = Mage::helper('peppermint_catalog/attributes')
                ->mapCarAttributes($vehicleHelper->getFirstVehicle($product));

            /**
             * @var Mage_Catalog_Model_Product $product
             */
            $productId = (int) $product->getId();

            // IMPORTANT!!!: If this final price logic is changed, corresponding changes have to also be made
            // for sorting functionality in \Rockar2_Catalog_Helper_Collection_Sort::_addCashSortFieldExpression
            // for price sorting functionality in \Rockar2_Catalog_Model_Resource_Layer_Filter_Price::_getPriceExpression
            if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                $price = (float) $product->getFinalPrice() + $product->getOptionsPrice();
            } else {
                $price = (float) $product->getFinalPrice();
            }

            try {
                $productData = [
                    'productId' => $productId,
                    'sku' => $product->getSku(),
                    'url' => $product->getProductUrl(),
                    'configuratorUrl' => $this->getUrl('configurator/build/new', ['vehicle' => $productId]),
                    'title' => $vehicleHelper->getTitle($product),
                    'subtitle' => $vehicleHelper->getSubTitle($product),
                    'bodystyle' => $product->getAttributeText('bodystyle') ?: '',
                    'short_description' => $product->getShortDescription(),
                    'price' => $price,
                    'monthlyPrice' => (float) $product->getData('monthly_price'),
                    'cashDeposit' => (float) $product->getData('cash_deposit'),
                    'cashback' => (float) $product->getData('cashback'),
                    'isHire' => (int) $product->getData('is_hire'),
                    'tooltip' => $this->getTooltipLabel(),
                    'image' => (string) Mage::helper('catalog/image')->init($product, 'small_image')
                        ->resize($helper->getProductSmallImageWidth(), $helper->getProductSmallImageHeight()),
                    'carAttributes' => $carAttributes,
                    'saveOffRrp' => ((float) $product->getRrp() > 0)
                        ? (float) $product->getRrp() - (float) $product->getFinalPrice()
                        : 0,
                    'overlayText' => $helper->getRunningCostOverlayText(),
                    'isInWishlist' => (bool) $wishlistHelper->isInWishlist($product->getId()),
                    'saveWishlistUrl' => $wishlistHelper->getSaveUrl($product->getId()),
                    'removeFromWishlistUrl' => $wishlistHelper->getRemoveByProductIdUrl($product->getId()),
                    'compareAddUrl' => $compareHelper->getAddUrl($product->getId()),
                    'compareRemoveUrl' => $compareHelper->getRemoveUrl($product),
                    'isInCompareList' => $compareHelper->isInCompareList($product->getId()),
                    'lessAnnual' => $isRunningCostsEnabled
                        ? Mage::helper('rockar_runningcosts')->calculateRunningCost($product)
                        : '',
                    'displayAnnual' => $displayAnnual,
                    'carData' => [
                        'gearbox_type' => $product->getData('trans_type') ? $product->getAttributeText('trans_type') : '',
                        'fuel_type' => $product->getData('fuel_type') ? $product->getAttributeText('fuel_type') : '',
                        'wheel_for_drive' => $product->getData('wheel_for_drive') ? $product->getAttributeText('wheel_for_drive') : ''
                    ],
                    'leadTime' => (int) $product->getData('cheapest_product_lead_time'),
                    'customOptions' => Mage::helper('rockar_catalog/vehicle')->getCustomOptions($product->getId()),
                    'offerTagId' => $product->getData('offer_tag_id'),
                    'offerTagData' => $product->getData('offer_tag')
                        ? $allHelper->jsonDecode($product->getData('offer_tag'))
                        : null
                ];
                $productDataObject = new Varien_Object($productData);

                Mage::dispatchEvent('rockar_catalog_product_list_data_prepare', [
                    'product' => $product,
                    'data' => $productDataObject,
                ]);

                $result[] = $productDataObject->toArray();
            } catch (Exception $e) {
                Mage::logException($e);
                // To get full string as well, otherwise exception stack trace is truncated
                Mage::log($e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Prepare model step and navigation titles for model select step
     *
     * @return string[]
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getModelStepTitles()
    {
        return Mage::helper('peppermint_carfinder')->getModelStepTitles();
    }

    /**
     * Override parent method to add attributes to select
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     * @throws Mage_Core_Exception
     */
    public function _getProductCollection()
    {
        $this->_productCollection = parent::_getProductCollection()->addAttributeToSelect(
            [
                'km',
                'fuel_type',
                'vehicle_condition',
                'transmission',
                'offer_tag_id',
                'offer_tag'
            ]
        );

        return $this->_productCollection;
    }
}
