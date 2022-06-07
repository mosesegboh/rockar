<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Adminhtml_FinanceApp_ApiController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Pull DFE reference codes action
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function pullRefCodesAction()
    {
        $response = ['success' => true];
        /** @var Peppermint_Dfe_Helper_Data $dfeHelper */
        $dfeHelper = Mage::helper('peppermint_dfe');

        try {
            $dfeHelper->pullRefCodes();
            $dfeHelper->pullBankBranches();
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
