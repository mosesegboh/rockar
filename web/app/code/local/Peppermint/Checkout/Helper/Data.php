<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>, Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Checkout_Helper_Data extends Rockar2_Checkout_Helper_Data
{
    /**
     * Helper method to retrieve data from magento.
     *
     * @param string $category
     * @param [] $description
     *
     * @return list of dfe objects as handled by magento
     */
    private function _getDfeData($category, $description = [])
    {
        $collection = Mage::getModel('peppermint_dfe/reference_code')->getCollection()
            ->addFieldToFilter('category', $category)
            ->addFieldToFilter('is_deleted', '0');

        if (!empty($description)) {
            $collection->addFieldToFilter('description', ['in' => $description]);
        }
        $collection->setOrder('description', 'asc');

        return $collection->getData();
    }

    /**
     * Get DFE reference data by category name.
     *
     * @param string $category
     *
     * @return []
     */
    public function getDfeDataByCategoryName($category)
    {
        $groupArray = $this->_getDfeData($category);
        $groupedArray = [];

        if (!empty($groupArray)) {
            foreach ($groupArray as ['code' => $code, 'description' => $des]) {
                $groupedArray[$code] = $des;
            }
        }

        return $groupedArray;
    }

    /**
     * Singular lookup, retrieve a string that will be used to compare too.
     *
     * @param string $category category to search for
     * @param string $description description to search
     * @return string the first code returned by the query
     */
    public function getDfeDataSingularStringByCategoryName($category, $description = '')
    {
        $groupItem = reset($this->_getDfeData($category, [$description]));

        if (!empty($groupItem)
            && !empty($groupItem['description'])
            && !empty($groupItem['code'])
            && $groupItem['description'] == $description
        ) {
            return $groupItem['code'];
        }

        return '';
    }

    /**
     * Get DFE reference data by category name.
     *
     * Returns a collection of strings eg ['string1','string2']
     *
     * @param string $category search for this category
     * @param [] $description
     *
     * @return []
     */
    public function getDfeDataCollectionByCategoryName($category, $description = [])
    {
        $groupArray = $this->_getDfeData($category, $description);
        $groupedArray = [];

        if (!empty($groupArray)) {
            foreach ($groupArray as ['code' => $code]) {
                if (isset($code)) {
                    $groupedArray[] = $code;
                }
            }
        }

        return $groupedArray;
    }

    /**
     * Get gender options.
     *
     * @return string
     */
    public function getGenderOptionsJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('Gender'));
    }

    /**
     * Get Ethnic Group options.
     *
     * @return string
     */
    public function getEthnicGroupJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('Race'));
    }

    /**
     * Gets a list(json) of Married statuses codes that will indicate to the front end this client is married.
     *
     * @return string
     */
    public function getMarriedLivingApartStatusJson()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataCollectionByCategoryName(
                'CustomerMaritalStatus',
                [
                    'Living Apart',
                    'Married'
                ]
            )
        );
    }

    /**
     * Gets a list(json) of arital contract where bmw fs will require all the spouses details.
     *
     * @return string
     */
    public function getMaritalContractTypeRequiresSpouseDetailsJson()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataCollectionByCategoryName(
                'TypeOfMarriage',
                [
                    'Community of Property',
                    'Customary Law'
                ]
            )
        );
    }

    /**
     * Gets a string that represents a south african id number
     * helps the front end identify this is a id number validate as such.
     *
     * @return string
     */
    public function getZaIdTypeJson()
    {
        $toReturn = $this->getDfeDataSingularStringByCategoryName('ZaIdType', 'SA ID Number');

        return empty($toReturn) ? null : Mage::helper('rockar_all')->jsonEncode($toReturn);
    }

    /**
     * Gets a list(json) of company type options.
     *
     * @return string
     */
    public function getCompanyTypesJson()
    {
        $dfeCompanyTypes = $this->getDfeDataByCategoryName('CompanyType');
        $companyTypesArray = [[
            'value' => '',
            'label' => ''
        ]];

        foreach ($dfeCompanyTypes as $key => $value) {
            $companyTypesArray[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return Mage::helper('rockar_all')->jsonEncode($companyTypesArray);
    }

    /**
     * Gets a list(json) of designation options.
     *
     * @param boolean $json
     * @return string
     */
    public function getDesignationsJson($json = true)
    {
        $dfeDesignations = $this->getDfeDataByCategoryName('Designation');
        $designationsArray = [[
            'value' => '',
            'label' => ''
        ]];

        foreach ($dfeDesignations as $key => $value) {
            $designationsArray[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $json ? Mage::helper('rockar_all')->jsonEncode($designationsArray) : $designationsArray;
    }

    /**
     * Get designation label by value.
     *
     * @param string $value
     *
     * @return string
     */
    public function getDesignationLabelByValue($value)
    {
        return $this->getDfeDataByCategoryName('Designation')[$value] ?? '';
    }

    /**
     * Get residential address from sales_order_address.
     *
     * @param integer $orderId
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getResidentialAddress($orderId)
    {
        return Mage::getModel('sales/order_address')->load($orderId, 'parent_id');
    }

    /**
     * Get postal address from rockar_order_additional_residence.
     *
     * @param integer $orderId
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getPostalAddress($orderId)
    {
        return Mage::getModel('rockar_checkout/order_additional_residence')->load($orderId, 'order_id');
    }

    /**
     * Get preferred language options.
     *
     * @return string
     */
    public function getPreferredLanguageJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('PreferredLanguage'));
    }

    /**
     * Get marital status options as an array.
     *
     * @return string
     */
    public function getMaritalStatusJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('CustomerMaritalStatus'));
    }

    /**
     * Get marriage type options.
     *
     * @return string
     */
    public function getMarriageTypeJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('TypeOfMarriage'));
    }

    /**
     * Get Spouse Unique Id Type options.
     *
     * @return string
     */
    public function getSpouseUniqueIdTypeJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('CustomerUniqueIdType'));
    }

    /**
     * Get the status that represents an owner -- code is used and not the description.
     *
     * @return string
     */
    public function getOwnerStatusJson()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataSingularStringByCategoryName('ResidentialStatus', 'Owner')
        );
    }

    /**
     * Gets a list(json) of bond statuses.
     *
     * @return string
     */
    public function getBondStatusJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('BondStatus'));
    }

    /**
     * Gets a list(json) of Employment industries.
     *
     * @return string
     */
    public function getEmploymentIndustryJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('EmploymentIndustry'));
    }

    /**
     * Gets the status that represents unemployed, will retrieve the first item.
     *
     * @return string
     */
    public function getUnemployedStatusJson()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataCollectionByCategoryName('EmploymentStatus', ['Unemployed'])
        );
    }

    /**
     * Gets a list(json) that is retired and student status codes.
     *
     * @return string
     */
    public function getRetiredOrStudentStatusJson()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataCollectionByCategoryName('EmploymentStatus', ['Retired', 'Student'])
        );
    }

    /**
     * Gets a list(json) of Additional income.
     *
     * @return string
     */
    public function getSourceOfAdditionalIncomeJson()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataByCategoryName('SourceOfAdditionalIncome')
        );
    }

    /**
     * Gets a list(json) of bank account types.
     *
     * @return string
     */
    public function getBankAccountTypeJson()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataByCategoryName('AccountType')
        );
    }

    /**
     * Gets a list(json) Bank names.
     *
     * @return string
     */
    public function getBankNameJson()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataByCategoryName('BankName')
        );
    }

    /**
     * Gets a list(json) of branch names.
     *
     * @return string
     */
    public function getBranchNameJson()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataByCategoryName('BranchName')
        );
    }

    /**
     * Gets a list(json) of deposit sources.
     *
     * @return string
     */
    public function getDepositSource()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            $this->getDfeDataByCategoryName('Deposit_Source')
        );
    }

    /**
     * Retrieve save details url.
     *
     * @return string
     */
    public function getSaveDepositDetailsUrl()
    {
        return $this->_getUrl('checkout/onepage/saveDepositDetails', [
            'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);
    }

    /**
     * Get employment status type options.
     *
     * @return string
     */
    public function getEmploymentStatusTypeJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('EmploymentStatus'));
    }

    /**
     * Get industry type options.
     *
     * @return string
     */
    public function getIndustryTypeJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getDfeDataByCategoryName('Industry'));
    }

    /**
     * Get bank names options.
     *
     * @throws Exception
     * @return string
     */
    public function getBankNamesJson()
    {
        $groupedArray = [];

        try {
            $listBanks = Mage::helper('peppermint_dfe')->getBanks();

            foreach ($listBanks as ['value' => $code, 'text' => $des]) {
                if (!empty($code) && !empty($des)) {
                    $groupedArray[] = [
                        'value' => $code,
                        'text' => $des
                    ];
                }
            }
        } catch (Exception $e) {
            // in case of exception, an empty array will be encoded
            Mage::logException($e);
        }

        return Mage::helper('rockar_all')->jsonEncode($groupedArray);
    }

    /**
     * Get Branches Url.
     *
     * @return string
     */
    public function getBankBranchesUrl()
    {
        return $this->_getUrl('dfe/bank/getBankBranches');
    }

    /**
     * Get empty quote data.
     *
     * @return []
     */
    public function getEmptyQuoteData()
    {
        return [
            'marital_status' => null,
            'home_tel' => null,
            'gender' => null,
            'race' => null,
            'preferred_language' => null,
            'marriage_type' => null,
            'spouse_first_name' => null,
            'spouse_last_name' => null,
            'spouse_id_type' => null,
            'spouse_id_no' => null,
            'spouse_cell_number' => null,
            'spouse_email' => null,
            'kin_name' => null,
            'kin_tel' => null,
            'spouse_consent' => null,
            'nationality' => null,
            'changed_name' => null,
            'previous_title' => null,
            'previous_first_name' => null,
            'previous_last_name' => null,
            'number_of_dependencies' => null,
            'gross_annual_salary' => null,
            'gross_annual_other_income' => null,
            'number_of_credit_cards' => null,
            'name_of_bank_account' => null,
            'sort_code' => null,
            'account_number' => null,
            'years_time_with_bank' => null,
            'months_time_with_bank' => null,
            'replace_existing_agreement' => null,
            'monthly_mortgage' => null,
            'other_monthly_expenditure' => null,
            'company_type' => null,
            'company_name' => null,
            'registration_number' => null,
            'vat_number' => null
        ];
    }

    /**
     * Get empty deposit data.
     *
     * @return []
     */
    public function getEmptyDepositData()
    {
        return [
            'sourceOfDeposit' => null,
            'sourceDescription' => null
        ];
    }

    /**
     * Clears cached quote item
     */
    public function resetQuoteItem()
    {
        $this->_quoteItem = null;
    }

    /**
     * Get empty quote data for communication preferences.
     *
     * @return []
     */
    public function getEmptyQuoteCommunicationPreferencesData()
    {
        return [
            'preferredComsMethodEmail' => 1,
            'preferredComsMethodSms' => 0,
            'preferredComsMethodNormal' => 0,
            'contractDocumentation' => '0',
            'statementFrequency' => '0'
        ];
    }

    /**
     * Get empty quote data for employment section.
     *
     * @return []
     */
    public function getEmptyQuoteEmploymentData()
    {
        return [
            'employment_status' => null,
            'employment_industry' => null,
            'occupation' => null,
            'current_employer' => null,
            'employers_phone' => null,
            'duration_at_current_employer' => null,
            'duration_at_previous_employer' => null,
            'influential' => [],
            'previous_employer' => null
        ];
    }

    /**
     * Get empty quote data for residence section.
     *
     * @return []
     */
    public function getEmptyQuoteResidenceData()
    {
        return [
            'ownership' => null,
            'accommodation_type' => null,
            'postcode' => null,
            'city' => null,
            'region' => null,
            'duration_at_current_address' => null,
            'duration_at_previous_address' => null,
            'address_1' => null,
            'address_2' => null,
            'house_status' => null,
            'same_as_residential' => null
        ];
    }

    /**
     * Get company type label by value.
     *
     * @param string $value
     *
     * @return string
     */
    public function getCompanyTypeLabelByValue($value)
    {
        return $this->getDfeDataByCategoryName('CompanyType')[$value] ?? '';
    }

    /**
     * Returns carfinder url for redirects
     *
     * @param bool $redirectToResults
     *
     * @return string
     */
    public function getCarFinderUrl($redirectToResults = false)
    {
        $urlParams = [];
        $urlParams['_escape'] = true;

        $productFilters = Mage::helper('rockar_carfinder')->getProductFilterParams();

        if (isset($productFilters['price'])) {
            unset($productFilters['price']);
        }

        // only applies to checkout so when vehicle out of stock shows and back is clicked
        // user is redirected to results page instead of finance step
        if ($redirectToResults) {
            $productFilters['step'] = Mage::helper('peppermint_carfinder')->getCarFilterStepName();
            $urlParams['_escape'] = false;
        }

        $urlParams['_query'] = $productFilters;
        $category = Mage::helper('rockar_catalog')->getCarFinderCategory();

        return Mage::getUrl($category->getRequestPath(), $urlParams);
    }

    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getSaveCompanyUrl()
    {
        return $this->_getUrl('checkout/onepage/saveCompany', [
            'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);
    }

    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getSaveResidenceUrl()
    {
        return $this->_getUrl('checkout/onepage/saveResidence', [
            'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);
    }

    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getSaveEmploymentUrl()
    {
        return $this->_getUrl('checkout/onepage/saveEmployment', [
            'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);
    }

    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getSaveDetailsUrl()
    {
        return $this->_getUrl('checkout/onepage/saveDetails', [
            'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);
    }

    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getAddressUrl()
    {
        return $this->_getUrl('checkout/onepage/getAddress', [
            'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);
    }
}
