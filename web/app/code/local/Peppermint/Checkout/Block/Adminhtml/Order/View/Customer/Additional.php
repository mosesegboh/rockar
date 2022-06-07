<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Checkout_Block_Adminhtml_Order_View_Customer_Additional extends Rockar_Checkout_Block_Adminhtml_Order_View_Customer_Additional
{

    /**
     * @var Peppermint_Checkout_Helper_Data
     */
    protected $_checkoutHelper;

    /**
     * @var Rockar_All_Helper_Data
     */
    protected $_helper;

    /**
     * Class constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_checkoutHelper = Mage::helper('peppermint_checkout');
        $this->_rockarHelper = Mage::helper('rockar_all');
        $this->setTemplate('peppermint/order/view/tab/customer/additional/form.phtml');
    }

    /**
     * Init form data.
     *
     * @throws Exception
     * @return $this
     */
    protected function _initForm()
    {
        try {
            $formDataObject = new Varien_Object();

            if ($orderId = $this->getOrder()->getId()) {
                $formDataObject = Mage::getModel('rockar_checkout/order_additional')->load($orderId, 'order_id');
            }

            $orderItemData = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($this->getOrder());
            $financeDataVariables = Mage::helper('rockar_all')->jsonDecode($orderItemData->getData('finance_data_variables'));
            $depositAmount = 0;

            if (isset($financeDataVariables['customer_deposit'])) {
                $depositAmount = $financeDataVariables['customer_deposit'];
            }

            $additionalResidenceData = Mage::getModel('rockar_checkout/order_additional_residence')
                ->load($orderId, 'order_id');
            $additionalEmploymentData = Mage::getModel('rockar_checkout/order_additional_employment')
                ->load($orderId, 'order_id');

            if ($additionalResidenceData->getId()) {
                $formDataObject->setAdditionalResidenceData($additionalResidenceData);
            }

            if ($additionalEmploymentData->getId()) {
                $formDataObject->setAdditionalEmploymentData($additionalEmploymentData);
            }

            $preferredCommunicationMethods = [
                'email' => $formDataObject->getPrefMethodContactEmail(),
                'sms' => $formDataObject->getPrefMethodContactSms(),
                'normal' => $formDataObject->getPrefMethodContactNormal()
            ];
            $preferredCommMethod = $this->_getPreferredCommunicatonMethod($preferredCommunicationMethods);

            $preferredLiableAs = [
                'surety' => $formDataObject->getLiableAsSurety(),
                'gaurantor' => $formDataObject->getLiableAsGaurantor(),
                'co_debtor' => $formDataObject->getLiableAsCoDebtor()
            ];
            $liableAs = $this->_getLiabilityName($preferredLiableAs);

            $formDataObject->setOrderItemData($orderItemData)
                ->setDepositAmount($depositAmount)
                ->setPreferredComminicationMethod($preferredCommMethod)
                ->setLiableAs($liableAs);

            $this->_formData = $formDataObject;

            return $this;
        } catch (Exception $e) {
            Mage::throwException($e);
        }
    }

    /**
     * Gets a list of deposit sources.
     *
     * @return []
     */
    public function getDepositSourceList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getDepositSource());
    }

    /**
     * Gets a list of employments status.
     *
     * @return []
     */
    public function getEmploymentStatusList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getEmploymentStatusTypeJson());
    }

    /**
     * Gets a list of employment industry.
     *
     * @return []
     */
    public function getEmploymentIndustryList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getIndustryTypeJson());
    }

    /**
     * Gets a list of residence status.
     *
     * @return []
     */
    public function getResidenceStatusList()
    {
        return $this->_checkoutHelper->getDfeDataByCategoryName('ResidentialStatus');
    }

    /**
     * Gets a list of accomodation type.
     *
     * @return []
     */
    public function getAccommodationTypeList()
    {
        return $this->_checkoutHelper->getDfeDataByCategoryName('ResidentialOwner');
    }

    /**
     * Get home(Bond) status options.
     *
     * @return []
     */
    public function getBondStatusList()
    {
        return $this->_checkoutHelper->getDfeDataByCategoryName('HomeStatus');
    }

    /**
     * Gets a list of bank account types.
     *
     * @return []
     */
    public function getBankAccountTypeList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getBankAccountTypeJson());
    }

    /**
     * Gets a list of bank names.
     *
     * @return []
     */
    public function getBankNameList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getBankNameJson());
    }

    /**
     * Gets a list of gender type.
     *
     * @return []
     */
    public function getGenderList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getGenderOptionsJson());
    }

    /**
     * Gets a list of ethnic groups.
     *
     * @return []
     */
    public function getEthnicGroupList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getEthnicGroupJson());
    }

    /**
     * Gets a list of preferred languages.
     *
     * @return []
     */
    public function getLanguageList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getPreferredLanguageJson());
    }

    /**
     * Gets a list of marital status.
     *
     * @return []
     */
    public function getMaritalStatusList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getMaritalStatusJson());
    }

    /**
     * Gets a list of marital contract type.
     *
     * @return []
     */
    public function getMaritalContractList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getMarriageTypeJson());
    }

    /**
     * Gets a list of spouse ID types.
     *
     * @return []
     */
    public function getSpouseIdTypesList()
    {
        return $this->_rockarHelper->jsonDecode($this->_checkoutHelper->getSpouseUniqueIdTypeJson());
    }

    /**
     * Get Y/M format.
     *
     * @param  string $totalMonths
     * @return string
     */
    public function getYearsMonths($totalMonths)
    {
        if ($totalMonths > 0) {
            $duration = Mage::helper('peppermint_dfe')->transformToYearMonth($totalMonths);

            return $duration['years'] . ' Y | ' . $duration['months'] . ' M';
        }

        return '-';
    }

    /**
     * Get preferred communication method.
     *
     * @param  array  $data
     * @return string
     */
    protected function _getPreferredCommunicatonMethod($data)
    {
        $preferredTranslationsByType = [
            'email' => $this->__('Email'),
            'sms' => $this->__('SMS'),
            'normal' => $this->__('Post')
        ];

        return $this->_applyTranslations($data, $preferredTranslationsByType);
    }

    /**
     * Get liability type.
     *
     * @param  array  $data
     * @return string
     */
    protected function _getLiabilityName($data)
    {
        $liabilityTranslationsByType = [
            'surety' => $this->__('Surety'),
            'gaurantor' => $this->__('Guarantor'),
            'co_debtor' => $this->__('Co-Debtor')
        ];

        return $this->_applyTranslations($data, $liabilityTranslationsByType);
    }

    /**
     * Produce translated string for the provided data.
     *
     * @param array $data
     * @param array $translations
     * @return string
     */
    protected function _applyTranslations($data, $translations)
    {
        $answer = [];
        foreach ($data as $type => $state) {
            if ($state) {
                $answer[] = $translations[$type];
            }
        }

        return !empty($answer) ? implode(', ', $answer) : $this->__('Not defined');
    }
}
