<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Helper_Vehicle extends Rockar2_Catalog_Helper_Vehicle
{
    const BRAND_BMW = 'bmw';
    const BRAND_MINI = 'min';
    const BRAND_MINI_FULL = 'mini';
    const BRAND_MOTORRAD = 'mot';
    const BRAND_MOTORRAD_FULL = 'motorrad';

    const CONDITION_NEW = 'N';
    const CONDITION_USED = 'U';
    const CONDITION_TEST_DRIVE = 'T';

    /** @var array Collect processed IDs to avoid running the same action multiple times */
    protected $_processedConfigurables = [];

    /**
     * Get custom option data for a vehicle
     *
     * @param integer $productId
     * @return array
     */
    public function getCustomOptions($productId)
    {
        $optionCollection = Mage::getModel('catalog/product_option')->getCollection()
            ->addFieldToSelect(['option_id'])
            ->addFieldToFilter('product_id', $productId);
        $optionCollection->getSelect()
            ->join(
                ['option_title' => 'catalog_product_option_title'],
                'option_title.option_id = main_table.option_id',
                ['option_type' => 'option_title.title']
            )->join(
                ['option_type_value' => 'catalog_product_option_type_value'],
                'option_type_value.option_id = main_table.option_id',
                ['option_type_id' => 'option_type_value.option_id']
            )->join(
                ['option_type_title' => 'catalog_product_option_type_title'],
                'option_type_title.option_type_id = option_type_value.option_type_id',
                ['title' => 'option_type_title.title']
            )->join(
                ['option_type_price' => 'catalog_product_option_type_price'],
                'option_type_price.option_type_id = option_type_value.option_type_id',
                ['price' => 'option_type_price.price']
            );
        $result = $optionCollection->getData();

        //get CustomOptions total price
        $prices = [
            'Extra Options' => false,
            'Line/Packages' => false,
            'Options' => false
        ];

        foreach ($result as $key => $item) {
            $result[$key]['product_id'] = $productId;
            if (isset($prices[$item['option_type']])) {
                $prices[$item['option_type']] += $item['price'];
            }
        }
        $result[] = $prices;

        return $result;
    }

    /**
     * Update the group price, lead time and mm description of the configurable
     * product with the cheapest associated simple data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return void
     * @throws Exception
     */
    public function updateConfigurableGroupPrice($product)
    {
        if (
            $product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            && !Mage::helper('rockar_catalog/carFinder')->isSimpleOnlyResultsMode()
        ) {
            if (in_array($product->getId(), $this->_processedConfigurables)) {
                // This product is already updated do not repeat this action
                return;
            }

            $simpleProducts = Mage::getModel('catalog/product_type_configurable')->setProduct($product)
                ->getUsedProductCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

            $simpleProducts->getSelect()
                ->joinLeft(
                    ['lead_time' => 'rockar_lead_time'],
                    'e.sku = lead_time.identifier',
                    ['lead_time' => 'lead_time.available_in']
                )
                ->group('e.entity_id')
                ->order(new Zend_Db_Expr('lead_time IS NULL, lead_time ASC'));


            if ($simpleProducts->getSize()) {
                foreach ($simpleProducts as $simpleProduct) {
                    $pricesArray[] = [
                        'product' => $simpleProduct,
                        'id' => $simpleProduct->getId(),
                        'price' => $this->getFinalPrice($simpleProduct)
                    ];
                }

                $cheapestProduct = $this->_getCheapestValue($pricesArray);
                $newGroupPriceData = $this->_getNewGroupPriceArray($cheapestProduct);
                //Removing old group price before adding new one
                Mage::getResourceModel('catalog/product_attribute_backend_groupprice')->deletePriceData($product->getId());
                $product->setData('group_price', $newGroupPriceData);

                $leadTime = ceil(($this->_getLeadTime($cheapestProduct)) / 7);
                $product->setData('cheapest_product_lead_time', $leadTime);
                $product->setData('default_lead_time', $leadTime);
                $product->setData('vin_number', $this->_getVinNumber($cheapestProduct));
                $product->setData('short_vin_number', $this->_getShortVinNumber($cheapestProduct));

                if ($mmDescription = $this->_getMmDescription($cheapestProduct)) {
                    $product->setData($mmDescription['store'] . '_mm_description', $mmDescription['mm_description']);
                }

                $appEmulation = Mage::getSingleton('core/app_emulation');

                $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(Mage_Core_Model_App::ADMIN_STORE_ID);

                $product->getResource()
                    ->save($product);

                $event = Mage::getSingleton('index/indexer')->logEvent(
                    $product,
                    $product->getResource()
                        ->getType(),
                    Mage_Index_Model_Event::TYPE_SAVE,
                    false
                );

                Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat')
                    ->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)
                    ->processEvent($event);

                Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($product->getId(), true);

                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            }

            $this->_processedConfigurables[] = $product->getId();
        }
    }

    /**
     * Get product first available vehicle
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $color
     *
     * @return Mage_Catalog_Model_Product|boolean
     */
    public function getFirstVehicle($product, $color = null)
    {
        if (!array_key_exists($product->getId(), $this->_firstVehiclesCache)) {
            $vehicle = null;

            if ($product->getData('vin_number')) {
                $model = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
                $vehicleId = $model->getUsedProductCollection()
                    ->addAttributeToFilter('vin_number', $product->getData('vin_number'))
                    ->setCurPage(1)
                    ->setPageSize(1)
                    ->getFirstItem()
                    ->getId();

                $vehicle = Mage::getModel('catalog/product')->load($vehicleId);
            }

            if (!$vehicle || !$vehicle->getId()) {
                $vehicle = parent::getFirstVehicle($product, $color);
            }

            if ($vehicle) {
                $this->_firstVehiclesCache[$product->getId()] = $vehicle;
            }
        }

        return $this->_firstVehiclesCache[$product->getId()];
    }

    /**
     * returns the cheapest value from an array
     *
     * @param array $data
     *
     * @return array
     */
    protected function _getCheapestValue($data)
    {
        if (is_array($data)) {
            $prices = array_column($data, 'price');

            $cheapest = $data[array_search(min($prices), $prices)];
        }

        return $cheapest ?? [];
    }

    /**
     * Helper method to return brands code
     *
     * @return array
     */
    public function getBrandsCodeArray()
    {
        return [
            self::BRAND_BMW,
            self::BRAND_MINI,
            self::BRAND_MOTORRAD
        ];
    }

    /**
     * Return cheapest simple product vin number
     *
     * @param array $productArr
     * @return mixed
     */
    protected function _getVinNumber($productArr)
    {
        $simpleProduct = $productArr['product'];

        return $simpleProduct->getVinNumber();
    }

    /**
     * Return cheapest simple product short vin number
     *
     * @param array $productArr
     * @return mixed
     */
    protected function _getShortVinNumber($productArr)
    {
        $simpleProduct = $productArr['product'];

        return $simpleProduct->getShortVinNumber();
    }

    /**
     * Get the mm_description from cheapest product price array
     *
     * @param array $data
     *
     * @return array|boolean
     */
    protected function _getMmDescription($productArr)
    {
        $simpleProduct = $productArr['product'];

        foreach ($this->getBrandsCodeArray() as $store) {
            if ($simpleProduct->getData($store . '_mm_description')) {
                $result = [
                    'store' => $store,
                    'mm_description' => $simpleProduct->getData($store . '_mm_description')
                ];
            }
        }

        return $result ?? false;
    }

    /**
     * Get the leadtime from cheapest product price array
     *
     * @param $productArr
     * @return string
     * @throws Exception
     */
    protected function _getLeadTime($productArr)
    {
        $simpleProduct = $productArr['product'];

        return $simpleProduct->getLeadTime() ?? $this->_calculateLeadTimeForNewSimple($simpleProduct);
    }

    /**
     * During import we might face situation when lead times are not yet created
     * for the provided simple in such case we calculate lead time on fly in a
     * way it is done on SPC side. Later actual lead time should be used based on
     * product availability
     *
     * @param $product
     * @return mixed
     * @throws Exception
     */
    protected function _calculateLeadTimeForNewSimple($product)
    {
        $today = new DateTime();
        $rfsDate = new DateTime($product->getRfsDate());

        $leadTimeFromProduct = new Varien_Object();
        $leadTimeFromProduct->setData([
            'available_on' => $today < $rfsDate ? $product->getRfsDate() : null,
            'available_in' => $today < $rfsDate ? null : Peppermint_LeadTime_Helper_Data::DEFAULT_LEAD_TIME,
            'minimum_days' => Peppermint_LeadTime_Helper_Data::DEFAULT_LEAD_TIME
        ]);

        return Mage::helper('rockar_lead_time')->getLeadTimeFromConfiguration($leadTimeFromProduct);
    }

    /**
     * Get the configurable product of a simple product
     *
     * @param Mage_Catalog_Model_Product|string $product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getConfigurableFromSimple($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product = $product->getId();
        }

        $parentIdByChild = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product);
        $parentIdByChild = reset($parentIdByChild);

        return Mage::getModel('catalog/product')->load($parentIdByChild);
    }

    /**
     * Get new group price array from catalog price rules if exists or from cheapest product
     *
     * @param array $productArr
     *
     * @return array
     */
    protected function _getNewGroupPriceArray($productArr)
    {
        $groupPrices = [];
        $customerGroupIds = Mage::getModel('customer/group')->getCollection()
            ->addFieldToSelect('customer_group_id');

        foreach ($customerGroupIds as $customerGroupId) {
            $cusGroupId = $customerGroupId->getCustomerGroupId();
            $rulePriceData = Mage::getModel('catalogrule/rule_product_price')->getCollection()
                ->addFieldToFilter('product_id', $productArr['id'])
                ->addFieldToFilter('customer_group_id', $cusGroupId);

            if ($rulePriceData->getSize()) {
                $rulePriceArray = [];
                foreach ($rulePriceData as $rulePrice) {
                    $rulePriceArray[] = [
                        'cust_group' => $rulePrice['customer_group_id'],
                        'price' => $rulePrice['rule_price']
                    ];
                }
                $groupPrices[] = $this->_getCheapestValue($rulePriceArray);
            } else {
                $groupPrices[] = [
                    'cust_group' => $cusGroupId,
                    'price' => $productArr['price']
                ];
            }
        }

        return $groupPrices;
    }

    /**
     * Get the final price with rule price taken into account if exists
     *
     * @param Peppermint_Catalog_Model_Product $product
     *
     * @return string|float
     */
    public function getFinalPrice($product)
    {
        $storeId = $product->getWebsiteIds()[0] ?? false;

        if (
            $storeId
            && $rulePrice = Mage::getResourceModel('catalogrule/rule')->getRulePrice(
                date('Y-m-d'),
                $storeId,
                Mage::getSingleton('customer/session')->getCustomerGroupId(),
                $product->getId()
            )
        ) {
            return $rulePrice;
        }

        return $product->getFinalPrice();
    }

    /**
     * Re-index configurable product attribute and category from order
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    public function reindexConfigProductFromOrder(Mage_Sales_Model_Order $order)
    {
        $orderItem = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order);
        $productId = $orderItem ? $orderItem->getProduct()->getId() : null;

        if ($productId) {
            try {
                Mage::getResourceModel('catalog/category_indexer_product')->catalogProductMassAction(
                    Mage::getModel('index/event')->setNewData(['product_ids' => [$productId]])
                );
                Mage::getResourceModel('catalog/product_indexer_eav_source')->reindexEntities([$productId]);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Get product options data
     *
     * Overridden to add highligted & cosy_url
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public function getOptionsData($product)
    {
        $optionsTotal = 0;
        $options = [];

        foreach ($product->getOptions() as $option) {
            foreach ($option->getValues() as $value) {

                Mage::dispatchEvent('rockar_catalog_prepare_option_data_before', [
                    'product' => $product,
                    'option' => $option,
                    'option_values' => $value,
                ]);

                if ($option->getTitle() == 'Configured options') {
                    break;
                } else {
                    $options[$option->getTitle()][] = [
                        'price' => $value->getPrice(),
                        'text' => $value->getTitle(),
                        'sku' => $value->getSku(),
                        'id' => $value->getOptionId(),
                        'value' => $value->getOptionTypeId(),
                        'highligted' => $value->getData('highligted'),
                        'cosy_url' => $value->getData('cosy_url')
                    ];
                }

                $optionsTotal += (float)$value->getPrice();
            }
        }

        return [
            'options' => $options,
            'total' => $optionsTotal,
        ];
    }
}
