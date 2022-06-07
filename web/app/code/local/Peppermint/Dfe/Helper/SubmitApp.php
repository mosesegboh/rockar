<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_SubmitApp extends Mage_Core_Helper_Abstract
{
    /**
     * Submit data to DFE.
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return boolean
     */
    public function submit($order)
    {
        $helper = Mage::helper('peppermint_dfe');
        $service = (new Peppermint_Dfe_Helper_Soap_FinanceApplicationService(
            $helper->wsdlPath,
            $helper->getServiceApiKey()
        ))->setAuthHeader($helper->getUser(), $helper->getPass())
            ->setServiceLocation($helper->getServiceUrl());

        $resendOrder = Mage::getModel('peppermint_dfe/resend_order')->load($order->getId(), 'order_id');
        $resendOrderExists = $resendOrder->getOrderId();

        try {
            $response = $service->submitApplication(
                new Peppermint_Dfe_Soap_Application_SubmitApplication($this->_dataToSubmit($order))
            );
            $comments = $this->_prepareDataResponse($response->getSubmitApplicationResult());

            if ($comments) {
                $order->addStatusHistoryComment($comments)
                    ->setIsVisibleOnFront(false)
                    ->setIsCustomerNotified(false)
                    ->save();
            }

            if ($resendOrderExists) {
                $resendOrder->delete();
            }
        } catch (Exception $e) {
            Mage::logException($e);

            $resendOrder->addData($resendOrderExists ? ['error_count' => $resendOrder->getErrorCount() + 1] : ['order_id' => $order->getId()])
                ->save();

            return false;
        }

        return true;
    }

    /**
     * Prepare DFE response message.
     *
     * @param object $submitApplicationDataResult
     * @return string
     */
    protected function _prepareDataResponse($submitApplicationDataResult)
    {
        $data = [];
        $dataMessageDfe = $submitApplicationDataResult->getMessages()
            ->getString();

        if ($dataMessageDfe && is_array($dataMessageDfe)) {
            foreach ($dataMessageDfe as $val) {
                $data[] = $val;
            }
        } else {
            $data[] = $dataMessageDfe;
        }

        return implode(PHP_EOL, $data);
    }

    /**
     * Mock data for submit to DFE
     * After integrated with checkout will need a mapping function with valid data.
     *
     * @param Peppermint_Sales_Model_Order $order
     * @throws Exception
     * @return object
     */
    protected function _dataToSubmit($order)
    {
        $orderId = $order->getId();
        $orderData = Mage::getModel('sales/order')->load($orderId);

        $applicant = Mage::helper('peppermint_dfe/application_applicant')->setApplicantData($orderId);
        $residentialAddress = Mage::helper('peppermint_dfe/application_residentialAddress')->setResidentialAddressData($orderId);
        $basicDetails = Mage::helper('peppermint_dfe/application_basicDetails')->setBasicDetailsData($orderId);
        $contactDetails = Mage::helper('peppermint_dfe/application_contactDetails')->setContactDetailsData($orderId);
        $employmentDetails = Mage::helper('peppermint_dfe/application_employmentDetails')->setEmploymentDetailsData($orderId);
        $incomeDetails = Mage::helper('peppermint_dfe/application_incomeDetails')->setIncomeDetailsData($orderId);
        $residentialDetails = Mage::helper('peppermint_dfe/application_residentialDetails')->setResidentialDetailsData($orderId);
        $bankDetails = Mage::helper('peppermint_dfe/application_bankDetails')->setBankDetailsData($orderId);
        $postalAddress = Mage::helper('peppermint_dfe/application_postalAddress')->setPostalAddressData($orderId);
        $previousAddress = Mage::helper('peppermint_dfe/application_previousAddress')->setPreviousAddressData($orderId);
        $feeList = Mage::helper('peppermint_dfe/application_feeDetails')->setFeeListData($order);
        $nefData = Mage::helper('peppermint_dfe/application_NEFDetails')->setNefData($order);

        $customerDetails = (new Peppermint_Dfe_Soap_Application_IndividualCustomerDetails())->setApplicant($applicant)
            ->setResidentialAddress($residentialAddress)
            ->setBasicDetails($basicDetails)
            ->setContactDetails($contactDetails)
            ->setEmploymentDetails($employmentDetails)
            ->setIncomeDetails($incomeDetails)
            ->setResidentialInformationDetails($residentialDetails)
            ->setBankDetails($bankDetails)
            ->setPreviousAddress($previousAddress)
            ->setPostalAddress($postalAddress);

        $financeDetails = Mage::helper('peppermint_dfe/application_financeDetails')->setFinanceDetailsData($order);
        $assetDetails = Mage::helper('peppermint_dfe/application_assetDetails')->setAssetDetailsData($order);

        if ($nefData->getNEFAmt() != 0) {
            $nefList = new Peppermint_Dfe_Soap_Application_ArrayOfNEFDetails();
            $nefList[] = $nefData;
        } else {
            $nefList = null;
        }
        $referenceId = $orderData->getIncrementId();

        $quoteDetails = (new Peppermint_Dfe_Soap_Application_QuoteDetails())->setQuoteReferenceId($referenceId ?? 0)
            ->setFinanceDetails($financeDetails)
            ->setAssetDetails($assetDetails)
            ->setFeeList($feeList)
            ->setNefList($nefList);

        $applicationDate = new \DateTime();
        $customerId = $orderData->getCustomerId();
        $dealerCode = $orderData->getDealerCode();
        $outletId = Mage::getModel('rockar_localstores/stores')->load($dealerCode, 'code')
            ->getBranch() ?: 0;

        return (new Peppermint_Dfe_Soap_Application_FinanceApplication($outletId, false))->setRequestId($referenceId ?? 0)
            ->setApplicationId(null)
            ->setApplicationDate($applicationDate->format('Ymd'))
            ->setDealerId(null)
            ->setUserId($customerId)
            ->setFnIemailAddress(null)
            ->setServiceProvider('Rockar')
            ->setSettlementBankName('')
            ->setSettlementAccountNumber('')
            ->setSettlementAmount(null)
            ->setMonthlyInstallment(null)
            ->setIndividualCustomerList([$customerDetails])
            ->setQuoteList([$quoteDetails]);
    }

    /**
     * Send order data to DFE
     *
     * @param  Mage_Sales_Model_Order $order
     * @return $this
     */
    public function sendOrderToDfe(Mage_Sales_Model_Order $order)
    {
        $financeData = [];
        $financeOverlayData = Mage::helper('core')->jsonDecode(
            Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order)
                ->getFinanceOverlay()
        );

        if (isset($financeOverlayData['options'])) {
            foreach ($financeOverlayData['options'] as $option) {
                if (isset($option['pay_in_full'])) {
                    $financeData['pay_in_full'] = $option['pay_in_full'];
                    break;
                }
            }
        }

        $isPayInFull = $financeData['pay_in_full'] ?? strpos($order->getFinancePaymentType(), 'cash') !== false;

        if ($order::STATE_PROCESSING === $order->getState() && !$isPayInFull) {
            $this->submit($order);
        }

        return $this;
    }
}
