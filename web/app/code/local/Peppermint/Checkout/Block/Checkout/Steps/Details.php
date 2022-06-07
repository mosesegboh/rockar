<?php

/**
 * @category  Peppermint
 * @package   Peppermint/Checkout
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>, Razvan Zofota <razvan.zofota@rockar.com>, Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright  Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Checkout_Block_Checkout_Steps_Details extends Rockar_Checkout_Block_Checkout_Steps_Details
{

    /**
     * Returns current quote data
     *
     * @return mixed
     */
    public function getQuoteData()
    {
        $quoteId = Mage::getSingleton('checkout/type_onepage')->getQuote()
            ->getId();

        $quoteData = Mage::getModel('rockar_checkout/quote_additional')->load($quoteId, 'quote_id')
            ->getData();

        $dataArray = $quoteData ?: Mage::helper('peppermint_checkout')->getEmptyQuoteData();

        return Mage::helper('rockar_all')->jsonEncode($dataArray);
    }

    /**
     * Returns current deposit quote data
     *
     * @return mixed
     */
    public function getDepositQuoteData()
    {
        $quoteId = Mage::getSingleton('checkout/type_onepage')->getQuote()
            ->getId();

        $quoteData = Mage::getModel('sales/quote_item')->load($quoteId, 'quote_id')
            ->getData();

        $depositData = [];

        if ($quoteData) {
            $depositData['sourceOfDeposit'] = $quoteData['deposit_source'] ?? null;
            $depositData['sourceDescription'] = $quoteData['deposit_other_description'] ?? null;
        } else {
            $depositData = Mage::helper('peppermint_checkout')->getEmptyDepositData();
        }

        return Mage::helper('rockar_all')->jsonEncode($depositData);
    }

    /**
     * Get url for deposit
     *
     * @return string
     */
    public function getSaveDepositDetailsUrl()
    {
        return $this->helper('peppermint_checkout')->getSaveDepositDetailsUrl();
    }

    /**
     * Get communication preferences quote data
     *
     * @return []
     */
    public function getCommunicationPreferencesQuoteData()
    {
        $quoteId = Mage::getSingleton('checkout/type_onepage')->getQuote()
            ->getId();

        $additionalData = Mage::getModel('rockar_checkout/quote_additional')->load($quoteId, 'quote_id')
            ->getData();

        $quoteData = [];

        if ($additionalData) {
            $quoteData['preferredComsMethodEmail'] = $additionalData['pref_method_contact_email'];
            $quoteData['preferredComsMethodSms'] = $additionalData['pref_method_contact_sms'];
            $quoteData['preferredComsMethodNormal'] = $additionalData['pref_method_contact_normal'];
            $quoteData['contractDocumentation'] = $additionalData['contract_document'];
            $quoteData['statementFrequency'] = $additionalData['statement_frequency'];
        } else {
            $quoteData = Mage::helper('peppermint_checkout')->getEmptyQuoteCommunicationPreferencesData();
        }

        return Mage::helper('rockar_all')->jsonEncode($quoteData);
    }

    /**
     * Returns the url for income save
     *
     * @return string
     */
    public function getIncomeDetailsAction()
    {
        return $this->helper('peppermint_checkout/quote_additional_income')->getIncomeDetailsAction();
    }

    /**
     * Returns json data with all income related variables saved
     *
     * @return string
     */
    public function getIncomeQuoteData()
    {
        return $this->helper('rockar_all')
            ->jsonEncode($this->helper('peppermint_checkout/quote_additional_income')
                ->getData()
            );
    }

    /**
     * Get data of saved employment forms
     *
     * @return string
     */
    public function getEmploymentQuoteData()
    {
        $helper = Mage::helper('rockar_all');
        $quoteId = Mage::getSingleton('checkout/type_onepage')->getQuote()
            ->getId();
        $collectionData = Mage::getModel('rockar_checkout/quote_additional_employment')->getCollection()
            ->addFieldToFilter('quote_id', ['eq' => $quoteId])
            ->getFirstItem()
            ->getData();

        if (!empty($collectionData)) {
            $collectionData['influential'] = isset($collectionData['influential'])
                ? explode(' ', $collectionData['influential']) : [];

            return $helper->jsonEncode($collectionData);
        }

        return $helper->jsonEncode(Mage::helper('peppermint_checkout')->getEmptyQuoteEmploymentData());
    }

    /**
     * Get ownership options as JSON
     *
     * @return string
     */
    public function getOwnershipJson()
    {
        return $this->_dfeJsonData('ResidentialStatus');
    }

    /**
     * Get accommodation type options as JSON
     *
     * @return string
     */
    public function getAccommodationTypeJson()
    {
        return $this->_dfeJsonData('ResidentialOwner');
    }

    /**
     * Get home(Bond) status options as JSON
     *
     * @return string
     */
    public function getBondStatusJson()
    {
        return $this->_dfeJsonData('HomeStatus');
    }

    /**
     * Get data of saved residence forms
     *
     * @return string
     */
    public function getResidenceQuoteDataJson()
    {
        $quoteId = Mage::getSingleton('checkout/type_onepage')->getQuote()
            ->getId();
        $quoteSavedValues = Mage::getModel('rockar_checkout/quote_additional_residence')->load($quoteId, 'quote_id')
            ->getData();

        return Mage::helper('rockar_all')->jsonEncode(
            empty($quoteSavedValues)
                ? Mage::helper('peppermint_checkout')->getEmptyQuoteResidenceData()
                : $quoteSavedValues
        );
    }

    /**
     * Get DFE reference data by category name json
     *
     * @param string $categoryName
     * @return string
     */
    private function _dfeJsonData($categoryName)
    {
        $dfeData = Mage::helper('peppermint_checkout')->getDfeDataByCategoryName($categoryName);

        return Mage::helper('rockar_all')->jsonEncode($dfeData);
    }


    /**
     * Get credit app data
     *
     * @return string
     */
    public function getCreditAppData()
    {
        $urls = [
            'skipOtpPopup' => $this->_isOtpPopupPassed(),
            'totalAttempts' => Mage::helper('peppermint_dfe/creditApp')->getAttemptsNumber(),
            'retryAttempts' => Mage::helper('peppermint_dfe/creditApp')->getRetryAttempts(),
            'getUserInfoUrl' => Mage::getUrl('peppermint_dfe/creditApp/getUserInfo'),
            'submitCodeUrl' => Mage::getUrl('peppermint_dfe/creditApp/submitCode'),
            'countdownExpires' => Mage::helper('peppermint_dfe/creditApp')->getExpiredPeriod()
        ];

        return Mage::helper('rockar_all')->jsonEncode($urls);
    }

    /**
     * Check if OTP popup must be shown
     *
     * @return bool
     */
    protected function _isOtpPopupPassed()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if ($quote) {
            return (bool)$quote->getOtpPopupPassed();
        }

        return false;
    }
}
