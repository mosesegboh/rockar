<?php
/**
 * @category     Peppermint
 * @package      Peppermint\Customer
 * @author       Craig Goodspeed <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Document_Order extends Mage_Core_Model_Abstract
{
    /**
     * Magento post __construct init.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_customer/document_order');
    }

    /**
     * Return the most recent order by customer and optionally document id
     *
     * @param $customerId integer|string customer to whom the order belongs.
     * @param null $documentId integer|string  document identity that does not have an order associated.
     *
     * @return mixed order to associate this document with.
     */
    private function _getFirstOrderDetailsByCustomer($customerId, $documentId = null)
    {
        return Mage::helper('peppermint_customer')->getFirstOrderByCustomer($customerId, $documentId)->getData();
    }

    /**
     * Create this class from the given data within the observer object.
     *
     * @param $observerObject mixed contains required data to load an order for the relevant document.
     *
     * @return $this|false returns false when there are no orders, otherwise this class with order populated.
     */
    public function copyFromObserver($observerObject)
    {
        $this->setDocumentId($observerObject['document_id']);

        if (!$observerObject['order_id']) {
            $tmp = $this->_getFirstOrderDetailsByCustomer($observerObject['customer_id'], $this->getDocumentId());

            if (count($tmp) > 0) {
                $this->setOrderId($tmp[0]['order_id']);
            } else {
                return false;
            }
        } else {
            $this->setOrderId($observerObject['order_id']);
        }

        return $this;
    }

    /**
     * Setter for document upload status
     *
     * @param $status Integer | String status identifier.
     */
    public function setDocumentOrderUploadStatusId($status)
    {
        $this->setUploadStatusId($status);
    }

    /**
     * Getter for document upload status
     *
     * @return mixed document upload status id
     */
    public function getDocumentOrderUploadStatusId()
    {
        return $this->getUploadStatusId();
    }
}
