<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020  Rockar, Ltd (https://rockar.com)
 */

require_once(Mage::getModuleDir('controllers', 'Rockar_Checkout') . DS . 'Onepage' . DS . 'FinancingController.php');

/**
 * Class Peppermint_Checkout_Onepage_FinancingController
 */
class Peppermint_Checkout_Onepage_FinancingController extends Rockar_Checkout_Onepage_FinancingController
{
    /**
     * Finance data save
     *
     * @return $this
     */
    public function saveAction()
    {
        if ($this->_expireAjax()) {
            return $this;
        }

        $approveAction = $this->getRequest()->getParam('action', false);

        if ($approveAction) {
            $this->checkoutFinanceStepAction();
        }

        $result = Mage::helper('peppermint_checkout/ReplacementProduct')->expireQuoteProduct();
        $quote = $this->getOnepage()->getQuote();
        $response = $result->getResponse();

        if (!$result->getSuccess()) {
            $this->setResponseHttpStatusCode(self::HTTP_CODE_BAD_REQUEST);
            $this->sendJson($response);
        } else if ($result->getAllocateProductNotice()) {
            $this->setResponseHttpStatusCode(self::HTTP_CODE_BAD_REQUEST);
            $quote->setSavedStep(Peppermint_Checkout_Helper_Data::DELIVERY_STEP_CODE)->save();
            $this->sendJson($response);
        } else {
            $this->_forward('saveOnCheckout', 'ajax', 'financing');
        }

        return $this;
    }
}
