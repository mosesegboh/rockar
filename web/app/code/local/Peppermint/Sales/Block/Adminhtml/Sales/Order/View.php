<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Sales_Order_View extends Rockar_Sales_Block_Adminhtml_Sales_Order_View
{
    /**
     * @var Rockar_Sales_Helper_Data
     */
    protected $_salesHelper;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_coreHelper;

    /**
     * @var Peppermint_Sales_Model_Order
     */
    protected $_order;

    /**
     * @var array
     */
    protected $_btnSortOrderMapping = [
        'order_reorder' => 45,
        'order_creditmemo' => 55,
        'order_cancel' => 65,
        'order_amend_lock' => 85
    ];

    /**
     * Peppermint_Sales_Block_Adminhtml_Sales_Order_View constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->_salesHelper = Mage::helper('rockar_sales');
        $this->_coreHelper = Mage::helper('core');
        $this->_order = $this->getOrder();

        // Remove specific actions and recreate by order status
        $this->removeButton('pre_retail')
            ->removeButton('order_ship')
            ->removeButton('order_cancel')
            ->removeButton('order_amend_cancel')
            ->removeButton('send_notification')
            ->removeButton('order_creditmemo')
            ->removeButton('void_payment')
            ->removeButton('order_hold')
            ->removeButton('order_unhold')
            ->removeButton('accept_payment')
            ->removeButton('deny_payment')
            ->removeButton('get_review_payment_update');

        switch ($this->_order->getState()) {
            case $this->_order::STATE_PROCESSING:
                $this->_addBtnSendEmail();
                $this->_addBtnHold();

                if ($this->_order->getStatus() === $this->_order::STATUS_ORDER_PLACED) {
                    $this->_addBtnCancel();
                    $this->_addBtnPrepareInvoice();
                } else {
                    if ($this->_order->getStatus() === $this->_order::STATUS_INVOICE_SUBMITTED) {
                        $this->_addBtnPreRetail();
                        $this->_addBtnCancel();
                        $this->_addBtnOrderAmendLock();
                        $this->_addBtnCredit();

                        // Remove OA button if OA unlock has not been request/push
                        if (
                            Mage::helper('peppermint_orderamend')->getCanUnlockAmendEnabled()
                            && !$this->_isAmendAllowed()
                        ) {
                            $this->removeButton('order_reorder');
                        }
                    } else {
                        // Disable Order Amend
                        $this->removeButton('order_reorder');
                    }
                }
                break;
            case $this->_order::STATE_CLOSED:
            case $this->_order::STATE_COMPLETE:
                $this->_addBtnSendEmail();
                $this->removeButton('order_reorder');
                break;
            case $this->_order::STATE_HOLDED:
                $this->_addBtnSendEmail();
                $this->_addBtnUnHold();
                $this->_addBtnCancelAmendment();
                $this->removeButton('order_reorder');
                break;
            case $this->_order::STATE_NEW:
                $this->_addBtnCancel();
                $this->_addBtnSendEmail();
                $this->_addBtnHold();
                break;
        }

        $this->_addBtnSyncOrder();
        $this->_addBtnRefreshProduct();
        $this->_addBtnSendToSap();
        $this->_adjustButtonSorting();
    }

    /**
     * Change the sort type of previously created buttons.
     * Since by default sort order is assigned based on the count of the buttons,
     * once most of the buttons are deleted - magento will overwrite existing buttons if the sort order is the same for them.
     *
     * @return void
     */
    protected function _adjustButtonSorting(): void
    {
        // Change the default order of amend button to be not an increment of 10
        // So that it is not overwritten when creating next buttons
        foreach ($this->_btnSortOrderMapping as $btn => $sortOrder) {
            if (isset($this->_buttons[0][$btn]['sort_order'])) {
                $this->_buttons[0][$btn]['sort_order'] = $sortOrder;
            }
        }
    }

    /**
     * Get amendment page URL.
     *
     * @return string
     */
    private function _getAmendUrl()
    {
        return $this->getUrl('adminhtml/order_amend_create/amendPageInit');
    }

    /**
     * Add Pre-Retail button
     *
     * @return void
     */
    protected function _addBtnPreRetail()
    {
        if (
            $this->_isAllowedAction('invoice')
            && $this->_order->canShip()
            && $this->_isAllowedByRfsDate()
        ) {
            $this->_addButton('pre_retail', [
                'label'     => $this->_salesHelper->__('Pre-Retail'),
                'class'     => 'save submit-button',
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/sales_order/preretail') . '\')'
            ]);
        }
    }

    /**
     * Add Prepare Invoice button.
     *
     * @return void
     */
    protected function _addBtnPrepareInvoice()
    {
        if (
            $this->_isAllowedAction('prepare_invoice')
            && $this->_order->canInvoice()
            && $this->_isAllowedByRfsDate()
        ) {
            $confirmationMessage = $this->_coreHelper->jsQuoteEscape(
                $this->_salesHelper->__('Are you sure you want to Prepare Invoice for this order?')
            );
            $prepareInvoiceUrl = $this->getUrl('*/*/prepareInvoice');

            $this->_addButton(
                'invoice_submitted',
                [
                    'label' => $this->_salesHelper->__('Prepare Invoice'),
                    'onclick' => 'confirmSetLocation(\'' . $confirmationMessage . '\', \'' . $prepareInvoiceUrl . '\')'
                ]
            );
        }
    }

    /**
     * Add Hold button.
     *
     * @return void
     */
    protected function _addBtnHold()
    {
        if (
            $this->_isAllowedAction('hold')
            && $this->_order->canHold()
        ) {
            $this->_addButton(
                'order_hold',
                [
                    'label' => $this->_salesHelper->__('Hold'),
                    'onclick' => 'setLocation(\'' . $this->getHoldUrl() . '\')'
                ]
            );
        }
    }

    /**
     * Add Send Email button.
     *
     * @return void
     */
    protected function _addBtnSendEmail()
    {
        if ($this->_isAllowedAction('emails')) {
            $confirmationMessage = $this->_coreHelper->jsQuoteEscape(
                $this->_salesHelper->__('Are you sure you want to send order email to customer?')
            );
            $this->addButton(
                'send_notification',
                [
                    'label' => $this->_salesHelper->__('Send Email'),
                    'onclick' => "confirmSetLocation('{$confirmationMessage}', '{$this->getEmailUrl()}')"
                ]
            );
        }
    }

    /**
     * Add Cancel button.
     *
     * @return void
     */
    protected function _addBtnCancel()
    {
        if (
            $this->_isAllowedAction('cancel')
            && $this->_order->canCancel()
        ) {
            $confirmationMessage = $this->_coreHelper->jsQuoteEscape(
                $this->_salesHelper->__('Are you sure you want to cancel this order?')
            );
            $this->addButton(
                'order_cancel',
                [
                    'label' => $this->_salesHelper->__('Cancel'),
                    'onclick' => 'deleteConfirm(\'' . $confirmationMessage . '\', \'' . $this->getCancelUrl() . '\')'
                ]
            );
        }
    }

    /**
     * Add Credit Memo button.
     *
     * @return void
     */
    protected function _addBtnCredit()
    {
        if (
            $this->_isAllowedAction('creditmemo')
            && $this->_order->canCreditmemo()
        ) {
            $confirmationMessage = $this->_coreHelper->jsQuoteEscape(
                $this->_salesHelper->__('This will create an offline refund. To create an online refund, open an invoice and create credit memo for it. Do you wish to proceed?')
            );
            $onClick = $this->_order->getPayment()->getMethodInstance()->isGateway()
                ? "confirmSetLocation('{$confirmationMessage}', '{$this->getCreditmemoUrl()}')"
                : "setLocation('{$this->getCreditmemoUrl()}')";

            $this->_addButton(
                'order_creditmemo',
                [
                    'label' => $this->_salesHelper->__('Credit Memo'),
                    'onclick' => $onClick,
                    'class' => 'go'
                ]
            );
        }
    }

    /**
     * Add UnHold button.
     *
     * @return void
     */
    protected function _addBtnUnHold()
    {
        if (
            $this->_isAllowedAction('unhold')
            && $this->_order->canUnhold()
        ) {
            $this->_addButton(
                'order_unhold',
                [
                    'label' => $this->_salesHelper->__('Unhold'),
                    'onclick' => 'setLocation(\'' . $this->getUnholdUrl() . '\')'
                ]
            );
        }
    }

    /**
     * Add Cancel Amendment button.
     *
     * @return void
     */
    protected function _addBtnCancelAmendment()
    {
        if (
            Mage::getSingleton('admin/session')->isAllowed('sales/order/order_amend/confirm')
            && Mage::helper('rockar_orderamend/order')->isPendingAmendment($this->_order)
        ) {
            $this->_addButton(
                'order_amend_cancel',
                [
                    'label' => $this->_salesHelper->__('Cancel amendment'),
                    'onclick' => sprintf(
                        'setLocation(\'%s\')',
                        $this->getUrl('*/rockar_amendment/cancel', ['order_id' => $this->_order->getId()])
                    ),
                    'class' => 'delete'
                ]
            );
        }
    }

    /**
     * Check if invoice is allowed by the Ready for Sale date
     *
     * @return bool
     */
    protected function _isAllowedByRfsDate()
    {
        if ($vin = Mage::helper('peppermint_sales/order')->getOrderVinNumber($this->_order)) {
            $additionalData = Mage::getModel('peppermint_sales/additionalData')->getCollection()
                ->addFieldToFilter('vin', $vin)
                ->addFieldToFilter('rfs_date', ['lteq' => Mage::getModel('core/date')->date('Y-m-d')]);

            if ($additionalData->getSize()) {
                // There is a record with RFS date in the past, show invoice
                return true;
            }
        }

        return false;
    }

    /**
     * Check if order amend has been unlock for pre-invoice/invoice_submitted order status
     *
     * @return bool
     */
    protected function _isAmendAllowed()
    {
        if ($vin = Mage::helper('peppermint_sales/order')->getOrderVinNumber($this->_order)) {

            return Mage::getResourceModel('peppermint_sales/additionalData_collection')
                ->addFieldToFilter('vin', $vin)
                ->addFieldToFilter('can_amend', Peppermint_Sales_Model_AdditionalData::CAN_AMEND_UNLOCK)
                ->getSize() > 0;
        }

        return false;
    }

    /**
     * Add Sync with SPC button
     *
     * @return void
     */
    protected function _addBtnSyncOrder()
    {
        if ($this->_isAllowedAction('sync_order')) {
            $syncOrderUrl = $this->getUrl('*/*/syncOrder');
            $this->addButton(
                'sync_order',
                [
                    'label' => $this->_salesHelper->__('Sync to DFE'),
                    'onclick' => 'setLocation(\'' . $syncOrderUrl . '\')'
                ]
            );
        }
    }

    /**
     * Add refresh product data  button
     *
     * @return void
     */
    protected function _addBtnRefreshProduct()
    {
        if ($this->_isAllowedAction('refresh_product')) {
            $reloadUrl = $this->getUrl('*/*/refreshProduct');
            $this->addButton(
                'refresh_product',
                [
                    'label' => $this->_salesHelper->__('Refresh Order Product Data'),
                    'onclick' => 'setLocation(\'' . $reloadUrl . '\')'
                ]
            );
        }
    }

    /**
     * Add send to SAP button
     *
     * @return void
     */
    protected function _addBtnSendToSap()
    {
        if ($this->_isAllowedAction('send_order')) {
            $this->addButton(
                'send_order',
                [
                    'label' => $this->_salesHelper->__('Send Order to SAP'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('*/*/sendOrder') . '\')'
                ]
            );
        }
    }

    /**
     * Add Order Amend lock button
     *
     * @return void
     */
    protected function _addBtnOrderAmendLock()
    {
        if ($this->_isAllowedAction('order_amend_lock')) {
            $this->addButton(
                'order_amend_lock',
                [
                    'label' => $this->_salesHelper->__('Lock Order Amend'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('*/*/orderAmendLock') . '\')'
                ]
            );
        }
    }
}
