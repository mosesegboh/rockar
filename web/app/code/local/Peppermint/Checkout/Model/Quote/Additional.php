<?php

/**
 * @category  Peppermint
 * @package   Peppermint\Checkout
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>, Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Checkout_Model_Quote_Additional extends Rockar_Checkout_Model_Quote_Additional
{
    /**
     * Save customer additional data
     *
     * @param [] $data
     * @return []
     */
    public function saveCustomerAdditionalData(array $data)
    {
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        $checkoutHelper = Mage::helper('rockar_checkout');
        if (empty($data) || !$quote) {
            return [
                'error' => true,
                'message' => $checkoutHelper->__('There was an error with your request.')
            ];
        }
        $quoteId = $quote->getId();
        $data['quote_id'] = $quoteId;
        $model = $this->load($quoteId, 'quote_id');
        $model->addData($data);
        $model->save();

        return [
            'error' => false,
            'message' => $checkoutHelper->__('Your details has been saved.')
        ];
    }

    /**
     * Save customer employment data
     *
     * @param array $data
     *
     * @return array
     */
    public function saveCustomerEmploymentData(array $data)
    {
        $helper = Mage::helper('rockar_checkout');
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();

        if (empty($data) || !$quote) {
            return [
                'error' => true,
                'message' => $helper->__('There was an error with your request.')
            ];
        }

        $quoteId = $quote->getId();
        $data['quote_id'] = $quoteId;

        $this->_processEmploymentsData($data, $quoteId);

        return [
            'error' => false,
            'message' => $helper->__('Your employment has been saved.')
        ];
    }

    /**
     * Process employments form data
     *
     * @param array $employmentEntry
     * @param int $quoteId
     */
    protected function _processEmploymentsData($employmentEntry, $quoteId)
    {
        $model = Mage::getModel('rockar_checkout/quote_additional_employment');
        $model->getResource()->deleteOldEntries($quoteId);

        $employmentEntry['quote_id'] = $quoteId;
        $employmentEntry['influential'] = isset($employmentEntry['influential']) ? implode(" ", $employmentEntry['influential']) : 0;

        $model->setData($employmentEntry)
            ->save();
    }
    
    /**
     * Save customer residence data
     *
     * @param [] $data
     * @return []
     */
    public function saveCustomerResidenceData(array $data)
    {
        $checkoutHelper = Mage::helper('rockar_checkout');
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        if (empty($data) || !$quote) {
            return [
                'error' => true,
                'message' => $checkoutHelper->__('There was an error with your request.')
            ];
        }

        $quoteId = $quote->getId();
        $data['quote_id'] = $quoteId;

        $model = Mage::getModel('rockar_checkout/quote_additional_residence');
        $model->getResource()
            ->deleteOldEntries($quoteId);

        // save data for PostalAddress DFE tag
        $model->setData($data)
            ->save();

        return [
            'error' => false,
            'message' => $checkoutHelper->__('Your residence has been saved.')
        ];
    }
}
