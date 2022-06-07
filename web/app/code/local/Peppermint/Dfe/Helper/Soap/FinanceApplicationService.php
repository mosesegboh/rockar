<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Soap_FinanceApplicationService extends Peppermint_Dfe_Helper_Soap_Abstract_Service
{
    /**
     * @var [] The defined classes
     */
    protected static $_classmap = [
        'Authentication' => 'Peppermint_Dfe_Soap_Authentication',
        'SubmitApplication' => 'Peppermint_Dfe_Soap_Application_SubmitApplication',
        'SubmitApplicationResponse' => 'Peppermint_Dfe_Soap_Application_SubmitApplicationResponse',
        'FinanceApplication' => 'Peppermint_Dfe_Soap_Application_FinanceApplication',
        'IndividualCustomerDetails' => 'Peppermint_Dfe_Soap_Application_IndividualCustomerDetails',
        'Applicant' => 'Peppermint_Dfe_Soap_Application_Applicant',
        'Address' => 'Peppermint_Dfe_Soap_Application_Address',
        'BasicDetails' => 'Peppermint_Dfe_Soap_Application_BasicDetails',
        'EmploymentDetails' => 'Peppermint_Dfe_Soap_Application_EmploymentDetails',
        'IncomeDetails' => 'Peppermint_Dfe_Soap_Application_IncomeDetails',
        'ResidentialInformationDetails' => 'Peppermint_Dfe_Soap_Application_ResidentialInformationDetails',
        'BankDetails' => 'Peppermint_Dfe_Soap_Application_BankDetails',
        'ContactDetails' => 'Peppermint_Dfe_Soap_Application_ContactDetails',
        'CorporateCustomer' => 'Peppermint_Dfe_Soap_Application_CorporateCustomer',
        'CustomerDetails' => 'Peppermint_Dfe_Soap_Application_CustomerDetails',
        'AssociatedCompanies' => 'Peppermint_Dfe_Soap_Application_AssociatedCompanies',
        'PersonnelInformation' => 'Peppermint_Dfe_Soap_Application_PersonnelInformation',
        'QuoteDetails' => 'Peppermint_Dfe_Soap_Application_QuoteDetails',
        'FinanceDetails' => 'Peppermint_Dfe_Soap_Application_FinanceDetails',
        'AssetDetails' => 'Peppermint_Dfe_Soap_Application_AssetDetails',
        'ArrayOfAssetOptionsDetails' => 'Peppermint_Dfe_Soap_Application_ArrayOfAssetOptionsDetails',
        'AssetOptionsDetails' => 'Peppermint_Dfe_Soap_Application_AssetOptionsDetails',
        'ArrayOfFeeDetails' => 'Peppermint_Dfe_Soap_Application_ArrayOfFeeDetails',
        'FeeDetails' => 'Peppermint_Dfe_Soap_Application_FeeDetails',
        'ArrayOfNEFDetails' => 'Peppermint_Dfe_Soap_Application_ArrayOfNEFDetails',
        'NEFDetails' => 'Peppermint_Dfe_Soap_Application_NEFDetails',
        'FinanceApplicationResponse' => 'Peppermint_Dfe_Soap_Application_FinanceApplicationResponse',
        'ArrayOfString' => 'Peppermint_Dfe_Soap_Application_ArrayOfString',
        'ArrayOfValidationMessages' => 'Peppermint_Dfe_Soap_Application_ArrayOfValidationMessages',
        'ValidationMessages' => 'Peppermint_Dfe_Soap_Application_ValidationMessages'
    ];

    /**
     * @param Peppermint_Dfe_Soap_Application_SubmitApplication $parameters
     * @return Peppermint_Dfe_Soap_Application_SubmitApplicationResponse
     */
    public function submitApplication(Peppermint_Dfe_Soap_Application_SubmitApplication $parameters)
    {
        return $this->__soapCall('SubmitApplication', [$parameters]);
    }
}
