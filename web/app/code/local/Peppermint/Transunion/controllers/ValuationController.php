<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transunion
 * @author    Osama Ahmed <osama.ahmed@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar2_PartExchange') . DS . 'ValuationController.php';

class Peppermint_Transunion_ValuationController extends Rockar2_PartExchange_ValuationController
{
    /**
     * indexAction
     *
     * {@inheritDoc}
     */
    public function indexAction()
    {
        $helper = Mage::helper('rockar_partexchange');
        $mileage = $this->_getMileage();
        $partExchange = $helper->loadPartExchangeFromSession();
        $productId = $this->getRequest()->getParam('productId', false);

        if ($mileage) {
            // Rewrite here | set flag if the mileage has change
            $partExchange->setData('has_mileage_change', ($mileage !== $partExchange->getMileage()));
            $partExchange->setData('mileage', $mileage);
        }

        try {
            $helper->getValuation($partExchange);
            $helper->savePartExchangeToSession($partExchange);
            $this->setResponseHttpStatusCodeOk();

            if ($productId && $productId > 0) {
                Mage::dispatchEvent('rockar_partexchange_set_px_future_value', [
                    'part_exchange' => $partExchange,
                    'product_id' => $productId
                ]);
            }

            Mage::dispatchEvent('rockar_partexchange_additional_data_set', [
                'part_exchange' => $partExchange
            ]);

            if ($partExchange->getUpdatedPartExchangeValue() !== 0 && ($total = $partExchange->getTotals()['total'] ?? false)) {
                $partExchange->setUpdatedPartExchangeValue($total);
            }

            $result = $partExchange->getData();
            $result['formatExpireDate'] = Mage::helper('core')->formatDate(date("Y-m-d H:i:s", strtotime($partExchange->getTimestamp())), 'short', false);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->setResponseHttpStatusCodeNotFound();
            $result['errorMessage'] = $this->__('There has been an issue processing your request');
        }

        Mage::dispatchEvent('rockar_partexchange_report_valuation', [
            'part_exchange' => $partExchange,
            'status' => Rockar_PartExchange_Model_Reports::STATUS_VALUATED,
        ]);

        $this->sendJson($result);
    }
}

