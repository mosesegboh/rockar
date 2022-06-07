<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Carfinder
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Carfinder_Block_Layer_View extends Rockar2_Carfinder_Block_Layer_View
{
    /**
     * @var null|Peppermint_Sales_Helper_QuoteMail $_quoteHelper
     */
    protected $_quoteHelper = null;

    /**
     * @var null|Peppermint_OfferTags_Model_Resource_OfferTagRules_Collection
     */
    private $_offerTagRulesCollection = null;

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_quoteHelper = Mage::helper('peppermint_sales/quoteMail');
    }

    /**
     * Get attribute option image.
     *
     * @param $optionId
     *
     * @return string
     */
    public function getAttributeOptionImage($optionId)
    {
        if (!$this->_attributesImages) {
            $this->_attributesImages = Mage::getResourceModel('eav/entity_attribute_option')->getAttributeImages();
        }

        if ($this->_attributesImages[$optionId]) {
            if (Zend_Uri::check($this->_attributesImages[$optionId])) {
                return $this->escapeHtml($this->_attributesImages[$optionId]);
            }

            return parent::getAttributeOptionImage($optionId);
        }
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
     * Return model step attributes for current store
     *
     * @return string[]
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getModelStepAttributes()
    {
        switch (str_replace('_store_view', '', Mage::app()->getStore()->getCode())) {
        case 'mini':
            $result = [
                'model_navigation_attribute' => 'min_model_carousel',
                'model_matrix_attribute' => 'min_model_matrix_carousel',
            ];
            break;
        case 'motorrad':
            $result = [
                'model_navigation_attribute' => 'mot_model_carousel',
                'model_matrix_attribute' => 'mot_model_matrix_carousel'
            ];
            break;
        default :
            $result = [
                'model_navigation_attribute' => 'bmw_model_carousel',
                'model_matrix_attribute' => 'bmw_model_matrix_carousel'
            ];
            break;
        }

        return $result;
    }

    /**
     * Return mapped model and model matrix attribute values
     *
     * @return object|Peppermint_Catalog_Model_Resource_MatrixMapping_Collection
     */
    public function getModelMatrixMap()
    {
        return Mage::getModel('peppermint_catalog/matrixMapping')->getCollection();
    }

    /**
     * Get all layer filters as JSON
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getFiltersJson()
    {
        if (is_null($this->_filters)) {
            $filters = [];
            $filterableAttributes = $this->_getFilterableAttributes();
            $activeFilters = [];

            if ($this->getChild('layer_state')->getActiveFilters()) {
                foreach ($this->getChild('layer_state')->getActiveFilters() as $filter) {
                    $activeFilters[$filter->getFilter()->getRequestVar()][] = $filter->getValue();
                }
            }

            $activeFiltersObject = new Varien_Object();
            $activeFiltersObject->setData($activeFilters);

            $hiddenOptions = $this->getHiddenOptions($filterableAttributes);
            $modelAttribute = Mage::helper('rockar_all')->getModelAttribute();

            $matrixAttributeMap = $this->getModelMatrixMap();
            $attributes = $this->getModelStepAttributes();
            $matrixAttr = $attributes['model_matrix_attribute'];

            $now = (new \DateTime())->setTime(0, 0);

            foreach ($filterableAttributes as $attribute) {
                $filter = $this->getChild($attribute->getAttributeCode() . '_filter');
                $options = [];
                $hasSelected = false;

                foreach ($filter->getItems() as $filterItem) {
                    $image = $this->getAttributeOptionImage($filterItem->getValue());

                    $state = (!isset($activeFilters[$filterItem->getFilter()->getRequestVar()]))
                        ? false
                        : in_array(
                            $filterItem->getValue(),
                            $activeFilters[$filterItem->getFilter()->getRequestVar()]
                        );

                    if (!in_array($filterItem->getValue(), $hiddenOptions)) {
                        $optionData = array(
                            'id' => $filterItem->getValue(),
                            'title' => $filterItem->getLabel(),
                            'count' => $filterItem->getCount(),
                            'state' => $state,
                            'image' => $image
                        );

                        if ($attribute instanceof Peppermint_OfferTags_Model_OfferTagAttribute) {
                            $offerTagRulesCollection = $this->getOfferTagRulesCollection();

                            foreach ($offerTagRulesCollection->getItems() as $offerTagRule) {
                                if ($offerTagRule->getOfferTagId() == $filterItem->getValue()) {
                                    $optionData['priority'] = $offerTagRule->getPriority();
                                    $optionData['rule_id'] = $offerTagRule->getRuleId();
                                    break;
                                }
                            }
                        }

                        if (
                            Mage::getStoreConfig(
                                'rockar_catalog/car_finder_display_settings/show_finance_prices_for_model_filter'
                            )
                            && $attribute->getAttributeCode() == $modelAttribute
                            && $filterItem->getCount() > 0
                        ) {
                            $financeData = Mage::helper('financing_options')->getFinancePricesForModel(
                                $filterItem->getValue(),
                                $modelAttribute,
                                Mage::app()->getLayout()->getBlock('product_list')->getLoadedProductCollection()
                            );
                            $optionData['price'] = $financeData['price'];
                            $optionData['monthlyPrice'] = $financeData['monthly_price'];
                        }

                        if ($attribute->getName() === $matrixAttr) {
                            $matrixAttribute = $matrixAttributeMap->getItemByColumnValue(
                                'model_matrix_carousel',
                                $filterItem->getLabel()
                            );

                            if (!$matrixAttribute) {
                                continue;
                            }

                            if ($filterItem->getCount() === 0 && $modelRunOutDate = $matrixAttribute->getRunOutDate()) {
                                $modelRunOutDate = (\DateTime::createFromFormat('Y-m-d', $modelRunOutDate))
                                    ->setTime(0, 0);

                                if (
                                    $now > $modelRunOutDate
                                    && !($this->_checkInCatalog($matrixAttr, $filterItem->getValue()))
                                ) {
                                    continue;
                                }
                            }

                            $optionData['modelId'] = $matrixAttribute ? $matrixAttribute->getData('model_carousel') : null;
                            $optionData['modelPosition'] = $matrixAttribute ? $matrixAttribute->getData('position') : 0;

                            if (!$optionData['image']) {
                                $optionData['image'] = Mage::helper('peppermint_catalog/images')->getModelMatrixPlaceholderImage(
                                    Mage::app()->getStore()->getId()
                                );
                            }
                        }

                        $options[] = $optionData;
                    }
                    $hasSelected = $hasSelected || $state;
                }

                $attributeData = new Varien_Object(
                    [
                        'code' => $attribute->getAttributeCode(),
                        'label' => $attribute->getStoreLabel(),
                        'options' => $options,
                        'hasSelected' => $hasSelected
                    ]
                );

                Mage::dispatchEvent(
                    'rockar_filter_attribute_add_before', [
                        'attribute_data' => $attributeData,
                        'attribute' => $attribute
                    ]
                );

                $attributeData = $attributeData->toArray();

                $filters[] = $attributeData;
            }

            Mage::helper('rockar_all/session')->addVarienObjectToSession(
                Rockar_Carfinder_Helper_Data::CAR_FINDER_FILTERS_SESSION_KEY,
                $activeFiltersObject, true
            );

            $this->_filters = Mage::helper('rockar_all')->jsonEncode($filters);
        }

        return $this->_filters;
    }

    /**
     * Check whether we have any simple product in catalog for the given model
     *
     * @param string $matrixAttr
     * @param int $matrixValue
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _checkInCatalog($matrixAttr, $matrixValue)
    {
        $activeStore = Mage::app()->getStore();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $productCount = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect($matrixAttr)
            ->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->addAttributeToFilter($matrixAttr, $matrixValue)
            ->getSize();

        Mage::app()->setCurrentStore($activeStore);

        return $productCount > 0;
    }

    /**
     * Get instalment group id for current store.
     *
     * @return string|null
     */
    public function getInstalmentGroupId()
    {
        return Mage::helper('peppermint_financingoptions')->getGroupIdByMethodType(
            Peppermint_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_INSTALMENT
        );
    }

    /**
     * Should show the button
     * @return bool
     */
    public function getShouldShowGetQuoteCta(): bool
    {
        return (bool) $this->_quoteHelper->getConfigEnabled();
    }

    /**
     * Get Quote Url
     * @return string
     */
    public function getQuoteUrl(): string
    {
        return $this->_quoteHelper->getQuoteUrl();
    }

    /**
     * Should show the "Continue Shopping" Cta inside
     * the get quote cta
     * @return bool
     */
    public function getShowContinueShoppingCta(): bool
    {
        return true;
    }

    /**
     * Get Customer Is Logged In?
     * @return bool
     */
    public function getCustomerIsLoggedIn(): bool
    {
        return (bool) Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Get Continue Shopping Url
     * @return string
     */
    public function getContinueShoppingUrl(): string
    {
        // The customer is already on the Car Finder results page
        return '';
    }

    /**
     * Get Customer Login Url
     * @return string
     */
    public function getCustomerLoginUrl(): string
    {
        return $this->getUrl('customer/account/login');
    }

    /**
     * Whether or not to redirect the user when they select the
     * "continue shopping" call to action
     * @return bool
     */
    public function getRedirectToContinueShopping(): bool
    {
        // The customer is already on the Car Finder results page
        return false;
    }

    /**
     * Get Offer Tag Rules Collection
     * @return Peppermint_OfferTags_Model_Resource_OfferTagRules_Collection
     */
    private function getOfferTagRulesCollection(): Peppermint_OfferTags_Model_Resource_OfferTagRules_Collection
    {
        if ($this->_offerTagRulesCollection === null) {
            $this->_offerTagRulesCollection = Mage::getModel('peppermint_offertags/offerTagRules')->getCollection();
        }

        return $this->_offerTagRulesCollection;
    }
}
