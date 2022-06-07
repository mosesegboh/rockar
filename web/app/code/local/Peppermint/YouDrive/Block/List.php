<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Sergejs Plisko <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Block_List extends Rockar_YouDrive_Block_List
{
    const INITIAL_SLIDE_INDEX = 0;

    /**
     * Get local store data
     *
     * @return string
     */
    public function getLocalStoreData()
    {
        $storeData = [];
        $stores = Mage::getResourceModel('rockar_localstores/stores_collection')
            ->getYouDriveStore()->getItems();

        if ($stores) {
            foreach ($stores as $store) {
                if ($store->getId()) {
                    $storeData[$store->getCode()] = [
                        'id' => $store->getEntityId(),
                        'code' => $store->getCode(),
                        'title' => $store->getName(),
                        'city' => $store->getStoreAddress()->getCity(),
                        'state' => $store->getStoreAddress()->getState(),
                        'postal_code' => $store->getStoreAddress()->getPostalCode(),
                        'location' => [
                            'lat' => (float) $store->getStoreAddress()->getLatitude(),
                            'lng' => (float) $store->getStoreAddress()->getLongitude()
                        ],
                        'street' => implode(
                            ' ',
                            array_filter(
                                [
                                    $store->getStoreAddress()->getData('address_line_1'),
                                    $store->getStoreAddress()->getData('address_line_2'),
                                    $store->getStoreAddress()->getData('address_line_3')
                                ]
                            )
                        ),
                        'phone' => $store->getStoreAddress()->getMainPhone(),
                        'tooltipInfo' => $store->getDescription(),
                        'associated_compound_dealer' => $store->getAssociatedCompoundDealer()
                    ];
                }
            }
        }

        return Mage::helper('rockar_all')->jsonEncode($storeData);
    }

    /**
     * Get youdrive options
     *
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getYouDriveOptions()
    {
        $youdriveHelper = Mage::helper('rockar_youdrive');

        $result = [
            'mapApiKey' => Mage::helper('rockar_all')->getMapKey(),
            'formKey' => Mage::getSingleton('core/session')->getFormKey(),
            'youdriveGetModelsUrl' => $this->getUrl('test-drives/ajax/models'),
            'youdriveSaveBookingUrl' => $this->getUrl('test-drives/ajax/addBooking'),
            'updateBookingProgressUrl' => $this->getUrl('test-drives/ajax/updateBookingProgress'),
            'isRebooking' => $this->getIsRebooking(),
            'clearBookingProgressUrl' => $this->getUrl('test-drives/ajax/clearBookingProgress'),
            'preselectStep' => $this->getStepFromUrl(),
            'customerUpdateUrl' => $this->getUrl('test-drives/ajax/customerUpdate'),
            'homeUrl' => $this->getUrl(''),
            'testDriveUrl' => $this->getUrl('test-drives'),
            'cancelBookingUrl' => $this->getUrl('test-drives/ajax/cancelBooking'),
            'availableTimesUrl' => $this->getUrl('test-drives/ajax/availableTimes'),
            'notAvailableDatesUrl' => $this->getUrl('test-drives/ajax/notAvailableDates'),
            'dealershipFindText' => Mage::getStoreConfig('rockar_youdrive/youdrive_general/find_dealer_text'),
            'showDealersInList' => Mage::getStoreConfig('rockar_localstores/rockar_general/show_dealer_amount'),
            'firstAvailableDateUrl' => $this->getUrl('test-drives/ajax/firstAvailableDate'),
            'maxCarsToBook' => $youdriveHelper->getVehiclesInBookingLimit(),
            'youdrivePageTitle' => $this->getYoudrivePageTitle(),
            'youdrivePageMessage' => $this->getYoudrivePageMessage(),
            'loginUrl' => $this->getUrl('test-drives/index/login'),
            'youdriveSaveCustomerUrl' => $this->getUrl('test-drives/ajax/customerUpdate'),
            'testDriveTitle' => $youdriveHelper->getTestDriveTitle(),
            'emailInfoText' => $youdriveHelper->getEmailInfoText()
        ];

        return Mage::helper('rockar_all')->jsonEncode($result);
    }

    /**
     * {@inheritDoc}
     */
    public function getSelectedModelData()
    {
        $currentProgress = Mage::helper('rockar_youdrive/booking_progress')->getCurrentProgress();

        if (!empty($currentProgress['vehicle_ids'])) {
            $models = $this->_getCurrentProgressModels();

            foreach ($models as $model) {
                // Search for the same model as they are already loaded, when found, merge data
                if (in_array($model['id'], $currentProgress['vehicle_ids'])) {
                    $currentProgress = $model + $currentProgress;
                }
            }
        }

        $currentProgress['stores'] = $this->_getCurrentProgressStores();

        return Mage::helper('rockar_all')->jsonEncode($currentProgress);
    }

    /**
     * Prepare youdrive page title
     *
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getYoudrivePageTitle()
    {
        $pageTitle = 'Choose a ';

        switch ($this->_getCurrentBrand()) {
            case Peppermint_Catalog_Helper_Vehicle::BRAND_BMW:
                $pageTitle .= 'BMW';
                break;
            case Peppermint_Catalog_Helper_Vehicle::BRAND_MINI_FULL:
                $pageTitle .= 'Mini';
                break;
            case Peppermint_Catalog_Helper_Vehicle::BRAND_MOTORRAD_FULL:
                $pageTitle .= 'Bike';
                break;
        }

        return $pageTitle;
    }

    /**
     * Prepare youdrive page message
     *
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getYoudrivePageMessage()
    {
        $transport = 'cars';
        $transportForm = 'drive';

        if ($this->_getCurrentBrand() === Peppermint_Catalog_Helper_Vehicle::BRAND_MOTORRAD_FULL) {
            $transport = 'motorcycles';
            $transportForm = 'ride';
        }

        return $this->__('Sorry, no %s are available at the moment for test %s', $transport, $transportForm);
    }

    /**
     * Return current brand carousel attribute code
     *
     * @return string
     */
    protected function _getBrandCarouselAttribute()
    {
        switch ($this->_getCurrentBrand()) {
            case Peppermint_Catalog_Helper_Vehicle::BRAND_MINI_FULL:
                $field = 'min_model_carousel';
                break;
            case Peppermint_Catalog_Helper_Vehicle::BRAND_MOTORRAD_FULL:
                $field = 'mot_model_carousel';
                break;
            default:
                $field = 'bmw_model_carousel';
                break;
        }

        return $field;
    }

    /**
     * Get product's title
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    public function getTitle(Mage_Catalog_Model_Product $product)
    {
        return $product->getAttributeText($this->_getBrandCarouselAttribute());
    }

    /**
     * Return current brand name
     *
     * @return string
     *
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _getCurrentBrand()
    {
        return str_replace('_store_view', '', Mage::app()->getStore()->getCode());
    }

    /**
     * Get Initial Slide Index For Test Drive Carousel
     *
     * @return integer
     */
    public function getInitialSlideIndex()
    {
        return self::INITIAL_SLIDE_INDEX;
    }

    /*
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        if (is_null($this->_collection)) {
            $modelAttributeName = Mage::helper('rockar_all')->getModelAttribute();
            $this->_collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect([
                    'name',
                    $modelAttributeName,
                    'visible_in',
                    $this->_getBrandCarouselAttribute()
                ])
                ->addAttributeToFilter('entity_id', ['in' => $this->getVisibleCars()])
                ->addAttributeToFilter('visible_in', [
                    'in' => [
                        Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::YOUDRIVE,
                        Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG_AND_YOUDRIVE,
                    ]
                ])
                ->addStoreFilter()->groupByAttribute($modelAttributeName);
        }

        return $this->_collection;
    }

    /**
     * Returns visible configurable products ids
     *
     * @return array
     */
    public function getVisibleCars()
    {
        $helper = Mage::helper('rockar_youdrive');
        $collection = $helper->getConfigurablesCollection(null, true);
        $visibleCars = [];

        foreach ($collection as $configurableProduct) {
            if ($helper->hasYouDriveCars($configurableProduct)) {
                $visibleCars[] = $configurableProduct->getId();
            }
        }

        return $visibleCars;
    }

    /**
     * Get personal details statement from CMS block
     *
     * @return string
     */
    public function getPersonalDetailsStatementBlock()
    {
        return htmlspecialchars(
            $this->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('youdrive_personal_details_statement')
                ->toHtml()
        );
    }

    /**
     * Get online booking disclaimer from CMS block
     *
     * @return string
     */
    public function getOnlineBookingDisclaimerBlock()
    {
        return $this->getLayout()
            ->createBlock('cms/block')
            ->setBlockId('youdrive_online_booking_disclaimer')
            ->toHtml();
    }

    /**
     * Get next step statement from CMS block
     *
     * @return string
     */
    public function getNextStepStatementBlock()
    {
        return htmlspecialchars(
            $this->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('youdrive_next_step_statement')
                ->toHtml()
        );
    }

    /**
     * Get How It Works CMS block
     *
     * @return string
     */
    public function getHowItWorksBlock()
    {
        return htmlspecialchars(
            $this->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('youdrive_howitworks')
                ->toHtml()
        );
    }

    /**
     * Get Terms and Conditions CMS block
     *
     * @return string
     */
    public function getTermsBlock()
    {
        return htmlspecialchars(
            $this->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('youdrive_tncs')
                ->toHtml()
        );
    }

    /**
     * Get 'We will get in touch' CMS block
     *
     * @return string
     */
    public function getInTouchBlock()
    {
        return htmlspecialchars(
            $this->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('youdrive_get_in_touch')
                ->toHtml()
        );
    }

    /**
     * Get Book Another CMS block
     *
     * @return string
     */
    public function getBookAnotherBlock()
    {
        return htmlspecialchars(
            $this->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('youdrive_book_another')
                ->toHtml()
        );
    }

    /**
     * Get customer data
     *
     * @return string
     */
    public function getCustomerData()
    {
        $customer = $this->getCustomer();
        $address = $customer->getPrimaryShippingAddress();
        $addressData = false;

        if ($address) {
            $country = $address->getCountry();
            $addressData = [
                'postcode' => $address->getPostcode(),
                'street' => $address->getStreet()[0],
                'region' => $address->getRegion(),
                'city' => $address->getCity(),
                'country' => $country ? Mage::getModel('directory/country')->loadByCode($country)->getName() : ''
            ];
        }

        return Mage::helper('rockar_all')->jsonEncode([
            'id' => $customer->getId(),
            'email' => $customer->getData('email'),
            'dob' => $this->getDobData(),
            'driving_license_type' => $customer->getData('driving_license_type'),
            'primary_phone_number' => $customer->getData('primary_number'),
            'firstname' => $customer->getData('firstname'),
            'lastname' => $customer->getData('lastname'),
            'prefix' => $customer->getData('prefix'),
            'address' => $addressData
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function _compare_order($a, $b)
    {
        return ($a['title'] > $b['title']);
    }
}
