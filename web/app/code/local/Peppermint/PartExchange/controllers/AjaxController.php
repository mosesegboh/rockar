<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_AjaxController extends Rockar_All_Controller_Front_Ajax
{
    /**
     * Sets in Customer session state of trade-in
     */
    public function updatePxStateAction()
    {
        try {
            $currentState = $this->getRequest()->getParam('currentState');
            Mage::getSingleton('customer/session')
                ->setData(Mage::helper('peppermint_partexchange')->getPxStateSessionKey(), $currentState);
            $result = [
                'success' => true
            ];
        } catch (Mage_Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Mage::logException($e);
            $result = [
                'success' => false,
                'message' => $this->__('There was an error while updating current state'),
            ];
        }

        $this->sendJson($result);
    }
}
