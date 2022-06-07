<?php
/**
 * @category    Peppermint
 * @package     Peppermint_Customer
 * @author      Krists Dadzitis <techteam@rockar.com>
 * @copyright   Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Documents_Form extends Rockar_Customer_Block_Documents_Form
{
    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getFormAction()
    {
        $urlParams = [
            '_secure' => $this->_isSecureConnection(),
            'form_key' => $this->getFormKey()
        ];

        if ($documentId = $this->getRequest()->getParam('entity_id', null)) {
            $urlParams['entity_id'] = $documentId;
        }

        return $this->getUrl('rockar_customer/documents/save', $urlParams);
    }

    /**
     * Get document types required for this form
     *
     * @Return mixed json of the document types.
     */
    public function getDocumentTypes()
    {
        return  Mage::helper('rockar_all')->jsonEncode(
            Mage::helper('peppermint_customer')->getDocumentTypes()
        );
    }

    /**
     * get the url that will allow saving.
     *
     * @Return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('rockar_customer/documents/save', [
            '_secure' => $this->_isSecureConnection(),
            'form_key' => $this->getFormKey()
        ]);
    }

    /**
     * Return the url that is required for this user to edit the document
     *
     * @Return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('rockar_customer/documents/edit', [
            '_secure' => $this->_isSecureConnection(),
            'form_key' => $this->getFormKey()
        ]);
    }

    /**
     * helper method to grab the form key
     *
     * @Return mixed
     */
    public function getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }
}
