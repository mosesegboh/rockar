<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Transunion
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Transunion_Helper_Details extends Peppermint_Transunion_Helper_Api implements Rockar_PartExchange_Helper_VehicleDetailsInterface
{
    /** overrides on template paths */
    public function getCheckoutTemplate()
    {
        return 'rockar/transunion/checkout.phtml';
    }

    /** overrides on template paths */
    public function getCustomerTemplate()
    {
        return 'rockar/transunion/customer.phtml';
    }

    /** overrides on template paths */
    public function getCatalogTemplate()
    {
        return 'rockar/transunion/catalog.phtml';
    }

    /** overrides on template paths */
    public function getCatalogWrapperTemplate()
    {
        return 'rockar/transunion/catalog_wrapper.phtml';
    }

    /** overrides on template paths */
    public function getSalesOrderTemplate()
    {
        return 'rockar/transunion/sales_order.phtml';
    }

    /**
     * Performs get request on vehicle types endpoint and prepares the data
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    public function getManualSelectionTypeList()
    {
        return $this->_getBasicLookupList([], 'code', 'description', 'vehtypeList', 'lookupType');
    }

    /**
     * Performs get request on years endpoint and prepares the data
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    public function getManualSelectionYearList($transUnion)
    {
        return $this->_getBasicLookupList($transUnion, 'description', 'description', 'lookupList', 'lookup');
    }

    /**
     * Performs get request on makes endpoint and prepares the data
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    public function getManualSelectionMakeList($transUnion)
    {
        return $this->_getBasicLookupList($transUnion, 'description', 'description', 'lookupList', 'lookup');
    }

    /**
     * Performs get request on models endpoint and prepares the data
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    public function getManualSelectionModelList($transUnion)
    {
        return $this->_getBasicLookupList($transUnion, 'description', 'description', 'lookupList', 'lookup');
    }

    /**
     * Performs get request on fuel types endpoint and prepares the data
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    public function getManualSelectionFuelTypeList($transUnion)
    {
        return $this->_getBasicLookupList($transUnion, 'code', 'description', 'lookupList', 'lookupType');
    }

    /**
     * Performs get request on transmission types endpoint and prepares the data
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    public function getManualSelectionTransmissionList($transUnion)
    {
        return $this->_getBasicLookupList($transUnion, 'code', 'description', 'lookupList', 'lookupType');
    }

    /**
     * Performs get request on variants endpoint and prepares the data
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    public function getManualSelectionVariantList($transUnion)
    {
        return $this->_getBasicLookupList($transUnion, 'mmCode', 'variantDescription', 'variantList', 'variant');
    }

    /**
     * Performs get request on makes endpoint and prepares the data
     * @return mixed[]
     */
    public function getMakeList()
    {
        $returnData = [
            'success' => false,
            'errorMessage' => '',
            'result' => []
        ];
        try {
            $response = $this->_doGetRequest($this->_getEndpointUrl() . self::API_MAKE_SUFFIX);
            $returnData = array_merge($returnData, [
                'success' => true,
                'result' => array_map(function ($make) {
                    return [
                        'ID' => $make->code,
                        'MakeDesc' => $make->description
                    ];
                }, $response->vehmakeList->lookup)
            ]);
        } catch (Exception $e) {
            $returnData['errorMessage'] = $e->getMessage();
            Mage::log('Transunion getMakeList failed with exception: ' . $e->getTraceAsString(), Zend_Log::ERR);
        }

        return $returnData;
    }

    /**
     * Performs get request on models endpoint and prepares the data
     * @return mixed[]
     */
    public function getModelList($brandId)
    {
        $returnData = [
            'success' => false,
            'errorMessage' => '',
            'result' => []
        ];
        $makeList = $this->getMakeList();
        $makes = $makeList['result'];
        if (!empty($makes) && $makeList['success']) {
            try {
                $response = $this->_doGetRequest(
                    $this->_getEndpointUrl() . self::API_MODEL_SUFFIX . '/'
                        . rawurlencode($makes[array_search($brandId, array_column($makes, 'ID'))]['MakeDesc'])
                );
                $returnData = array_merge($returnData, [
                    'success' => true,
                    'result' => array_map(function ($model) {
                        return [
                            'ID' => $model->model,
                            'Model' => $model->model . ' (' . $model->vehicleTypeDescription . ')',
                            'FromYear' => '',
                            'ToYear' => ''
                        ];
                    }, $response->vehmodelList->lookup)
                ]);
            } catch (Exception $e) {
                $returnData['errorMessage'] = $e->getMessage();
                Mage::log('Transunion getModelList failed with exception: ' . $e->getTraceAsString(), Zend_Log::ERR);
            }
        } else {
            $returnData['errorMessage'] = $makeList['errorMessage'];
        }

        return $returnData;
    }

    /**
     * Get list of model ranges for the given manufacturer
     *
     * @param $makeId
     * @return array
     */
    public function getRangeList($makeId)
    {
        Mage::throwException('This method is not implemented and will never be');
    }

    /**
     * Get list of model derivatives for the given model
     *
     * @param $modelId
     * @return array
     */
    public function getDerivativeList($modelId)
    {
        Mage::throwException('This method is not implemented and will never be');
    }


    /**
     * Get list of available colours for the given CAP ID
     *
     * @return array
     */
    public function getColourList()
    {
        Mage::throwException('This method is not implemented and will never be');
    }

    /**
     * Returns vehicle details
     *
     * @param Varien_Object $object
     * @return $this
     */
    public function getVehicleDetails(Varien_Object $partExchange)
    {
        if (!$this->_getEndpointUrl()) {
            Mage::throwException('Please set Endpoint URL to get vehicle details.');
        }
        $manualSelectedData = $partExchange->getTransUnion();
        $requestPayload = $partExchange->getIsManualSelection()
            ? array_merge($this->_getBlankRequestPayload(), [
                'vehicleMMCode' => $manualSelectedData['variant'],
                'vehicleMileage' => $partExchange->getMileage(),
                'vehicleManufactureYear' => $manualSelectedData['year']
            ]) : array_merge($this->_getBlankRequestPayload(), [
                'vehicleRegistrationNumber' => $partExchange->getVrm(),
                'vehicleMileage' => $partExchange->getMileage(),
                'vehicleManufactureYear' => $partExchange->getPlateYear(),
                'vehicleMMCode' => $partExchange->getMmCode()
            ]);

        $response = $this->_requestDetails($requestPayload);

        $responseArray = $this->_parseData($response, $partExchange);
        $partExchange->addData($responseArray);

        return $this;
    }

    /**
     * Simply maps standard lookup response into data for dropdowns
     *
     * @param mixed[] $transUnion
     * @param string $idProp
     * @param string $descProp
     * @param string $firstLevelProp
     * @param string $secondLevelProp
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    protected function _getBasicLookupList($transUnion = [], $idProp, $descProp, $firstLevelProp, $secondLevelProp)
    {
        return $this->_mapLookupList($this->_requestNestedData($transUnion), $idProp, $descProp, $firstLevelProp, $secondLevelProp);
    }

    /**
     * Simply maps standard lookup response into data for dropdowns
     *
     * @param stdClass $response
     * @param string $idProp
     * @param string $descProp
     * @param string $firstLevelProp
     * @param string $secondLevelProp
     * @return mixed[]
     * @throws Mage_Core_Exception
     */
    protected function _mapLookupList($response, $idProp, $descProp, $firstLevelProp, $secondLevelProp)
    {
        return array_map(function ($type) use ($idProp, $descProp) {
            return [
                'id' => $type->{$idProp},
                'desc' => $type->{$descProp}
            ];
        }, $response->{$firstLevelProp}->{$secondLevelProp});
    }

    /**
     * Formats response into proper PX structure
     *
     * @param $data
     * @param Varien_Object $partExchange
     * @todo This mapping is done as far as possible according to cap network module dataset
     *       This method requires refactoring including the mapping way, but for now it's easier to read this code
     * @return []
     */
    protected function _parseData($response, $partExchange)
    {
        $record = reset($response->transunionResponse->record);
        $vehicleName = Mage::helper('peppermint_transunion')->assembleName($record);
        $introducedDate = \DateTime::createFromFormat('Y-m-d', $record->introductionDate);
        $discontinuedDate = \DateTime::createFromFormat('Y-m-d', $record->discontinuedDate);
        $plateYear = $this->_getPlateYear($partExchange);

        $result = [];
        $result['success'] = !$response->etReturn->error ? 'true' : 'false';
        $result['timestamp'] = time();
        $result['lookup_vrm'] = $partExchange->getVrm();
        $result['plate_year'] = $plateYear;

        $result['dvla'] = [];
        $result['dvla']['registration_date'] = ''; // not mapped
        $result['dvla']['colour'] = ''; // not mapped
        $result['cap'] = [];
        $result['cap']['capid'] = $record->vehicleCode;
        $result['cap']['manufacturer'] = $record->vehicleMake;
        $result['cap']['manufacturer_code'] = $record->makeCode;
        $result['cap']['derivative'] = $record->vehicleVariant;
        $result['cap']['range'] = $record->modelRange;
        $result['cap']['model'] = $record->vehicleModel;
        $result['cap']['model_dates'] = '(' . $record->introductionDate . ' - ' . $record->discontinuedDate . ')';
        $result['cap']['fueltype'] = $record->fuelDescription;
        $result['cap']['plate'] = implode('_', [$partExchange->getVrm(), $plateYear]); // not mapped
        $result['cap']['plate_year'] = $plateYear;
        $result['cap']['plate_month'] = 1; // not mapped
        $result['cap']['derivative_spec'] = $vehicleName;
        $result['cap']['first_date_of_registration'] = '';
        $result['cap']['make_name'] = $record->vehicleMake;
        $result['cap']['make_code'] = $record->makeCode;
        $result['cap']['original_valuation'] = $record->tradePrice;
        $result['cap']['vin'] = '';

        $result['alternatives'] = []; // not applicable

        $result['derivative'] = [];
        $result['derivative']['expected_mileage'] = (date('Y') - date('Y')) * 10000; // unknown default multiplier
        $result['derivative']['default_mileage_multiplier'] = 10000;  // unknown default multiplier
        $result['derivative']['current_year'] = date('Y');

        $result['cap_extended'] = [];
        $result['cap_extended']['emission'] = $record->co2;
        $result['cap_extended']['transmission'] = $record->transmissionDescription;
        $result['cap_extended']['ec_combined_mpg'] = ''; // not mapped
        $result['cap_extended']['ec_extra_urban_mpg'] = ''; // not mapped
        $result['cap_extended']['ec_urban_mpg'] = ''; // not mapped
        $result['cap_extended']['engine_power_bhp'] = $record->kilowatts;
        $result['cap_extended']['insurance_group'] = ''; // not mapped
        $result['cap_extended']['st_manufacturers_warranty'] = '';  // not mapped
        $result['cap_extended']['st_manufacturers_warranty_mil'] = '';  // not mapped
        $result['cap_extended']['ncap_adult_occupant'] = '';  // not mapped
        $result['cap_extended']['ncap_child_occupant'] = '';  // not mapped
        $result['cap_extended']['ncap_overall_rating'] = '';  // not mapped
        $result['cap_extended']['ncap_pedestrian'] = '';  // not mapped
        $result['cap_extended']['N_0_to_62_mph_secs'] = '';  // not mapped
        $result['cap_extended']['N_0_to_60_mph_secs'] = '';  // not mapped
        $result['cap_extended']['top_speed'] = '';  // not mapped
        $result['cap_extended']['space_saver'] = '';  // not mapped
        $result['cap_extended']['tyre_size_spare'] = '';  // not mapped
        $result['cap_extended']['height'] = $record->vehicleHeight;
        $result['cap_extended']['length'] = $record->vehicleLength;
        $result['cap_extended']['wheelbase'] = $record->wheelBase;
        $result['cap_extended']['width'] = $record->vehicleWidth;
        $result['cap_extended']['width_including_mirrors'] = $record->vehicleWidth;
        $result['cap_extended']['fuel_tank_capacity_in_litres'] = $record->fuelTankSize;
        $result['cap_extended']['luggage_capacity_seats_down'] = ''; // not mapped
        $result['cap_extended']['luggage_capacity_seats_up'] = ''; // not mapped
        $result['cap_extended']['max_towing_weight_braked'] = ''; // not mapped
        $result['cap_extended']['max_towing_weight_unbraked'] = ''; // not mapped
        $result['cap_extended']['minimum_kerbweight'] = ''; // not mapped
        $result['cap_extended']['seats'] = $record->seats;
        $result['cap_extended']['corrosion_perf_guarantee_yrs'] = ''; // not mapped
        $result['cap_extended']['man_paintwork_guarantee_yrs'] = ''; // not mapped
        $result['cap_extended']['badge_engine_cc'] = ''; // not mapped
        $result['cap_extended']['coin_series'] = ''; // not mapped
        $result['cap_extended']['service_interval_mileage'] = ''; // not mapped
        $result['cap_extended']['service_interval_frequency'] = ''; // not mapped
        $result['cap_extended']['manufacturer'] = $record->vehicleMake;
        $result['cap_extended']['range'] = $record->modelRange;
        $result['cap_extended']['model'] = $record->vehicleModel;
        $result['cap_extended']['derivative'] = $record->vehicleVariant;
        $result['cap_extended']['bodystyle'] = $record->bodyType;
        $result['cap_extended']['doors'] = $record->doors;
        $result['cap_extended']['introduced_year'] = $introducedDate ? $introducedDate->format('Y') : '';
        $result['cap_extended']['introduced_month'] = $introducedDate ? $introducedDate->format('m') : '';
        $result['cap_extended']['discontinued_year'] = $discontinuedDate ? $discontinuedDate->format('Y') : '';
        $result['cap_extended']['discontinued_month'] = $discontinuedDate ? $discontinuedDate->format('m') : '';
        $result['cap_extended']['fueltype'] = $record->fuelDescription;
        $result['cap_extended']['warranty'] = ''; // not mapped
        $result['cap_extended']['ncap_safety_assist'] = ''; // not mapped
        $result['cap_extended']['bluetooth'] = false; // not mapped
        $result['cap_extended']['cruise_control'] = false; // not mapped
        $result['cap_extended']['parking_sensors'] = false; // not mapped
        $result['cap_extended']['dab_digital_radio'] = false; // not mapped
        $result['cap_extended']['usb_connection'] = false; // not mapped
        $result['cap_extended']['leather_upholstry'] = false; // not mapped
        $result['cap_extended']['climate_control'] = false; // not mapped
        $result['cap_extended']['heated_seats'] = false; // not mapped
        $result['cap_extended']['satellite_navigation'] = false; // not mapped
        $result['cap_extended']['iso_fix'] = false; // not mapped
        $result['cap_extended']['quickclear_heated_windscreen'] = false; // not mapped
        $result['cap_extended']['xenon'] = false; // not mapped
        $result['cap_extended']['miles_per_gallon'] = ''; // not mapped
        $result['cap_extended']['luggage_capacity'] = ''; // not mapped
        $result['cap_extended']['road_fund_licence'] = ''; // not mapped
        $result['cap_extended']['road_fund_licence_cost'] = ''; // not mapped
        $result['cap_extended']['product_name'] = $vehicleName;

        if (isset($record->tradePrice, $record->retailPrice)) {
            $result['used_values'] = [
                'success' => true,
                'errorMessage' => '',
                'retail' => $record->retailPrice,
                'clean' => $record->tradePrice,
                'average' => 0,
                'below' => 0
            ];
        }

        return $result;
    }

    /**
     * Check if manual entry form is enabled
     *
     * @return bool
     */
    public function getManualEntryEnabled()
    {
        return false;
    }

    /**
     * Get plate year from px Object, depending on px type.
     *
     * @param object $pxData
     * @return string
     */
    protected function _getPlateYear(Varien_Object $pxData)
    {
        $plateYear = $pxData->getPlateYear();

        return $pxData->getIsManualSelection() ? ($pxData->getTransUnion()['year'] ?? $plateYear) : $plateYear;
    }
}
