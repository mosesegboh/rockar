<?php
/**
 * @category     Peppermint
 * @package      Peppermint\Transunion
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transunion_Helper_Valuation extends Peppermint_Transunion_Helper_Api implements Rockar_PartExchange_Helper_ValuationInterface
{
    /**
     * Performs the valiation request & response mapping
     * @param Varien_Object $partExchange
     * @return $this
     * @throws Exception
     */
    public function getValuation(Varien_Object $partExchange)
    {
        if (!$this->_getEndpointUrl()) {
            Mage::throwException('Please set Endpoint URL to get vehicle valuation.');
        }

        $valuationResult = $partExchange->getUsedValues();
        $partExchange->setData('car_condition', $this->_getRequest()->getParam('carCondition'));
        $partExchange->setData('additional_info', $this->_getRequest()->getParam('additionalInfo'));

        // If mileage hasn't change and we already have valuation value from the first call, we do not need to make
        // another api call since it is using the same endpoint for both valuatation and details call
        if ($partExchange->getHasMileageChange() || !isset($valuationResult['retail'], $valuationResult['clean'])) {
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
            $partExchange->addData(['used_values' => $responseArray]);
        }

        return $this;
    }

    /**
     * Parse response data
     * @param stdClass $response
     * @return mixed[]
     */
    protected function _parseData($response)
    {
        $record = reset($response->transunionResponse->record);

        return [
            'success' => true,
            'errorMessage' => '',
            'retail' => $record->retailPrice,
            'clean' => $record->tradePrice,
            'average' => 0,
            'below' => 0
        ];
    }
}
