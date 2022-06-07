<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Juris Krislauks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar_Orderamend') . DS .
    'Adminhtml' . DS . 'Order' . DS . 'Amend' . DS . 'FinanceController.php';

class Peppermint_Orderamend_Adminhtml_Order_Amend_FinanceController extends Rockar_Orderamend_Adminhtml_Order_Amend_FinanceController
{
    /**
     * Save filters to finance_data field in quote item
     * Added balloonPercentage variable to quoteFinanceData.
     *
     * @return void
     */
    public function saveFiltersAction()
    {
        try {
            $params = $this->getRequest()->getParams();

            $orderAmendHelper = Mage::helper('rockar_orderamend');
            $helperAll = Mage::helper('rockar_all');

            $quote = $orderAmendHelper->getQuote();

            $quoteItem = Mage::helper('rockar_checkout/quote')->getFirstVisibleQuoteItem($quote);
            $quoteFinanceData = $helperAll->jsonDecode($quoteItem->getData('finance_data'));

            $quoteFinanceData['method'] = $params['method'];
            $quoteFinanceData['group_id'] = $params['group_id'];
            $quoteFinanceData['payment_type'] = $params['payment_type'];
            $quoteFinanceData[$params['group_id']]['mileage'] = $params['mileage'];
            $quoteFinanceData[$params['group_id']]['deposit'] = $params['deposit'];
            $quoteFinanceData[$params['group_id']]['term'] = $params['term'];
            $quoteFinanceData[$params['group_id']]['depositMultiple'] = $params['depositMultiple'];
            $quoteFinanceData[$params['group_id']]['maintenance'] = $params['maintenance'];
            $quoteFinanceData[$params['group_id']]['method'] = $params['method'];
            $quoteFinanceData[$params['group_id']]['group_id'] = $params['group_id'];
            $quoteFinanceData[$params['group_id']]['payment_type'] = $params['payment_type'];
            $quoteFinanceData[$params['group_id']]['balloonPercentage'] = $params['balloonPercentage'];

            $quoteItem->setData('finance_data', $helperAll->jsonEncode($quoteFinanceData));

            $reordered = Mage::getSingleton('adminhtml/session_quote')->getReordered();
            Mage::getSingleton('adminhtml/session')->setData("quote_finance_data_{$reordered}", $quoteFinanceData);

            $this->_forward('recalculatePartExchangeAndFinances', 'order_amend_create');
        } catch (Exception $e) {
            Mage::logException($e);
            $response = [
                'status' => self::STATUS_ERROR,
                'success' => null,
                'error' => $e->getMessage()
            ];

            $this->sendJson($response);
        }
    }
}
