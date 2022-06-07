<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Documents extends Rockar_Customer_Model_Documents
{
    /**
     * Upload File. Additionally save initial filename in separate field if available
     *
     * @param array      $data
     * @param bool|false $isUpdate
     * @param bool|false $isAdminRequest
     * @param bool|true  $isHttpRequest
     *
     * @return bool
     * @throws Exception
     * @throws Mage_Exception
     */
    public function upload(array $data, $isUpdate = false, $isAdminRequest = false, $isHttpRequest = true)
    {
        $date = date("Y-m-d H:i:s");
        $customer = $this->getCustomer($isAdminRequest);
        $filePath = $this->getDocumentsFolderPath();
        $error = [];

        if (isset($data['title'])) {
            $data['title'] = htmlentities($data['title']);
        }

        if (!isset($data['title']) || empty(trim($data['title']))) {
            $error[] = Mage::helper('rockar_customer')->__('Document Name cannot be empty.');
        }

        if (strlen($data['title']) > 255) {
            $error[] = Mage::helper('rockar_customer')->__('Document name is to long');
        }

        if (!is_dir($filePath)) {
            mkdir($filePath);
        }

        $filePath .= DS . $customer->getId();
        if (!is_dir($filePath)) {
            mkdir($filePath);
        }

        $key = $customer->getEmail() . self::SALT . $date;

        if ($isHttpRequest) {
            $adapter = new Zend_File_Transfer_Adapter_Http();
        } else {
            $adapter = new Zend_File_Transfer_Adapter_Registry();
        }

        $adapter->setDestination($filePath);

        $files = $adapter->getFileInfo();
        $firstFile = array_shift($files);

        if (isset($firstFile['type']) && $firstFile['type']
            && !in_array($firstFile['type'], $this->_allowedFileTypes)
        ) {
            $error[] = Mage::helper('rockar_customer')->__('This file type is restricted.');
        }

        $validator = Mage::getModel('rockar_customer/documents_validator');
        if (isset($firstFile['type']) && !$validator->isValid($firstFile['tmp_name'])) {
            $error[] = Mage::helper('rockar_customer')->__('This file was identified as malicious.');
        }

        if (!empty($error)) {
            return implode('<br/>', $error);
        }

        $filename = $this->getFilename();
        $this->addData($data);

        if (isset($firstFile['name']) && !empty($firstFile['name'])) {
            if ($isUpdate) {
                unlink($filePath . DS . $this->getFilename());
            }

            if ($sizeLimitError = $this->_isFileSizeExceeded($firstFile)) {
                return $sizeLimitError;
            }

            $filename = $firstFile['name'] ? $firstFile['name'] : $this->getFilename();
            $this->setInitialFilename(filter_var($filename, FILTER_SANITIZE_STRING));
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $adapter->addFilter('Rename', ['target' => $filePath . DS . $filename, 'overwrite' => true]);

            $filename = htmlentities($filename);

            $filter = Mage::getModel('rockar_customer/filter_encrypt');
            $filter->setKey($key);
            $adapter->addFilter($filter);

            $adapter->receive();

            $this->setDate($date);
            $this->setFilename($filename);
        }

        $this->setCustomerId($customer->getId());
        $this->setCustomerEmail($customer->getEmail());
        $oldData = $this->getOrigData();
        $documentHelper =  Mage::helper('rockar_customer/documents');

        if ($filename && is_readable($filePath . DS . $filename)) {
            $this->save();

            /**
             * Set new email notifications for NEW documents.
             */
            if ($this->isObjectNew() || ($filename != $oldData['filename'])) {
                $this->sendEmailNotification(
                    self::EMAIL_NOTIFICATION_TYPE_ADMIN,
                    $isAdminRequest
                );

                if (!$isAdminRequest) {
                    $this->setStatus($documentHelper::STATUS_PROCESSING);
                }

                $this->save();

                if ($this->getShowInFrontend()
                    && $documentHelper->isEmailNotificationEnabled()
                ) {
                    $this->sendEmailNotification(
                        $isAdminRequest ? self::EMAIL_NOTIFICATION_TYPE_NEW_ADMIN : self::EMAIL_NOTIFICATION_TYPE_NEW,
                        $isAdminRequest
                    );
                }
            }
        } else {
            $error = Mage::helper('rockar_customer')->__('Cannot save document. File is missing on the server.');
        }

        return $error;
    }
}
