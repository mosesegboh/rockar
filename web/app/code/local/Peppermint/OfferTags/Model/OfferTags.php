<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Model_OfferTags extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'peppermint_offertags';
    protected $_allowedImageExtensions = ['jpg', 'jpeg', 'gif', 'png', 'swf'];

    private $_helper;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('peppermint_offertags/offerTags');
        $this->_helper = Mage::helper('peppermint_offertags');
    }


    /**
     * @return Mage_Core_Controller_Request_Http
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }

    /**
     * method to process image upload for icons
     *
     * @param $field
     * @param string $fileNamePrefix
     *
     * @return $this
     */
    protected function _processImageUpload($field, $fileNamePrefix = '')
    {
        $request = $this->_getRequest();

        if (isset($_FILES[$field]['name']) && $_FILES[$field]['name'] != '') {
            $uploader = new Varien_File_Uploader($field);

            $uploader->setAllowedExtensions($this->_allowedImageExtensions);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowRenameFiles(true);

            $mediaPath = $this->_helper->getIconsBaseDir() .
                $this->_helper->getIconsDir();

            // Upload the image
            $uploader->save($mediaPath, $fileNamePrefix . $_FILES[$field]['name']);

            $data[$field] = $this->_helper->getIconsDir() . $uploader->getUploadedFileName();

            // Set thumbnail name
            $this->setData($field, $data[$field]);
        } else {
            $data = $request->getPost();

            if (isset($data[$field]['delete']) && $data[$field]['delete']) {
                $this->setData($field, null);
                $fileBasePath = $this->_helper->getIconsBaseDir();

                if (isset($data[$field]['value']) && file_exists($fileBasePath . $data[$field]['value'])) {
                    unlink($fileBasePath . $data[$field]['value']);
                }
            } else {
                if (isset($data[$field]) && isset($data[$field]['value'])) {
                    $this->setData($field, $data[$field]['value']);
                }
            }
        }

        return $this;
    }

    /**
     * _beforeSave
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $this->_processImageUpload('icon');

        return parent::_beforeSave();
    }

    /**
     * _afterDelete
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterDelete()
    {
        $fileBasePath = $this->_helper->getIconsBaseDir();

        if ($this->getData('icon') && file_exists($fileBasePath . $this->getData('icon'))) {
            unlink($fileBasePath . $this->getData('icon'));
        }

        return parent::_afterDelete();
    }
}
