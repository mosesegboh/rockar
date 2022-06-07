<?php
/**
 * @category Peppermint
 * @package Peppermint\Orderamend
 * @author Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Orderamend_Helper_Grid_ProductSwap extends Rockar_Orderamend_Helper_Grid_ProductSwap
{
    protected $_interiorAttribute = 'interior_description';

    /**
     * Adds vin_number column, removes vista_order_number as not used
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->_gridBlock->addColumnAfter(
            'vin_number',
            [
                'name' => 'vin_number',
                'index' => 'vin_number',
                'label' => 'VIN',
                'type' => 'text',
                'align' => 'left',
                'width' => 70
            ],
            'vista_order_number'
        );
        $this->_gridBlock->removeColumn('vista_order_number');
    }

    /**
     * {@inheritdoc}
     */
    protected function _getCollectionAttributes()
    {
        return [
            'short_title',
            'title',
            'short_subtitle',
            'subtitle',
            'price',
            'final_price',
            'color',
            $this->getInteriorAttribute(),
            'visible_in',
            'visibility',
            'derivative',
            'vin_number',
            'fuel_type',
            'cap_code',
            $this->getTransmissionAttribute(),
            $this->getBodystyleAttribute(),
            $this->getModelYearAttribute(),
            $this->getEngineAttribute(),
        ];
    }

    /**
     * Format collection
     *
     * @return array
     */
    public function formatCollection()
    {
        $result = [];

        /**
         * This filter to function loads all of the collection products and if we add it on prepareCollection() then
         * when default magento applies filters, collection is already loaded and no filter is applied. Thats why
         * we only want to add options to result once the collection has been fully processed and loaded by magento
         * when applying grid filters.
         */
        $this->getCollection()->addOptionsToResult();
        $currentProductId = Mage::helper('rockar_orderamend')->getProduct()->getId();
        $vehicleHelper = Mage::helper('rockar_catalog/vehicle');

        foreach ($this->getCollection() as $product) {
            $leadTime = (int)($product->getData('lead_time') ?: Mage::helper('rockar_lead_time')->getLeadTime($product)) . ' ' . $this->__('days');

            /**
             * @var Mage_Catalog_Model_Product $product
             *
             * Note: If any of the columns data is changed, then check if it also needs corresponding adjustments
             * in filtering. Maybe need to add custom filter_condition_callback.
             */
            $productData = [
                'entity_id' => (int)$product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'title' => $vehicleHelper->getTitle($product),
                'subtitle' => $vehicleHelper->getSubTitle($product),
                'price' => $product->getFinalPrice(),
                'color' => $product->getData('color') ? $product->getAttributeText('color') : '',
                $this->getInteriorAttribute() => $product->getData($this->getInteriorAttribute()) ? $product->getAttributeText($this->getInteriorAttribute()) : '',
                'fuel_type' => $product->getData('fuel_type') ? $product->getAttributeText('fuel_type') : '',
                'options' => $this->formatProductOptions($product->getOptions()),
                'lead_time' => $leadTime,
                'type_id' => $product->getData('type_id'),
                'visibility' => $product->getData('visibility'),
                'visible_in' => $product->getData('visible_in'),
                'monthly_price' => $product->getData('monthly_price'),
                'derivative' => $product->getData('derivative'),
                'vin_number' => $product->getData('vin_number'),
                'cap_code' => $product->getData('cap_code'),
                'is_current' => $product->getId() == $currentProductId,
                $this->getTransmissionAttribute() => $product->getData($this->getTransmissionAttribute()) ? $product->getAttributeText($this->getTransmissionAttribute()) : '',
                $this->getBodystyleAttribute() => $product->getData($this->getBodystyleAttribute()) ? $product->getAttributeText($this->getBodystyleAttribute()) : '',
                $this->getEngineAttribute() => $product->getData($this->getEngineAttribute()) ? $product->getAttributeText($this->getEngineAttribute()) : '',
                $this->getModelYearAttribute() => $product->getData($this->getModelYearAttribute()),
            ];

            /**
             * Override currently active product data from quote
             */
            if ($product->getId() == $currentProductId) {
                $finannceDataVariables = Mage::helper('rockar_all')->jsonDecode(
                    Mage::helper('rockar_checkout/quote')->getFirstVisibleQuoteItem(
                        Mage::helper('rockar_orderamend')->getQuote()
                    )->getData('finance_data_variables')
                );

                if (!empty($finannceDataVariables) && isset($finannceDataVariables['monthly_price'])) {
                    $productData['monthly_price'] = $finannceDataVariables['monthly_price'];
                }
            }

            $productDataObject = new Varien_Object($productData);

            Mage::dispatchEvent('rockar_orderAmend_product_swap_list_data_prepare', [
                'product' => $product,
                'data' => $productDataObject,
            ]);

            $result[(int)$product->getId()] = $productDataObject->toArray();
        }

        return $result;
    }
}
