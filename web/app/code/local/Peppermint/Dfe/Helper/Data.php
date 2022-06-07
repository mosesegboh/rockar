<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NS_API_WDSL_URL = 'peppermint_dfe/api/wsdl_url';
    const NS_API_SERVICE_URL = 'peppermint_dfe/api/service_url';
    const NS_API_SERVICE_KEY = 'peppermint_dfe/api/key';
    const NS_API_AUTH_USER = 'peppermint_dfe/api/user';
    const NS_API_AUTH_PASS = 'peppermint_dfe/api/pass';
    const WSDL_FILENAME = 'financeapp_old.wsdl';

    /**
     * @var string The WSDL path to be used
     */
    public $wsdlPath;

    /**
     * @return Peppermint_Dfe_Helper_Data
     */
    public function __construct()
    {
        $this->wsdlPath = Mage::getModuleDir('', 'Peppermint_Dfe') . '/Soap/' . self::WSDL_FILENAME;

        return $this;
    }

    /**
     * @return string
     */
    public function getWsdlUrl()
    {
        return Mage::getStoreConfig(self::NS_API_WDSL_URL);
    }

    /**
     * @return string
     */
    public function getServiceUrl()
    {
        return Mage::getStoreConfig(self::NS_API_SERVICE_URL);
    }

    /**
     * Configured api key.
     *
     * @return string
     */
    public function getServiceApiKey()
    {
        return Mage::getStoreConfig(self::NS_API_SERVICE_KEY);
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return Mage::getStoreConfig(self::NS_API_AUTH_USER);
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return Mage::getStoreConfig(self::NS_API_AUTH_PASS);
    }

    /**
     * Pulls reference codes from DFE and synchronizes the local table.
     *
     * @return void
     */
    public function pullRefCodes()
    {
        try {
            $service = (new Peppermint_Dfe_Helper_Soap_ReferenceCodesService($this->wsdlPath, $this->getServiceApiKey()))
                ->setAuthHeader($this->getUser(), $this->getPass())
                ->setServiceLocation($this->getServiceUrl());

            /** @var Peppermint_Dfe_Soap_ReferenceCodes_GetReferenceCodesResponse $response */
            $response = $service->getReferenceCodes(new Peppermint_Dfe_Soap_ReferenceCodes_GetReferenceCodes());

            Mage::getModel('peppermint_dfe/reference_code')->sync($response->getGetReferenceCodesResult());
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Pulls bank branches from DFE and synchronizes the local table.
     *
     * @return void
     */
    public function pullBankBranches()
    {
        try {
            $service = (new Peppermint_Dfe_Helper_Soap_BankBranchService($this->wsdlPath, $this->getServiceApiKey()))
                ->setAuthHeader($this->getUser(), $this->getPass())
                ->setServiceLocation($this->getServiceUrl());

            /** @var Peppermint_Dfe_Soap_BankBranch_GetBankBranchListResponse $response */
            $response = $service->getBankBranch(new Peppermint_Dfe_Soap_BankBranch_GetBankBranchList());

            Mage::getModel('peppermint_dfe/bank_branches')->sync($response->getGetBankBranchListResult());
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Get bank list from DFE.
     *
     * @return array
     */
    public function getBanks()
    {
        $result = [];

        /** @var Peppermint_Dfe_Model_Resource_Bank_Branches_Collection $collection */
        $collection = Mage::getResourceModel('peppermint_dfe/bank_branches_collection');

        if ($collection->count()) {
            foreach ($collection as $item) {
                $bankCode = trim($item->getBankCode());

                $result[$bankCode] = [
                    'value' => $bankCode,
                    'text' => trim($item->getBankName())
                ];
            }

            return $result;
        }

        try {
            $service = (new Peppermint_Dfe_Helper_Soap_BankService($this->wsdlPath, $this->getServiceApiKey()))
                ->setAuthHeader($this->getUser(), $this->getPass())
                ->setServiceLocation($this->getServiceUrl());

            /** @var Peppermint_Dfe_Soap_Bank_GetBankListResponse $response */
            $response = $service->getBank(new Peppermint_Dfe_Soap_Bank_GetBankList());

            return Mage::getModel('peppermint_dfe/bank_bank')->getTheArrayOfBanks($response->getGetBankListResult());
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $result;
    }

    /**
     * Get bank branches by bankCode.
     *
     * @param $bankCode
     *
     * @return array
     */
    public function getBankBranches($bankCode)
    {
        $result = [];

        /** @var Peppermint_Dfe_Model_Resource_Bank_Branches_Collection $collection */
        $collection = Mage::getResourceModel('peppermint_dfe/bank_branches_collection')
            ->addFieldToFilter('bank_code', $bankCode);

        if ($collection->count()) {
            foreach ($collection as $item) {
                $result[] = [
                    'value' => trim($item->getBranchCode()),
                    'text' => trim($item->getBranchName())
                ];
            }

            return $result;
        }

        try {
            $service = (new Peppermint_Dfe_Helper_Soap_BankBranchService($this->wsdlPath, $this->getServiceApiKey()))
                ->setAuthHeader($this->getUser(), $this->getPass())
                ->setServiceLocation($this->getServiceUrl());

            /** @var Peppermint_Dfe_Soap_BankBranch_GetBankBranchListResponse $response */
            $response = $service->getBankBranch(new Peppermint_Dfe_Soap_BankBranch_GetBankBranchList());

            return Mage::getModel('peppermint_dfe/bank_branches')
                ->getTheArrayOfBranches($response->getGetBankBranchListResult(), $bankCode);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $result;
    }

    /**
     * Transform into year and month a total months data.
     *
     * @param integer $totalMonths
     *
     * @return array
     */
    public function transformToYearMonth($totalMonths)
    {
        return [
            'years' => (int) ($totalMonths / 12),
            'months' => (int) ($totalMonths % 12)
        ];
    }
}
