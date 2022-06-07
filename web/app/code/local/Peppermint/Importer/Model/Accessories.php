<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Accessories extends Mage_Core_Model_Abstract
{
    /**
     * Callback method that will receive queue message and sending to be processed.
     *
     * @param string|array $msg
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    public function processAccessories($msg)
    {
        try {
            $actions = is_array($msg)
                ? $msg
                : (Mage::helper('rockar_all')->jsonDecode($msg) ?: []);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        foreach ($actions as $action => $accessoriesData) {
            switch ($action) {
                case 'delete':
                    if (!empty($accessoriesData)) {
                        $this->deleteAccessories($accessoriesData);
                    }
                    break;
                case 'update':
                case 'add':
                    if (!empty($accessoriesData)) {
                        $this->saveAccessories($accessoriesData);
                    }
                    break;
            }
        }
    }

    /**
     * Save accessories
     *
     * @param array $accessoriesData
     * @return void
     */
    public function saveAccessories($accessoriesData)
    {
        foreach ($accessoriesData as $groupCode => $group) {
            $groupModel = false;

            try {
                $groupModel = $this->saveAccessoriesGroup($group);
            } catch (Exception $e) {
                Mage::logException($e);
            }

            if ($groupModel) {
                foreach ($group['accessories'] as $accessory) {
                    try {
                        $this->saveAccessory($accessory);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
    }

    /**
     * Save accessories group
     *
     * @param $group
     * @return false|Mage_Core_Model_Abstract
     * @throws Exception
     */
    public function saveAccessoriesGroup($group)
    {
        $identifier = $group['categoryCode'];
        $groupModel = Mage::getModel('rockar_accessories/accessories_group')->load($identifier, 'identifier');
        $saveGroup = false;

        if (!$groupModel->getId()) {
            $groupModel->setData([
                'identifier' => $identifier,
                'name' => $group['categoryDescription'],
                'position' => $group['priority']
            ]);
            $saveGroup = true;
        } else {
            if ($groupModel->getName() !== $group['categoryDescription'] || $groupModel->getPosition() !== $group['priority']) {
                $groupModel->addData([
                    'name' => $group['categoryDescription'],
                    'position' => $group['priority']
                ]);
                $saveGroup = true;
            }
        }

        if ($saveGroup) {
            $groupModel->save();
        }

        return $groupModel;
    }

    /**
     * Save accessory data and connect it to corresponding group
     *
     * @param array $accessory
     * @return void
     */
    public function saveAccessory($accessory)
    {
        $group = Mage::getModel('rockar_accessories/accessories_group')->load($accessory['categoryCode'], 'identifier');

        if ($group->getId()) {
            $identifier = $accessory['fullKey'];
            $accessoryModel = Mage::getModel('rockar_accessories/accessories')->load($identifier, 'identifier');

            try {
                if (!$accessoryModel->getId()) {
                    $accessoryModel->setIdentifier($identifier);
                }

                $accessoryModel->addData([
                    'accessories_group_id' => $group->getId(),
                    'material_number' => $accessory['materialNumber'],
                    'name' => $accessory['shortText'],
                    'price' => $accessory['netPrice'],
                    'image' => $accessory['imageUrl'],
                    'description' => $accessory['longText'],
                    'option_code' => $accessory['optionIndicator'],
                    'position' => $accessory['priority']
                ]);

                $accessoryModel->save();

                $this->_checkMissingMappings($accessoryModel);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Delete accessories
     *
     * @param array $accessoriesData
     * @return void
     */
    public function deleteAccessories($accessoriesData)
    {
        $accessoryCodes = [];

        foreach ($accessoriesData as $accessory) {
            $accessoryCodes[] = $accessory['accessoryCode'];
        }

        $collection = Mage::getModel('rockar_accessories/accessories')->getCollection()
            ->addFieldtoFilter('identifier', ['in' => $accessoryCodes]);

        $collection->walk('delete');
    }

    /**
     * Assign mappings that were imported before accessories
     *
     * @param $accessory
     * @return $this
     */
    protected function _checkMissingMappings($accessory)
    {
        $data = [];

        $collection = Mage::getModel('peppermint_importer/accessories_missing')->getCollection()
            ->addFieldToFilter('accessory_identifier', $accessory->getIdentifier());

        foreach ($collection as $item) {
            $data[] = [
                'product_sku' => $item->getProductSku(),
                'accessory_id' => $accessory->getId()
            ];
        }

        if (!empty($data)) {
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');

            $writeConnection->insertOnDuplicate(
                $resource->getTableName('rockar_accessories/accessories_relations'),
                $data
            );
        }

        $collection->walk('delete');

        return $this;
    }
}
