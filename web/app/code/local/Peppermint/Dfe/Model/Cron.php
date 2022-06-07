<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Model_Cron
{
    /**
     * Pulls reference codes and bank branches from DFE and synchronizes the local table.
     *
     * @return void
     */
    public function pullDfeData()
    {
        /** @var Peppermint_Dfe_Helper_Data $dfeHelper */
        $dfeHelper = Mage::helper('peppermint_dfe');

        $dfeHelper->pullRefCodes();
        $dfeHelper->pullBankBranches();
    }

    /**
     * Resend order to credit api.
     *
     * @return void
     */
    public function reSend()
    {
        // needs to set admin store and mimic admin user session for order status/comments update
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        Mage::getSingleton('admin/session')->setUser(Mage::getModel('admin/user')->setUsername('cron'));

        $resendOrders = Mage::getModel('peppermint_dfe/resend_order')->getCollection();
        $cronMaxSend = (int) Mage::getStoreConfig('peppermint_dfe/cron/cron_limit');

        /** @var Peppermint_Dfe_Model_Resend_Order $resendOrder */
        foreach ($resendOrders as $resendOrder) {
            $order = Mage::getModel('sales/order')->load($resendOrder->getOrderId());

            if (Mage::helper('peppermint_dfe/SubmitApp')->submit($order)) {
                continue;
            }

            if ($resendOrder->getErrorCount() >= $cronMaxSend - 1) {
                $this->_sendMail($order->getIncrementId(), $cronMaxSend);
            }
        }
    }

    /**
     * Alert email, sent for each order individually.
     *
     * @param string $incrementId
     * @param integer $cronMaxSend
     *
     * @return $this
     */
    protected function _sendMail($incrementId, $cronMaxSend)
    {
        $adminEmail = Mage::getStoreConfig('trans_email/ident_general/email');

        Mage::getModel('core/email')->setToEmail($adminEmail)
            ->setBody(
                'Credit app send errors has exceeded your limit of ' . $cronMaxSend
                . ' to send order with id: ' . $incrementId
            )
            ->setSubject('DFE Send api errors:')
            ->setFromEmail($adminEmail)
            ->setFromName(Mage::getStoreConfig('trans_email/ident_general/name'))
            ->setType('html')
            ->send();

        return $this;
    }
}
