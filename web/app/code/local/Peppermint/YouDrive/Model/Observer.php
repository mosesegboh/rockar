<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Alexander Metzgen <alexander.metzgen@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Model_Observer
{
    protected $_resourceModel;
    protected $_readConnection;
    protected $_writeConnection;

    /**
     * Add booking placed instore or online data
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function saveBookingItem(Varien_Event_Observer $observer)
    {
        $booking = $observer->getDataObject();
        $bookingPlaced = $booking->getData('local_store_code') || Mage::getModel('core/cookie')->get(Rockar_All_Helper_Data::IN_STORE_COOKIE_NAME)
            ? 0
            : 1;
        $dealerId = '';
        $savedDealerId = $booking->getData('dealer_id');
        $newDealerId = Mage::helper('peppermint_all')->getDealerId();

        if (!empty($savedDealerId) || !empty($newDealerId)) {
            if ($savedDealerId) {
                $dealerId = $savedDealerId;
            } else {
                $dealerId = $newDealerId;
            }
        }

        $booking->addData([
            'booking_placed' => $bookingPlaced,
            'dealer_id'      => $dealerId
        ]);
    }

    /**
     * Init db connection models
     * @return void
     */
    protected function _initDbConnectionModels()
    {
        $this->_resourceModel = Mage::getSingleton('core/resource');
        $this->_readConnection = $this->_resourceModel->getConnection('core_read');
        $this->_writeConnection = $this->_resourceModel->getConnection('core_write');
    }

    /**
     * Create youdrive vehicle for imported Test Drive product
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function createImportedYoudriveVehicles(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()->getProductIds();

        if ($productIds) {
            $this->_initDbConnectionModels();

            $optionId = Mage::getResourceModel('catalog/product')->getAttribute('vehicle_condition')
                ->getSource()
                ->getOptionId(Peppermint_Catalog_Helper_Vehicle::CONDITION_TEST_DRIVE);

            $productCollection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(['vehicle_condition', 'local_store_code'])
                ->addFieldToFilter('entity_id', ['in'=> $productIds])
                ->addAttributeToFilter('vehicle_condition', $optionId);

            $table = $this->_resourceModel->getTableName('rockar_youdrive/vehicle');
            $statement = $this->_readConnection->select()
                ->from($table, ['product_id'])
                ->where('product_id IN (?)', $productIds);

            $existingVehicles = $this->_readConnection->fetchCol($statement);
            $importedYouDriveIds = [];

            foreach ($productCollection as $vehicle) {
                $vehicleId = $vehicle->getId();
                $importedYouDriveIds[] = $vehicleId;

                $data = [
                    'parent_id' => $vehicleId,
                    'product_id' => $vehicleId,
                    'assigned_to' => $vehicle->getData('local_store_code'),
                    'is_active' => 1,
                    'position' => 0,
                    'funded' => 0
                ];

                if (in_array($vehicleId, $existingVehicles)) {
                    $this->_writeConnection->update($table, $data, 'product_id = ' . $vehicleId);
                } else {
                    $this->_writeConnection->insert($table, $data);
                }
            }

            $this->_doYouDriveVehiclesCleanUp($existingVehicles, $importedYouDriveIds);
        }

        return $this;
    }

    /**
     * Adjust youdrive email configuration.
     * Add request confirmation email
     *
     * @param Varien_Event_Observer $observer
     */
    public function extendEmailConfiguration(Varien_Event_Observer $observer)
    {
        $emailConfiguraion = $observer->getEvent()
            ->getData('email_configuration');

        $this->removeEmailConfigurations($emailConfiguraion);

        $bookingConfiguration = $emailConfiguraion->getData(Peppermint_YouDrive_Model_Booking::EMAIL_TYPE_BOOKING_CONFIRMATION);
        $bookingConfiguration['send_copy_to_dealer'] = Peppermint_YouDrive_Model_Booking::XML_PATH_BOOKING_COPY_TO_DEALER;

        $cancelBookingConfiguration = $emailConfiguraion->getData(Peppermint_YouDrive_Model_Booking::EMAIL_TYPE_NOTIFICATION_CANCELLED);
        $cancelBookingConfiguration['send_copy_to_dealer'] = Peppermint_YouDrive_Model_Booking::XML_PATH_BOOKING_CANCELLED_COPY_TO_DEALER;

        $emailConfiguraion->addData(
            [
                Peppermint_YouDrive_Model_Booking::EMAIL_TYPE_REQUEST_CONFIRMATION => [
                    'enabled' => Peppermint_YouDrive_Model_Booking::XML_PATH_REQUEST_ENABLED,
                    'sender' => Peppermint_YouDrive_Model_Booking::XML_PATH_REQUEST_SENDER,
                    'copy_to' => Peppermint_YouDrive_Model_Booking::XML_PATH_REQUEST_COPY_TO,
                    'copy_method' => Peppermint_YouDrive_Model_Booking::XML_PATH_REQUEST_COPY_METHOD,
                    'template' => Peppermint_YouDrive_Model_Booking::XML_PATH_REQUEST_CONFIRMATION_TEMPLATE,
                    'send_copy_to_dealer' => Peppermint_YouDrive_Model_Booking::XML_PATH_REQUEST_COPY_TO_DEALER,
                ],
                Peppermint_YouDrive_Model_Booking::EMAIL_TYPE_BOOKING_CONFIRMATION => $bookingConfiguration,
                Peppermint_YouDrive_Model_Booking::EMAIL_TYPE_NOTIFICATION_CANCELLED => $cancelBookingConfiguration
            ]
        );

        return $this;
    }

    /**
     * Remove unnecessary youdrive email configurations.
     *
     * @param Varien_Object
     */
    public function removeEmailConfigurations($emailConfiguraions)
    {
        $emailConfiguraions->unsetData(Peppermint_YouDrive_Model_Booking::EMAIL_TYPE_NOTIFICATION_COMPLETE);
        $emailConfiguraions->unsetData(Peppermint_YouDrive_Model_Booking::EMAIL_TYPE_NOTIFICATION_MISSED);
        $emailConfiguraions->unsetData(Peppermint_YouDrive_Model_Booking::EMAIL_TYPE_NOTIFICATION_REMINDER);
    }

    /**
     * Remove old youdrive product and unlink simple from configurable that has been republised as test-drive product
     *
     * @param array $existingVehicles
     * @param array $importedYouDriveIds
     * @return void
     */
    protected function _doYouDriveVehiclesCleanUp(array $existingVehicles, array $importedYouDriveIds)
    {
        try {
            /**
             * Compile all the old youdrive products that has been re-publised as used or ex-demo product for deletion
             * If a recently imported product exists in the rockar_youdrive_vehicle table
             * But it is not in $productCollection then it has been re-publised as used or ex-demo product
             */
            $oldYouDriveVehicles = array_diff(
                // All the vehicles that was recently imported and also exists in the rockar_youdrive_vehicle table
                $existingVehicles,
                // All the youdrive vehicle that was imported or updated
                $importedYouDriveIds
            );

            if ($oldYouDriveVehicles) {
                $this->_writeConnection->delete(
                    $this->_resourceModel->getTableName('rockar_youdrive/vehicle'),
                    $this->_writeConnection->quoteInto('product_id IN(?)', $oldYouDriveVehicles)
                );
            }

            /**
             * Compile recently imported test-drive that that has been re-publised from a existing used or new products
             * If a recently imported product exists in the catalog_product_super_link table then delete association
             */
            $superLinkTable = Mage::getResourceSingleton('catalog/product_type_configurable')->getMainTable();
            $select = $this->_readConnection->select()
                ->from($superLinkTable, ['product_id'])
                ->where('product_id IN(?)', $importedYouDriveIds);

            $republishedVehicles = $this->_readConnection->fetchCol($select);

            // Delete simple/configurable product association
            // Adapted from Mage_Catalog_Model_Resource_Product_Type_Configurable::saveProducts
            if ($republishedVehicles) {
                // Deleting from catalog_product_super_link table
                $this->_writeConnection->delete(
                    $superLinkTable,
                    $this->_writeConnection->quoteInto('product_id IN(?)', $republishedVehicles)
                );
                // Deleting from catalog_product_relation table
                $this->_writeConnection->delete(
                    $this->_resourceModel->getTableName('catalog/product_relation'),
                    $this->_writeConnection->quoteInto('child_id IN(?)', $republishedVehicles)
                );

                // Re-index products attributes to update configurable vehicles_condition to take out T condition fron index table
                Mage::getResourceModel('catalog/product_indexer_eav_source')->reindexEntities([$republishedVehicles]);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
