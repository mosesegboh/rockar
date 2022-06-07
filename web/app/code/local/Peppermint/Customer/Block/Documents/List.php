<?php
/**
 * @category    Peppermint
 * @package     Peppermint_Customer
 * @author      Krists Dadzitis <techteam@rockar.com>
 * @copyright   Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Documents_List extends Rockar_Customer_Block_Documents_List
{
    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getDownloadUrl($documentId)
    {
        return $this->getUrl('rockar_customer/documents/download', [
            'entity_id' => $documentId,
            '_secure' => $this->_isSecureConnection(),
            'form_key' => $this->_getFormKey()
        ]);
    }

    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getEditUrl($documentId)
    {
        return $this->getUrl('rockar_customer/documents/edit', [
            '_secure' => $this->_isSecureConnection(),
            'form_key' => $this->_getFormKey()
        ]);
    }

    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getRemoveUrl($documentId)
    {
        return $this->getUrl('rockar_customer/documents/delete', [
            'entity_id' => $documentId,
            '_secure' => $this->_isSecureConnection(),
            'form_key' => $this->_getFormKey()
        ]);
    }

    /**
     * get the document types as defined in the helper class.
     *
     * @return mixed
     */
    public function getDocumentTypes()
    {
        return Mage::helper('peppermint_customer')->getDocumentTypes();
    }

    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getSaveUrl()
    {
        $urlParams = [
            '_secure' => $this->_isSecureConnection(),
            'form_key' => $this->_getFormKey()
        ];

        if ($documentId = $this->getRequest()->getParam('entity_id', null)) {
            $urlParams['entity_id'] = $documentId;
        }

        return $this->getUrl('rockar_customer/documents/save', $urlParams);
    }

    /**
     * Generate a url required to retrieve the documents from the server.
     *
     * @Return string url required to load the documents.
     */
    public function getLoadDocumentsUrl()
    {
        $urlParams = [
            '_secure' => $this->_isSecureConnection(),
            'form_key' => $this->_getFormKey()
        ];

        return $this->getUrl('rockar_customer/documents/getDocuments', $urlParams);
    }

    /**
     * Helper method to get the form key.
     *
     * @return mixed form key for security reasons.
     */
    private function _getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }

    /**
     * selected finance group and finance type based on the first order in the list.
     *
     * @return mixed array containing the default data to load.
     * @throws Exception
     */
    public function getDefaultSelectedItems()
    {
        $items = Mage::helper('peppermint_customer')->getDefaultDocumentList($this->_getCustomer()->getId());

        $toReturn = [
            'customerType' => null,
            'financeGroup' => null,
            'documents' => []
        ];

        foreach ($items as $item) {
            $toReturn['documents'][] = [
                'documentType' => $item['documentTypeName'],
                'documentId' => $item['documentId'],
                'financeType' => $item['financeType'],
                'documentTypeId' => $item['documentTypeId'],
                'allowMultipleUploads' => $item['allowMultipleUploads'],
                'dateUploaded' => (new DateTime($item['dateUploaded']))->format('d/m/Y'),
                'documentName' => $item['documentName']
            ];
        }

        $tmp = $items->getFirstItem();

        if($tmp)
        {
            $toReturn['customerType'] = ['title' => $tmp['customerName'], 'value' => $tmp['customerId']];
            $toReturn['financeGroup'] = ['title' => $tmp['groupName'], 'value' => $tmp['groupId']];
        }

        return Mage::helper('rockar_all')->jsonEncode($toReturn);
    }

    /**
     * Returns the maximum file upload size
     *
     * @return integer
     */
    public function getMaxFileUploadSize()
    {
        return Mage::helper('peppermint_customer')->getMaxFileSize();
    }
}
