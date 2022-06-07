<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Alexander Metzgen <alexander.metzgen@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Observer extends Rockar_Customer_Model_Observer
{
    /**
     * Dealer ID cookie
     */
    const DEALER_ID_COOKIE_NAME = 'user';

    /**
     * Updates user attribute if first login happened in store and sets flag for GTM
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function setRegisteredIn($observer)
    {
        $storeCode = Mage::getModel('core/cookie')->get(self::IN_STORE_COOKIE_NAME);
        $customer = $observer->getCustomer();
        $customerHelper = Mage::helper('peppermint_customer/data');

        /** Check if logging in from store */
        if ($storeCode) {
            $store = Mage::helper('peppermint_localstores/data')->getStoreFromCode($storeCode);

            if ($store) {
                $customer->setRegisteredIn($customerHelper::REGISTERED_IN_STORE)
                    ->setInStoreName($store->getName())
                    ->setInStoreId($store->getId())
                    ->save();
            }
        } else {
            $customer->setRegisteredIn($customerHelper::REGISTERED_ONLINE)
                ->save();
        }

        // Used for GTM
        $sessionModel = Mage::getModel('customer/session');
        $sessionModel->setCustomerPage($this->_getCustomerPage());
        $sessionModel->setCustomerRegistered(true);
    }

    /**
     * Updates store dealer id attribute if first login happened in store
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function setStoreDealerId($observer)
    {
        $dealerId = Mage::helper('peppermint_all')->getDealerId();
        $customer = $observer->getCustomer();

        $customer->setDealerId($dealerId)
            ->save();
    }

    /**
     * @param $observer
     * @throws Exception
     * @return void
     */
    public function sendToS3($observer) {
        $enabled = Mage::getStoreConfig('rockar_customer/documents/documents_send_to_s3');

        if ($enabled == 1) {
            $coms = Mage::helper('peppermint_customer/communication');

            $toSave = $observer['document_order_id']
                ? Mage::getModel('peppermint_customer/document_order')->load($observer['document_order_id'])
                : Mage::getModel('peppermint_customer/document_order')->copyFromObserver($observer);

            if (!$toSave) {
                return; //here we have no order associated we should wait till an active order with this document is created.
            }

            $notStarted = Mage::getModel('peppermint_customer/document_upload_status')
                ->getStatusByName(Peppermint_Customer_Model_Document_Upload_Status::NOT_STARTED)
                ->getId();
            $completed = Mage::getModel('peppermint_customer/document_upload_status')
                ->getStatusByName(Peppermint_Customer_Model_Document_Upload_Status::COMPLETED)
                ->getId();
            $toSave->setDocumentOrderUploadStatusId($notStarted);
            $toSave->setDateUpdated(date('Y-m-d H:i:s'));
            $resource = $toSave->getResource();
            $resource->beginTransaction();

            try {
                $resource->save($toSave);
                $resource->commit();
                $dataToSend = Mage::helper('peppermint_customer/data')
                    ->getDetailsForFinancialServices($toSave['document_id']);

                if ($dataToSend) {
                    $coms->sendToS3(
                        false,
                        $observer['path'],
                        $observer['file'],
                        $dataToSend->getData()
                    );
                }

                $toSave->setDocumentOrderUploadStatusId($completed);
                $toSave->setDateUploaded(date('Y-m-d H:i:s'));
            } catch (Exception $e) { //if an error is raised rollback to not started
                $toSave->setDocumentOrderUploadStatusId($notStarted);
                Mage::log($e->getMessage());
            } finally {
                $toSave->save();
            }
        }
    }
}
