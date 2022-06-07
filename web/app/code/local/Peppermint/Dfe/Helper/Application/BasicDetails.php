<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_BasicDetails extends Mage_Core_Helper_Abstract
{
    /**
     * @var locale value
     */
    const LOCALE = 'en_za';

    /**
     * Set data for basic details.
     *
     * @param  integer $orderId
     * @return object
     */
    public function setBasicDetailsData($orderId)
    {
        $data = $this->_prepareData($orderId);

        return (new Peppermint_Dfe_Soap_Application_BasicDetails(
            $data['liable_as_surety'] ?? false,
            $data['liable_as_gaurantor'] ?? false,
            $data['liable_as_co_debtor'] ?? false,
            $data['liable_as_comments'] ?? false,
            $data['spouse_consent'] ?? false
        ))->setTitle($data['title'])
            ->setFirstName($data['first_name'])
            ->setLastName($data['last_name'])
            ->setUniqueID($data['unique_id'])
            ->setUniqueIDType($data['id_type_code'])
            ->setCountryOfResidence($data['country'])
            ->setDateOfBirth($data['dob'])
            ->setPreferredLanguage($data['preferred_language'])
            ->setMoExclusiveDealsBMW(false)
            ->setMoShareDetailsBMW(false)
            ->setMoTelemarketingBMW(false)
            ->setRace($data['race'])
            ->setHomeTel($data['home_tel'])
            ->setCellphone($data['cellphone'])
            ->setOfficeNumber(null)
            ->setEmailAddress($data['email'])
            ->setMaritalStatus($data['marital_status'])
            ->setMarriageType($data['marriage_type'])
            ->setGender($data['gender'])
            ->setKinName($data['kin_name'])
            ->setKinTel($data['kin_tel'])
            ->setSpouseFirstName($data['spouse_first_name'])
            ->setSpouseLastName($data['spouse_last_name'])
            ->setSpouseIDNo($data['spouse_id_no'])
            ->setSpouseIDType($data['spouse_id_type'])
            ->setSpouseCellNumber($data['spouse_cell_number'])
            ->setSpouseEmail($data['spouse_email'])
            ->setLiableAsSurety($data['liable_as_surety'])
            ->setLiableAsGaurantor($data['liable_as_gaurantor'])
            ->setLiableAsCoDebtor($data['liable_as_co_debtor'])
            ->setLiableAsComments($data['liable_as_comments'])
            ->setSpouseConsent($data['spouse_consent']);
    }

    /**
     * Prepare collection data by orderID.
     *
     * @param  integer $orderId
     * @return []
     */
    protected function _prepareData($orderId)
    {
        $data = Mage::getModel('rockar_checkout/order_additional')->load($orderId, 'order_id')
            ->getData();
        $order = Mage::getModel('sales/order')->load($orderId);
        $billingAddress = $order->getBillingAddress();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $referenceModel = Mage::getModel('peppermint_dfe/reference_code');
        $idTypeCode = $referenceModel->load($this->_getAttributeNonStore($customer, 'south_african_document_type'), 'description')
            ->getCode();
        $title = $referenceModel->load(preg_replace('/[^A-Za-z]/', '', $order->getCustomerPrefix()), 'description')
            ->getCode();
        $country = $this->_getCustomerCountryCode($referenceModel, $billingAddress);

        return [
            'preferred_language' => $data['preferred_language'] ?? '',
            'race' => $data['race'] ?? '',
            'home_tel' => $data['home_tel'] ?? '',
            'kin_name' => $data['kin_name'] ?? '',
            'kin_tel' => $data['kin_tel'] ?? '',
            'spouse_first_name' => $data['spouse_first_name'] ?? '',
            'spouse_last_name' => $data['spouse_last_name'] ?? '',
            'spouse_id_no' => $data['spouse_id_no'] ?? '',
            'spouse_id_type' => $data['spouse_id_type'] ?? '',
            'spouse_cell_number' => $data['spouse_cell_number'] ?? '',
            'spouse_email' => $data['spouse_email'] ?? '',
            'spouse_consent' => $data['spouse_consent'] ?? '',
            'liable_as_surety' => $data['liable_as_surety'] ?? '',
            'liable_as_gaurantor' => $data['liable_as_gaurantor'] ?? '',
            'liable_as_co_debtor' => $data['liable_as_co_debtor'] ?? '',
            'liable_as_comments' => $data['liable_as_comments'] ?? '',
            'marital_status' => $data['marital_status'] ?? '',
            'marriage_type' => $data['marriage_type'] ?? '',
            'unique_id' => $customer->getData('south_african_id_number') ?? '',
            'first_name' => $order->getCustomerFirstname() ?? '',
            'last_name' => $order->getCustomerLastname() ?? '',
            'id_type_code' => $idTypeCode ?? '',
            'dob' => DateTime::createFromFormat('Y-m-d H:i:s', $order->getCustomerDob())->format('Ymd') ?? 0,
            'country' => $country,
            'cellphone' => $billingAddress->getTelephone() ?? '',
            'title' => $title ?? 'TRLMR',
            'email' => $order->getCustomerEmail() ?? '',
            'gender' => $data['gender'] ?? 'GRNAP'
        ];
    }

    /**
     * Get Attribute value base on store.
     *
     * @param $model
     * @param $attribute
     * @return mixed
     */
    protected function _getAttributeNonStore($model, $attribute)
    {
        return $model->getResource()
            ->getAttribute($attribute)
            ->getFrontend()
            ->getValue($model);
    }

    /**
     * Get Customer Country DFE reference code
     *
     * @param Peppermint_Dfe_Reference_Code $referenceModel
     * @param Mage_Sales_Model_Quote_Address $billingAddress
     *
     * @return string|null
     */
    protected function _getCustomerCountryCode($referenceModel, $billingAddress)
    {
        $locale = new Zend_Locale(self::LOCALE);
        $referenceModel->unsetData();

        return $referenceModel->load(
            $locale->getTranslation($billingAddress->getCountryId(), 'country', $locale),
             'description'
         )->getCode();
    }
}
