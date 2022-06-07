<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_BankController extends Mage_Core_Controller_Front_Action
{
    /**
     * Get banks
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function getBanksAction()
    {
        try {
            /** @var Peppermint_Dfe_Soap_Bank_GetBankListResponse $response */
            $response = Mage::helper('peppermint_dfe')->getBanks();
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('rockar_all')->jsonEncode($response));
    }

    /**
     * Get bank Branches
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function getBankBranchesAction()
    {
        $bankCode = $this->getRequest()->getParam('bankCode');

        try {
            /** @var Peppermint_Dfe_Soap_BankBranch_GetBankBranchListResponse $response */
            $response = Mage::helper('peppermint_dfe')->getBankBranches($bankCode);
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('rockar_all')->jsonEncode($response));
    }
}
