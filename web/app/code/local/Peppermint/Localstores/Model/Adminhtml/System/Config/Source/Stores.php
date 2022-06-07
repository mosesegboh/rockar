<?php
/**
 * @category Rockar
 * @package  Rockar\Localstores
 * @author   Taras Kapushchak <techteam@rockar.com>
* @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */
class Peppermint_Localstores_Model_Adminhtml_System_Config_Source_Stores
    extends Rockar_Localstores_Model_Adminhtml_System_Config_Source_Stores
{
    /**
     * Get stores
     *
     * @return array
     */
    protected function _getStores()
    {
        $stores = array();
        $result = Mage::getResourceModel('rockar_localstores/stores_collection')
            ->addFieldToFilter('status', Rockar_Localstores_Model_Resource_Stores_Collection::STATUS_ENABLED);

        foreach ($result as $store) {
            $stores[$store->getEntityId()] = $store->getName() . ' ' . $store->getBrandCode();
        }

        return $stores;
    }

    /**
     * Get stores
     *
     * @return array
     */
    protected function _getStoresWithCodes()
    {
        $stores = array();
        $result = Mage::getResourceModel('rockar_localstores/stores_collection')
            ->addFieldToFilter('status', Rockar_Localstores_Model_Resource_Stores_Collection::STATUS_ENABLED);

        foreach ($result as $store) {
            $stores[$store->getCode()] = $store->getName() . ' ' . $store->getBrandCode();
        }

        asort($stores);

        return $stores;
    }
}
