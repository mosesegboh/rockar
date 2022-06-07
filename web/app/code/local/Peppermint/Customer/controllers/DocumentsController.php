<?php

/**
 * @category    Peppermint
 * @package     Peppermint_Customer
 * @author      Krists Dadzitis <techteam@rockar.com>
 * @copyright   Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar_Customer'). DS .'DocumentsController.php';

class Peppermint_Customer_DocumentsController extends Rockar_Customer_DocumentsController
{
    /**
     * Rewrite of parent function to add form key
     *
     * {@inheritDoc}
     */
    public function deleteAction()
    {
        if (!$this->checkFormKey()) {
            return;
        }

        $customer = $this->_getCustomerSession()->getCustomer();
        $id = $this->getRequest()->getParam('entity_id', false);
        $downloadModel = Mage::getModel('rockar_customer/documents');
        $downloadModel->load($id);
        $filename = $downloadModel->getFilename();

        try {
            if ($downloadModel->getCustomerId() == $customer->getId()) {
                $filePath = Mage::getBaseDir('media') . DS . Rockar_Customer_Model_Documents::DOCUMENTS_FOLDER . DS . $customer->getId() . DS . $filename;

                if ($filename && file_exists($filePath)) {
                    unlink($filePath);
                }

                $downloadModel->delete();
            } else {
                $this->getSession()->addError($this->__('You are not authorized to perform this action.'));
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            $this->getSession()->addError($this->__('There was an error during document removal process.'));
        }

        $this->getDocumentsAction();
    }

    /**
     * Rewrite of parent function to add form key
     *
     * {@inheritDoc}
     */
    public function editAction()
    {
        if (!$this->checkFormKey()) {
            return;
        }

        if ($id = $this->getRequest()->getParam('entity_id', false)) {
            $downloadModel = Mage::getModel('rockar_customer/documents')->load($id);
            Mage::register('current_document', $downloadModel);
        }

        return $this->sendJson([
            'documentTypeId' => $downloadModel->getData('document_type_id'),
            'title' => $downloadModel->getData('title')
        ]);
    }

    /**
     * Rewrite of parent function to add form key and provide initial file name if exists
     *
     * {@inheritDoc}
     */
    public function downloadAction()
    {
        if (!$this->checkFormKey()) {
            return;
        }

        $customer = $this->_getCustomerSession()->getCustomer();
        $id = $this->getRequest()->getParam('entity_id', false);
        $documentsModel = Mage::getModel('rockar_customer/documents');
        $documentsModel->load($id);
        $content = false;

        if ($documentsModel->getId() && $documentsModel->getCustomerId() == $customer->getId()) {
            $documentsModel->setCustomer($customer);
            $content = $documentsModel->download();

            if (!$content) {
                $this->getSession()->addError($this->__('File is missing on the server.'));
            }
        }

        if ($content) {
            $this->_prepareDownloadResponse(
                $documentsModel->getInitialFilename() ?: $documentsModel->getFilename(),
                $content
            );
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Rewrite of parent function to allow for ajax requests
     * and responses.
     *
     * {@inheritDoc}
     */
    public function saveAction()
    {
        if (!$this->checkFormKey()) {
            return;
        }

        $customer = $this->_getCustomerSession()->getCustomer();
        $data = array_map('trim', $this->getRequest()->getPost());
        $isUpdate = false;
        $documentsModel = Mage::getModel('rockar_customer/documents');

        if ($documentId = $this->getRequest()->getParam('entity_id', null)) {
            $isUpdate = true;
            $documentsModel->load($documentId);

            if (!$documentsModel->getId() || $customer->getId() !== $documentsModel->getCustomerId()) {
                $this->getSession()->addError($this->__('You are not authorized to perform this action.'));
                $this->_redirectReferer();
                return;
            }
        }

        /** @var $documentsHelper Rockar_Customer_Helper_Documents */
        $documentsHelper = Mage::helper('rockar_customer/documents');
        $customerId = $customer->getId();

        if ($documentsHelper->isUploadAllowed($customerId)) {
            $documentsModel->setCustomer($customer);
            $documentsModel->setShowInFrontend(1);
            $documentsModel->setDocumentFinanceTypeId($data['documentFinanceType']);
            $documentsModel->setDocumentTypeId($data['documentType']);

            try {
                if ($error = $documentsModel->upload($data, $isUpdate)) {
                    $this->getSession()->addError($error);
                } else {
                    $documentsHelper->logFileUpload($customerId);
                }

                if (Mage::helper('peppermint_checkout/customer')->getCustomerActiveOrders()) {
                    Mage::dispatchEvent('peppermint_document_upload_success', [
                        'document_id' => $documentsModel->getId(),
                        'customer_id' => $customerId,
                        'file' => $documentsModel->download(),
                        'path' => $customer->getId() . '/' . $documentsModel->getFilename(),
                        'document_order_upload_status_id' => 1
                    ]);
                }
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->getSession()->addError($this->__('There was an error during document upload.'));
            }
        } else {
            $cmsErrorMessage =
                Mage::getModel('cms/block')->load(Rockar_Customer_Helper_Documents::CMS_BLOCK_CUSTOMER_UPLOAD_LIMIT_ERROR)
                    ->getContent();
            $this->getSession()->addError($this->__($cmsErrorMessage));
        }

        $this->getDocumentsAction();
    }

    /**
     * sends json object to the client, informing it how to render the page.
     * @return void
     */
    public function getDocumentsAction()
    {
        $variables = $this->getRequest()->getParams();
        $documents = Mage::helper('peppermint_customer')
            ->getDocumentDisplayGrid(
                $variables['customerGroup'],
                $variables['financeGroup'],
                $this->_getCustomerSession()->getCustomer()->getEntityId()
            );
        $toReturn = [];

        foreach ($documents as $document) {
            $toReturn[] = [
                'groupName' => $document['groupName'],
                'documentType' => $document['documentTypeName'],
                'customerName' => $document['customerName'],
                'documentId' => $document['documentId'],
                'financeType' => $document['financeType'],
                'documentTypeId' => $document['documentTypeId'],
                'allowMultipleUploads' => $document['allowMultipleUploads'],
                'dateUploaded' => (new DateTime($document['dateUploaded']))->format('d/m/Y'),
                'documentName' => $document['documentName']
            ];
        }

        $this->sendJson($toReturn);
    }

    /**
     * Check if form key matches the one generated in session
     *
     * @return bool
     */
    protected function checkFormKey() {
        if (!$this->_validateFormKey()) {
            $this->getSession()->addError($this->__('Error. Invalid form key.'));
            $this->_redirectReferer();

            return false;
        }

        return true;
    }
}
