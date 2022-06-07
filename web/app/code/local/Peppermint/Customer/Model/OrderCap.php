<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Customer
 * @author       Ketevani Revazishvili <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_OrderCap extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    /**
     * Adds customer groups on which settings are not defined yet
     *
     * @return void
     */
    protected function _afterLoad()
    {
        try {
            if ($this->getValue()) {
                $serializedValue = is_string($this->getValue()) ? $this->getValue() : $this->getValue()->__toString();

                if (!empty($serializedValue)) {
                    $unserializedValue = Mage::helper('core/unserializeArray')
                        ->unserialize($serializedValue);
                    $customerGroupIds = array_column($unserializedValue, 'customer_group');
                    $unserializedValue = $this->_addCustomerGroups($unserializedValue, $customerGroupIds);
                    $this->setValue($unserializedValue);
                }
            } else {
                $unserializedValue = [];
                $unserializedValue = $this->_addCustomerGroups($unserializedValue);
                $this->setValue($unserializedValue);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Get customer groups
     *
     * @return array
     */
    protected function _addCustomerGroups($unserializedValue, $customerGroupIds = null)
    {
        $customerGroupsCollection = Mage::getModel('customer/group')->getCollection();

        if ($customerGroupIds) {
            $customerGroupsCollection->addFieldToFilter('customer_group_id', ['nin'  => $customerGroupIds]);
        }

        foreach ($customerGroupsCollection as $k => $group) {
            $groupID = $group->getId();
            $unserializedValue[$this->generateId($groupID)] = [
                "customer_group" => $group->getId(),
                "individual_cap" => null,
                "corporate_cap" => null
            ];
        }

        return $unserializedValue;
    }

    /**
     * Generates unique ID for storing customer group settings
     *
     * @param $groupID
     * @return string
     */
    public function generateId($groupID)
    {
        $milliSeconds = microtime(true);
        $generatedID = '_' . (int)($milliSeconds  * 1000) . '_' . $groupID;

        return $generatedID;
    }
}
