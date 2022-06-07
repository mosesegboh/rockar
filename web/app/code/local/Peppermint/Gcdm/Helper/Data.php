<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Gcdm
 * @author       Stefan Lucaci <lucacistefan.alexandru@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Gcdm_Helper_Data extends Mage_Core_Helper_Abstract
{
    const GCDM_DATE_FORMAT = 'Y-m-d';
    const GCDM_PRIVATE = 'PRIVATE';
    const GCDM_BUSINESS = 'BUSINESS';
    const GCDM_COMMUNICATION_PRIVATE = 'MOB';
    const GCDM_COMMUNICATION_BUSINESS = 'TEL';
    const GCDM_LATEST_POLICY_ACCEPTED_SESSION_KEY = 'latest_policy_accepted';
    const GCDM_LATEST_POLICY_SESSION_KEY = 'latest_policy_data';
    const GCDM_LATEST_POLICY_CHECKBOXES_SESSION_KEY = 'latest_policy_checkboxes';
    const GCDM_POLICY_MANDATORY = 'MANDATORY';
    const GCDM_POLICY_TOUCHPOINT = 'DEALER_WEB_SITE_ZA';
    const GCDM_POLICY_ACCEPTED = 'ACCEPT';
    const GCDM_POLICY_REJECTED = 'REJECT';
    const GCDM_CUSTOMER_MANDATORY_DETAILS_SESSION_KEY = 'customer_mandatory_details';

    /**
     * @var string[]
     */
    private static $_prefixMapping = [
        'SAL_MISS' => 'Miss',
        'SAL_MS' => 'Ms.',
        'SAL_MR' => 'Mr.',
        'SAL_MRS' => 'Mrs.',
        'SAL_DR' => 'Dr.'
    ];

    protected static $_documentTypeMapping = [
        'Passport Number' => 'PER_IDE_PASSPORT_ID',
        'SA ID Number' => 'PER_IDE_SOUTH_AFRICA_NATIONAL_ID'
    ];

    /**
     * @var boolean
     */
    private $_suppressObserver = false;

    /**
     * Sets the flag used for observer suppression
     * @param boolean $suppressObserver
     * @return Peppermint_Gcdm_Helper_Data
     */
    public function setSuppressObserver($suppressObserver)
    {
        $this->_suppressObserver = $suppressObserver;

        return $this;
    }

    /**
     * Get the flag used for observer suppression
     * @return boolean
     */
    public function getSuppressObserver()
    {
        return $this->_suppressObserver;
    }

    /**
     * Extract Mage Customer compatible array from GCDM data object
     *
     * @param stdClass $gcdmCustomerInfo
     * @return []
     */
    public function extractCustomerData($gcdmCustomerInfo)
    {
        $businessPartner = $gcdmCustomerInfo->businessPartner;

        return [
            'email' => $gcdmCustomerInfo->userAccount->mail,
            'prefix' => self::$_prefixMapping[$businessPartner->salutation],
            'firstname' => $businessPartner->givenName,
            'middlename' => $businessPartner->initialsName,
            'lastname' => $businessPartner->surname,
            'dob' => $businessPartner->birthday ? \DateTime::createFromFormat(self::GCDM_DATE_FORMAT, $businessPartner->birthday)->format('d/m/Y') : null,
            'primary_number' => $this->extractGcdmPhoneData($businessPartner)['personal']['value'] ?? null,
            'south_african_document_type' => $this->convertDocumentTypeFromGcdm(
                $businessPartner->profile->personalIdentifications->personalIdentifications[0]->type
            ),
            'south_african_id_number' => $businessPartner->profile->personalIdentifications->personalIdentifications[0]->id
        ];
    }

    /**
     * Extract Mage Customer address compatible array from GCDM data object
     *
     * @param stdClass $gcdmCustomerInfo
     * @param [] $customerProfileData
     *
     * @return []
     */
    public function extractCustomerAddressData($gcdmCustomerInfo, $customerProfileData)
    {
        $businessPartner = $gcdmCustomerInfo->businessPartner;
        $addresses = $businessPartner->addresses->addresses;

        $personalAddress = null;
        if (is_array($addresses)) {
            foreach ($addresses as $address) {
                if ($address->addressGUID == $customerProfileData['main_address_guid']) {
                    $personalAddress = $address;
                }
            }
        }

        $cusPhoneData = $this->extractGcdmPhoneData($businessPartner);

        return [
            'prefix' => self::$_prefixMapping[$businessPartner->salutation],
            'firstname' => $businessPartner->givenName,
            'middlename' => $businessPartner->initialsName,
            'lastname' => $businessPartner->surname,
            'street' => [
                $personalAddress->street,
                $personalAddress->strSuppl1
            ],
            'city' => $personalAddress->districtName,
            'country_id' => $personalAddress->country,
            'region' => $personalAddress->city,
            'postcode' => $personalAddress->postalCode,
            'telephone' => $cusPhoneData['personal']['value'] ?? null
        ];
    }

    /**
     * Extract Mage Customer profile compatible array from GCDM data object
     *
     * @param stdClass $gcdmCustomerInfo
     * @return []
     */
    public function extractCustomerProfileData($gcdmCustomerInfo)
    {
        $guids = [
            'main_address_guid' => '',
            'business_address_guid' => '',
            'vat_number_guid' => '',
            'main_phone_guid'=> '',
            'business_phone_guid'=> ''
        ];
        $businessPartner = $gcdmCustomerInfo->businessPartner;

        $addresses = $businessPartner->addresses->addresses;
        if (is_array($addresses)) {
            foreach ($addresses as $address) {
                if ($address->addressStatus == self::GCDM_PRIVATE) {
                    $guids['main_address_guid'] = $address->addressGUID;
                } else {
                    $guids['business_address_guid'] = $address->addressGUID;
                }
            }
        }

        $cusPhoneData = $this->extractGcdmPhoneData($businessPartner);

        if (isset($cusPhoneData['personal']['guid'])) {
            $guids['main_phone_guid'] = $cusPhoneData['personal']['guid'];
        }

        if (isset($cusPhoneData['business']['guid'])) {
            $guids['business_phone_guid'] = $cusPhoneData['business']['guid'];
        }

        if (is_array($businessPartner->taxDatas)) {
            $guids['vat_number_guid'] = $businessPartner->taxDatas[0]->taxDataGUID;
        }

        return $guids;
    }

    /**
     * Prepares the address dataset as GCDM expects it
     *
     * @param [] $gcdmData
     * @param [] $postData
     * @return []
     */
    private function _prepareGcdmAddress($gcdmData, $postData)
    {
        $customerType = $postData['customer_type'];
        $addressStatus = ($customerType == 'business' ? self::GCDM_BUSINESS : self::GCDM_PRIVATE);
        $addressGUID = ($customerType == 'business' ? $gcdmData['business_address_guid'] : $gcdmData['main_address_guid']);
        $address = [
            'addresses' => [[
                'street' => $postData['address']['street'][0],
                'strSuppl1' => $postData['address']['street'][1],
                'postalCode' => $postData['address']['postcode'],
                'city' => $postData['address']['region'],
                'country' => 'ZA',
                'districtName' => $postData['address']['city'],
                'addressStatus' => $addressStatus,
                'preferred' => true
            ]]
        ];

        if (!empty($addressGUID)) {
            $address['addresses'][0]['addressGUID'] = $addressGUID;
        }

        return $address;
    }

    /**
     * Prepares the dataset as GCDM expects it
     * @param [] $gcdmData
     * @param [] $postData
     * @return []|false
     */
    public function prepareDataForGcdm($gcdmData, $postData)
    {
        $flippedPrefixMapping = array_flip(self::$_prefixMapping);

        $gcdmDataArray = [
            'businessPartner' => [
                'gcid' => $gcdmData['gcid'],
                'partnerCategory' => 'PERSON',
                'surname' => $postData['customer']['lastname'],
                'givenName' => $postData['customer']['firstname'],
                'salutation' => $flippedPrefixMapping[$postData['customer']['prefix']],
                'communications' => [
                    'communicationPhones' => [[
                        'communicationType' => self::GCDM_COMMUNICATION_PRIVATE,
                        'communicationStatus' => self::GCDM_PRIVATE,
                        'preferred' => true,
                        'value' => $postData['customer']['primary_number']
                    ]]
                ]
            ]
        ];

        // Test Drive may not provide address data, check whether we have them
        if (!empty($postData['address'])) {
            $gcdmDataArray['businessPartner']['addresses'] = $this->_prepareGcdmAddress($gcdmData, $postData);
        }

        if (!empty($postData['customer']['dob'])) {
            $gcdmDataArray['businessPartner']['birthday'] = \DateTime::createFromFormat(
                Mage::helper('peppermint_all')->getLocaleDateGlobalFormat(),
                $postData['customer']['dob']
            )->format(self::GCDM_DATE_FORMAT);
        }

        return $this->_pushPhoneIdentifier($gcdmData['main_phone_guid'], $gcdmDataArray);
    }

    /**
     * Prepares the dataset as GCDM expects it
     *
     * @param [] $gcdmData
     * @param [] $postData
     *
     * @return []|false
     */
    public function prepareDataForGcdmFromCheckout($gcdmData, $postData)
    {
        if (empty($postData['address'])) {
            return false;
        }

        // General partner information
        $gcdmDataArray = [
            'businessPartner' => [
                'gcid' => $gcdmData['gcid'],
                'partnerCategory' => 'PERSON',
                'givenName' => $postData['first_name'] ?? null,
                'surname' => $postData['last_name'] ?? null,
                'addresses' => $this->_prepareGcdmAddress($gcdmData, $postData),
                'profile' => [
                    'personalIdentifications' => [
                        'personalIdentifications' => [[
                            'id' => $postData['south_african_id_number'],
                            'type' => $this->convertDocumentTypeToGcdm($postData['south_african_document_type'])
                        ]]
                    ]
                ]
            ]
        ];
        if ($postData['customer_type'] == 'business') {
            // Small companies data
            $communicationStatus = self::GCDM_BUSINESS;
            $communicationType = self::GCDM_COMMUNICATION_BUSINESS;
            $phoneValue = $postData['company']['contact_number'];
            $phoneGUID = $gcdmData['business_phone_guid'];
            $vatGUID = $gcdmData['vat_number_guid'];

            $gcdmDataArray = $this->_pushBusinessData($postData, $gcdmDataArray);
            $gcdmDataArray = $this->_pushVatNumber($vatGUID, $postData['company']['vat_number'], $gcdmDataArray);
        } else {
            $communicationStatus = self::GCDM_PRIVATE;
            $communicationType = self::GCDM_COMMUNICATION_PRIVATE;
            $phoneValue = $postData['primary_number'];
            $phoneGUID = $gcdmData['main_phone_guid'];
        }

        // Communications data
        $gcdmDataArray['businessPartner']['communications'] = [
            'communicationPhones' => [[
                'communicationType' => $communicationType,
                'communicationStatus' => $communicationStatus,
                'preferred' => true,
                'value' => $phoneValue
            ]]
        ];

        if (!empty($postData['dob'])) {
            $gcdmDataArray['businessPartner']['birthday'] = \DateTime::createFromFormat(
                Mage::helper('peppermint_all')->getLocaleDateGlobalFormat(),
                $postData['dob']
            )->format(self::GCDM_DATE_FORMAT);
        }

        return $this->_pushPhoneIdentifier($phoneGUID, $gcdmDataArray);
    }

    /**
     * Push consGUID identifier if is defined
     *
     * @param string $mainPhoneGuid
     * @param array $gcdmDataArray
     * @return array
     */
    private function _pushPhoneIdentifier($mainPhoneGuid, $gcdmDataArray)
    {
        if (!empty($mainPhoneGuid)) {
            $gcdmDataArray['businessPartner']['communications']['communicationPhones'][0]['consGUID'] = $mainPhoneGuid;
        }

        return $gcdmDataArray;
    }

    /**
     * Push vat_number and GUID  if is defined
     *
     * @param string $vatGuid
     * @param string $vatNumber
     * @param [] $gcdmDataArray
     *
     * @return []
     */
    private function _pushVatNumber($vatGuid, $vatNumber, $gcdmDataArray)
    {
        if ($vatNumber) {
            $gcdmDataArray['businessPartner']['taxDatas'] = [[
                'vatRegNo' => $vatNumber
            ]];
        }

        if (!empty($vatGuid)) {
            $gcdmDataArray['businessPartner']['taxDatas'][0]['taxDataGUID'] = $vatGuid;
        }

        return $gcdmDataArray;
    }

    /**
     * Push business details
     *
     * @param [] $postData
     * @param [] $gcdmDataArray
     *
     * @return []
     */
    private function _pushBusinessData($postData, $gcdmDataArray)
    {
        $gcdmDataArray['businessPartner']['customExtension']['parameters'] = [
            [
                'key' => 'ZA_organisationName',
                'value' => $postData['company']['name']
            ],
            [
                'key' => 'ZA_organisationTradeRegisterNo',
                'value' => $postData['company']['registration_number']
            ]
        ];

        return $gcdmDataArray;
    }

    /**
     * Extract authorization data
     * @param stdClass $authData
     * @return []
     */
    public function extractAuthData($authData)
    {
        return [
            'accessToken' => $authData->access_token,
            'expiresIn' => $authData->expires_in,
            'refreshToken' => $authData->refresh_token,
            'scope' => $authData->scope,
            'state' => $authData->state
        ];
    }

    /**
     * Convert document type from option_id to gcdm needed format
     *
     * @param integer $optionId
     *
     * @return string
     */
    public function convertDocumentTypeToGcdm($optionId)
    {
        if (!$optionId) {
            return '';
        }
        $optionLabel = Mage::getResourceSingleton('customer/customer')->getAttribute('south_african_document_type')
            ->getSource()
            ->getOptionText($optionId);

        return self::$_documentTypeMapping[$optionLabel];
    }

    /**
     * Convert document type from gcdm format to option_id
     *
     * @param string $optionValue
     *
     * @return integer|null
     */
    public function convertDocumentTypeFromGcdm($optionValue)
    {
        $optionLabel = array_search($optionValue, self::$_documentTypeMapping);
        if (!$optionValue && !$optionLabel) {
            return null;
        }
        $optionId = Mage::getResourceSingleton('customer/customer')->getAttribute('south_african_document_type')
            ->getSource()
            ->getOptionId($optionLabel);

        return $optionId;
    }

    /**
     * Prepare resp data and insert/update peppermint_gcdm_customer_profile guids data
     *
     * @param stdClass $gcdmCusData
     * @param int|string $customerId
     *
     * @return void
     */
    public function syncGuidData($gcdmCusData, $customerId)
    {
        $guidData = $this->extractCustomerProfileData($gcdmCusData);
        $guidData['customer_id'] = $customerId;

        try {
            Mage::getResourceModel('peppermint_gcdm/customer_profile')->sync($guidData);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Extract GCDM Customer phone data
     *
     * @param stdClass $gcdmCusData
     *
     * @return array
     */
    public function extractGcdmPhoneData($gcdmCusData)
    {
        $phoneData = $gcdmCusData->communications->communicationPhones;
        $cusPhoneData = [];

        if (is_array($phoneData)) {
            foreach ($phoneData as $phoneEntry) {
                if ($phoneEntry->communicationStatus === self::GCDM_PRIVATE && $phoneEntry->preferred) {
                    $cusPhoneData['personal'] = [
                        'guid' => $phoneEntry->consGUID ?? null,
                        'value' => $phoneEntry->value ?? null
                    ];
                } elseif ($phoneEntry->communicationStatus === self::GCDM_BUSINESS
                    && $phoneEntry->communicationType === self::GCDM_COMMUNICATION_BUSINESS) {
                    $cusPhoneData['business'] = [
                        'guid' => $phoneEntry->consGUID ?? null,
                        'value' => $phoneEntry->value ?? null
                    ];
                }
            }
        }

        return $cusPhoneData;
    }

    /**
     * Store latest policy data and checkboxes to session
     *
     * @param stdClass $latestPolicy
     * @return void
     */
    public function storePolicyDataToSession($latestPolicy): void
    {
        $policyData = [];
        $policyCheckboxes = [];

        foreach ($latestPolicy->textBlocks[0]->usageDefinitions as $index => $usageDefinition) {
            foreach ($usageDefinition->legalEntities as $legalEntity) {
                foreach ($usageDefinition->usages as $usage) {
                    foreach ($usageDefinition->purposes as $purpose) {
                        $policyData[$usageDefinition->id][] = [
                            'country' => $legalEntity->country,
                            'majorVersion' => $latestPolicy->majorVersion,
                            'minorVersion' => $latestPolicy->minorVersion,
                            'legalEntityId' => $legalEntity->name,
                            'policyId' => $latestPolicy->name,
                            'purposeId' => $purpose->purpose->name,
                            'usageId' => $usage->usage->name,
                            'language' => $latestPolicy->textBlocks[0]->language,
                            'touchpointId' => static::GCDM_POLICY_TOUCHPOINT
                        ];
                    }
                }
            }

            $policyCheckboxes[$usageDefinition->id] = [
                'title' => $usageDefinition->textPrimary,
                'description' => $latestPolicy->textBlocks[0]->usageText[$index]->value,
                'isMandatory' => $usage->variant === static::GCDM_POLICY_MANDATORY
            ];
        }

        $customerSession = Mage::getSingleton('customer/session');
        $customerSession->setData(static::GCDM_LATEST_POLICY_SESSION_KEY, $policyData);
        $customerSession->setData(static::GCDM_LATEST_POLICY_CHECKBOXES_SESSION_KEY, $policyCheckboxes);
    }

    /**
     * Get policy checkboxes data
     *
     * @return array|mixed|null
     */
    public function getCustomerPolicyCheckboxes()
    {
        return Mage::getSingleton('customer/session')->getData(static::GCDM_LATEST_POLICY_CHECKBOXES_SESSION_KEY);
    }

    /**
     * Prepares policy consents and send them to gcdm
     *
     * @param array $acceptedIds array of accepted policy ids
     * @return stdClass
     */
    public function processPolicies($acceptedIds = [])
    {
        $policiesToSend = [];
        $date = gmdate('Y-m-d\TH:i:s\Z');
        $customerSession = Mage::getSingleton('customer/session');

        $gcdmLatestPolicy = $customerSession->getData(static::GCDM_LATEST_POLICY_SESSION_KEY);

        if ($gcdmLatestPolicy) {
            foreach ($gcdmLatestPolicy as $policyGroupId => $policyGroup) {
                foreach ($policyGroup as $policy) {
                    $policiesToSend[] = [
                            'consent' => in_array($policyGroupId, $acceptedIds) ? static::GCDM_POLICY_ACCEPTED
                                : static::GCDM_POLICY_REJECTED,
                            'consentDate' => $date
                        ] + $policy;
                }
            }
        }

        $customerId = $customerSession->getCustomer()
            ->getId();
        $customerAccessData = Mage::getModel('peppermint_gcdm/customer_access')->load($customerId);

        return Mage::helper('peppermint_gcdm/externalCommunication')
            ->setAccessToken($customerAccessData->getAccessToken())
            ->setRefreshToken($customerAccessData->getRefreshToken())
            ->putGcdmCustomerInfo(['contactPolicyConsents' => $policiesToSend]);
    }
}
