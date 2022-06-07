<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transunion
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transunion_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NS_API_URL = 'peppermint_transunion/api/url';
    const NS_API_KEY = 'peppermint_transunion/api/key';
    const NS_VEHICLE_DETAILS_HELPER = 'peppermint_transunion/details';
    const NS_VEHICLE_VALUATION_HELPER = 'peppermint_transunion/valuation';
    const NS_PART_EXCHANGE_PROVIDER_DETAILS = 'partexchange/general_options/provider_details';
    const NS_PART_EXCHANGE_PROVIDER_VALUATION = 'partexchange/general_options/provider_valuation';

    /**
     * A list of parameters to use for manual car selection.
     * Order is very important!
     * @var string[]
     */
    protected static $_whiteListedRequestParams = [
        'type' => 'vehicleType',
        'year' => 'registrationYear',
        'make' => 'make',
        'model' => 'model',
        'fuelType' => 'fuelType',
        'transmission' => 'transmission',
        'variant' => 'variant'
    ];

    /**
     * A list of parts to use for assembling vehicle name.
     * @var string[]
     */
    protected static $_nameParts = [
        'vehicleMake',
        'modelRange',
        'vehicleVariant',
        'fuelDescription',
        'transmissionDescription'
    ];

    /**
     * Adds TransUnion as Vehicle details provided.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function registerTransApiDetailsProvider(Varien_Event_Observer $observer)
    {
        $this->_registerGeneric($observer, self::NS_VEHICLE_DETAILS_HELPER);

        return $this;
    }

    /**
     * Adds TransUnion as Vehicle valuation provided.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function registerTransApiValuationProvider(Varien_Event_Observer $observer)
    {
        $this->_registerGeneric($observer, self::NS_VEHICLE_VALUATION_HELPER);

        return $this;
    }

    /**
     * Checks if Transunion is selected as provided partexchange.
     * @return string
     */
    public function isConfiguredAsActive()
    {
        return Mage::getStoreConfig(self::NS_PART_EXCHANGE_PROVIDER_DETAILS) === self::NS_VEHICLE_DETAILS_HELPER
            && Mage::getStoreConfig(self::NS_PART_EXCHANGE_PROVIDER_VALUATION) === self::NS_VEHICLE_VALUATION_HELPER;
    }

    /**
     * Configured api url.
     * @return string
     */
    public function getApiUrl()
    {
        return Mage::getStoreConfig(self::NS_API_URL);
    }

    /**
     * Configured api key.
     * @return string
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig(self::NS_API_KEY);
    }

    /**
     * Cleans all other request parameters.
     * @param mixed $parameters
     * @return string[]
     */
    public function parseManualRequestParams($parameters)
    {
        $filteredParams = [];

        foreach (self::$_whiteListedRequestParams as $localKey => $foreignKey) {
            if (isset($parameters[$foreignKey])) {
                $filteredParams[$localKey] = $parameters[$foreignKey];
            }
        }

        return $filteredParams;
    }

    /**
     * Builds the vehicle name based on parts of the Transunion respose.
     * @param stdClass $record
     * @return string
     */
    public function assembleName($record)
    {
        return implode(
            ' ',
            array_map(
                function ($property) use ($record) {
                    return $record->{$property};
                },
                self::$_nameParts
            )
        );
    }

    /**
     * @param Varien_Event_Observer $observer
     * @param string $helperName
     * @return $this
     */
    protected function _registerGeneric($observer, $helperName)
    {
        $providers = $observer->getProviders();
        $providers->setArray(
            array_merge(
                $providers->getArray(),
                [[
                    'label' => 'Trade In API (TransUnion)',
                    'value' => $helperName
                ]]
            )
        );

        return $this;
    }
}
