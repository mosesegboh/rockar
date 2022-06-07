<?php
/**
 * @category        Peppermint
 * @package         Peppermint\Customer
 * @author          Craig Goodspeed <techteam@rockar.com>
 * @copyright       Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 * @description     Cron job to pick up failed uploads as well as the scenario when a document is uploaded but
 *                  no order has been placed.
 */

class Peppermint_Customer_Model_Cron
{
    /**
     * Entry point for the cron job, 2 actions are performed
     * 1.   In the scenario a customer uploads a document without an active order, this will upload these documents
     *      when the order becomes available.
     * 2.   Documents that tried and failed to upload, have a status of not started. This process will complete the loop
     *      and upload these documents again.
     */
    public function run()
    {
        if (!Mage::getStoreConfig('rockar_customer/documents/documents_send_to_s3')) {
            return;
        }

        $toAction = $this->getCustomersWithUnlinkedDocuments();
        foreach ($toAction as $actionMe) {
            $this->actionCustomerDocuments($actionMe['customer_id'], explode(',',$actionMe['document_ids']));
        }

        $documentOrderCollection = $this->getDocumentsNotUploaded();
        foreach ($documentOrderCollection as $documentOrder) {
            $this->doUpload($documentOrder['document_id'], $documentOrder['customer_id'], $documentOrder['order_id'], $documentOrder['document_order_id']);
        }
    }

    /**
     * Get a list of documents that have not been uploaded, items that are in a not started state.
     *
     * @return object collection of documents to be uploaded to S3
     */
    private function getDocumentsNotUploaded()
    {
        $notStarted = Mage::getModel('peppermint_customer/document_upload_status')
                ->getStatusByName(Peppermint_Customer_Model_Document_Upload_Status::NOT_STARTED)
                ->getId();
        $collection = Mage::getModel('rockar_customer/documents')->getCollection();
        $collection->getSelect()
            ->join(
                ['doc_order' => $collection->getTable('peppermint_customer/document_order')],
                'doc_order.document_id = main_table.entity_id'
            )
            ->where('doc_order.upload_status_id = ?', $notStarted)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns([
                'customer_id' => 'main_table.customer_id',
                'order_id' => 'doc_order.order_id',
                'document_id' => 'main_table.entity_id',
                'document_order_id' => 'doc_order.id'
            ]);

        return $collection;
    }

    /**
     * Retrieve a list of documents that have not yet been associated with an order.
     * Should the document be uploaded before there is an active order.
     * This process will associate and upload the documents.
     *
     * @return mixed
     */
    private function getCustomersWithUnlinkedDocuments()
    {
        $collection = Mage::getModel('rockar_customer/documents')->getCollection();
        $collection->getSelect()
            ->join(
                ['sfo' => $collection->getTable('sales/order')],
                'sfo.customer_id = main_table.customer_id'
            )
            ->joinLeft(['pdocs' => $collection->getTable('peppermint_customer/document_order')],
                'main_table.entity_id = pdocs.document_id'
            )
            ->join(
                ['rfo' => $collection->getTable('rockar_financingoptions/options')],
                'rfo.type = sfo.finance_payment_type'
            )
            ->join(
                ['pcdft' => $collection->getTable('peppermint_customer/document_finance_type')],
                'main_table.document_finance_type_id = pcdft.id'
            )
            ->join(
                ['pcdcg' => $collection->getTable('peppermint_customer/document_customer_group')],
                'pcdft.applicable_to_customer_group_id = pcdcg.id'
            )
            ->join(
                ['pcdfg' => $collection->getTable('peppermint_customer/document_finance_group')],
                'pcdfg.id = pcdft.finance_group_id'
            )
            ->where('pdocs.id is null')
            ->where('main_table.document_finance_type_id is not null')
            ->where('main_table.document_type_id is not null')
            ->where('rfo.pay_in_full = pcdfg.is_pay_in_full')
            ->where('rfo.is_business = pcdcg.is_company')
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                [
                    'customer_id' => 'main_table.customer_id',
                    'document_ids' => 'group_concat(main_table.entity_id)'
                ]
            )
            ->group(['main_table.customer_id']);

        return $collection->addFieldToFilter('sfo.state', ['nin' => Peppermint_Customer_Helper_Data::ORDER_STATES]);
    }

    /**
     * This document needs to be associated with an order.
     * the first active order(newest order) is retrieved based on the customer id
     *
     * @param $customerId Integer customer identifier.
     * @param $documentIds array collection of integers that represent document identities of already uploaded documents.
     * @throws Exception
     */
    private function actionCustomerDocuments($customerId, $documentIds)
    {
        $lastOrder = Mage::helper('peppermint_customer/data')->getFirstOrderByCustomer($customerId)->getFirstItem()->toArray();
        
        foreach ($documentIds as $documentId) {
            $document = Mage::getModel('peppermint_customer/document_order');
            $document->setDocumentId($documentId);
            $document->setOrderId($lastOrder['order_id']);
            $document->setDateUpdated(date('Y-m-d H:i:s'));
            $document->setDateCreated(date('Y-m-d H:i:s'));
            $document->setUploadStatusId(1);
            $document->save();
            $this->doUpload($documentId, $customerId, $document->getOrderId(), $document->getId());
        }
    }

    /**
     * Given a document id, customer id and an order id
     * Send the object to s3
     *
     * @param $documentId Integer identifier within the rockar_customer_document table
     * @param $customerId Integer identifies the customer.
     * @param $orderId Integer Order identity
     */
    private function doUpload($documentId, $customerId, $orderId, $documentOrderId) {
        $rockarDocument = Mage::getModel('rockar_customer/documents');
        $documentsModel = $rockarDocument->load($documentId);
        $documentToSend = $documentsModel->download(true);

        if ($documentToSend) {
            Mage::getSingleton('peppermint_customer/observer')->sendToS3(
                [
                    'document_id' => $documentId,
                    'customer_id' => $customerId,
                    'file' => $documentToSend,
                    'path' => $customerId . '/' . $documentsModel->getFilename(),
                    'document_order_upload_status_id' => 1,
                    'order_id' => $orderId,
                    'document_order_id' => $documentOrderId
                ]
            );
        }
    }
}
