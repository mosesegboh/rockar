<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transunion
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar2_PartExchange') . DS . 'DetailsController.php';

class Peppermint_Transunion_DetailsController extends Rockar2_PartExchange_DetailsController
{
    /**
     * Base action for getting details for a vehicle.
     * @return void
     */
    public function indexAction()
    {
        $vrm = $this->_getVrm();
        $pxStep = $this->_getPXStep();

        /** @var Rockar_PartExchange_Helper_Data $helper */
        $helper = Mage::helper('rockar_partexchange');
        $savedPartExchange = $helper->loadPartExchangeFromSession();

        $partExchange = new Varien_Object();
        $baseData = [
            'step', $pxStep,
            'vrm' => $vrm,
            'mileage' => $this->_getMileage(),
            'plate_year' => $this->_getRegistrationYear(),
            'is_manual_selection' => false,
            'mm_code' => $this->_getMmCode()
        ];
        $partExchange->addData($baseData);

        try {
            $helper->getVehicleDetails($partExchange);

            if ($savedPartExchange->getVrm() === $vrm) {
                $partExchange->addData(array_merge($savedPartExchange->getData(), $baseData));
            } else {
                $helper->clearPartExchangeFromSession();
            }
            $result = $partExchange->getData();
        } catch (Rockar_PartExchange_Exception_VehicleDetailsNotFound $e) {
            $this->setResponseHttpStatusCodeBadRequest();
            $result['errorMessage'] = $this->__('Not found');
            $helper->clearPartExchangeFromSession();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->setResponseHttpStatusCodeBadRequest();
            $result['errorMessage'] = $this->__('There has been an issue processing your request');
            $helper->clearPartExchangeFromSession();
        }

        $savedRunningCostsPX = $helper->loadPartExchangeFromSession(Rockar_PartExchange_Helper_Data::RUNNING_COSTS_PART_EXCHANGE_KEY)
            ->addData(['step' => $pxStep]);
        $helper->savePartExchangeToSession($savedRunningCostsPX, Rockar_PartExchange_Helper_Data::RUNNING_COSTS_PART_EXCHANGE_KEY);

        $helper->savePartExchangeToSession($partExchange);

        $this->sendJson($result);
    }

    /**
     * Get M&M code value
     *
     * @return string|null
     */
    protected function _getMmCode()
    {
        return preg_replace('/\D/', '', $this->getRequest()->getParam('capId')) ?: null;
    }

    /**
     * Manual select of the car.
     * @return void
     */
    public function customCarAction()
    {
        $data = Mage::helper('peppermint_transunion')->parseManualRequestParams($this->getRequest()->getParams());
        $helper = Mage::helper('rockar_partexchange');
        $partExchange = $helper->loadPartExchangeFromSession()->addData([
            'trans_union' => $data,
            'is_manual_selection' => true
        ]);

        try {
            $helper->getVehicleDetails($partExchange);
            $result = $partExchange->getData();
        } catch (Rockar_PartExchange_Exception_VehicleDetailsNotFound $e) {
            $this->setResponseHttpStatusCodeBadRequest();
            $result['errorMessage'] = $this->__('Not found');
            $helper->clearPartExchangeFromSession();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->setResponseHttpStatusCodeBadRequest();
            $result['errorMessage'] = $this->__('There has been an issue processing your request');
            $helper->clearPartExchangeFromSession();
        }

        $savedRunningCostsPX = $helper->loadPartExchangeFromSession(Rockar_PartExchange_Helper_Data::RUNNING_COSTS_PART_EXCHANGE_KEY)
            ->addData(['step' => $this->_getPXStep()]);
        $helper->savePartExchangeToSession($savedRunningCostsPX, Rockar_PartExchange_Helper_Data::RUNNING_COSTS_PART_EXCHANGE_KEY);

        $helper->savePartExchangeToSession($partExchange);

        $this->sendJson($result);
    }

    /**
     * verifyRequest.
     *
     * @param [] $responseArray
     * @return boolean
     */
    protected function _verifyRequest($responseArray = [])
    {
        if ($this->getRequest()->getActionName() !== 'index') {
            return true;
        }
        $registrationYear = $this->_getRegistrationYear();

        if (!Zend_Validate::is($registrationYear, 'NotEmpty')) {
            $responseArray['errorMessage'] = $this->__('Registration Year is missing');
        }

        if (
            !Zend_Validate::is($registrationYear, 'Date', ['format' => 'Y'])
            || !Zend_Validate::is($registrationYear, 'Between', [
                'min' => 1885,
                'max' => date('Y'),
                'inclusive' => true]
            )
        ) {
            $responseArray['errorMessage'] = $this->__('Invalid year number');
        }

        return parent::_verifyRequest($responseArray);
    }

    /**
     * Gets the registration year from request.
     * @return string
     */
    protected function _getRegistrationYear()
    {
        return $this->getRequest()->getParam('registrationYear');
    }

    /**
     * Prints the list of vehicle types got from TransUnion.
     * @return void
     */
    public function getTypeAction()
    {
        $this->_manualDataGenericAction('getManualSelectionTypeList');
    }

    /**
     * Prints the list of years got from TransUnion.
     * @return void
     */
    public function getYearAction()
    {
        $this->_manualDataGenericAction('getManualSelectionYearList');
    }

    /**
     * Prints the list of makes got from TransUnion.
     * @return void
     */
    public function getMakeAction()
    {
        $this->_manualDataGenericAction('getManualSelectionMakeList');
    }

    /**
     * Prints the list of models got from TransUnion.
     * @return void
     */
    public function getModelAction()
    {
        $this->_manualDataGenericAction('getManualSelectionModelList');
    }

    /**
     * Prints the list of fuel types got from TransUnion.
     * @return void
     */
    public function getFuelTypeAction()
    {
        $this->_manualDataGenericAction('getManualSelectionFuelTypeList');
    }

    /**
     * Prints the list of transmission types got from TransUnion.
     * @return void
     */
    public function getTransmissionAction()
    {
        $this->_manualDataGenericAction('getManualSelectionTransmissionList');
    }

    /**
     * Prints the list of variants got from TransUnion.
     * @return void
     */
    public function getVariantAction()
    {
        $this->_manualDataGenericAction('getManualSelectionVariantList');
    }

    /**
     * Performs specific helper calls for each step.
     * @param mixed $helperMethod
     * @return void
     */
    protected function _manualDataGenericAction($helperMethod)
    {
        $data = Mage::helper('peppermint_transunion')->parseManualRequestParams($this->getRequest()->getParams());
        $this->_persistToSession($data);

        try {
            $result = Mage::helper('rockar_partexchange/serviceFactory')->getVehicleDetailsHelper()
                ->{$helperMethod}($data);
        } catch (Exception $e) {
            $this->setResponseHttpStatusCodeBadRequest();
            $result = ['errorMessage' => $e->getMessage()];
        }

        $this->sendJson($result);
    }

    /**
     * Saves to session the provided manual selection parameters.
     * @param mixed $data
     * @return $this
     */
    protected function _persistToSession($data)
    {
        $helper = Mage::helper('rockar_partexchange');
        $partExchange = $helper->loadPartExchangeFromSession()
            ->addData(['trans_union' => $data]);
        $helper->savePartExchangeToSession($partExchange);

        return $this;
    }

    /**
     * Overriding parent action with an exception.
     * @return void
     */
    public function getRangeAction()
    {
        $this->setResponseHttpStatusCodeBadRequest();
        $this->sendJson(['errorMessage' => $this->__('Not implemented')]);
    }

    /**
     * Overriding parent action with an exception.
     * @return void
     */
    public function getColourAction()
    {
        $this->setResponseHttpStatusCodeBadRequest();
        $this->sendJson(['errorMessage' => $this->__('Not implemented')]);
    }

    /**
     * Overriding parent action with an exception.
     * @return void
     */
    public function getDerivativeAction()
    {
        $this->setResponseHttpStatusCodeBadRequest();
        $this->sendJson(['errorMessage' => $this->__('Not implemented')]);
    }
}
