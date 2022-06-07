<?php
/**
 * @category Peppermint
 * @package Peppermint_Carfinder
 * @author Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Carfinder_Model_Catalog_Resource_Eav_Mysql4_Attribute extends Rockar_Carfinder_Model_Catalog_Resource_Eav_Mysql4_Attribute
{
    /**
     * Save attribute options.
     *
     * @param Mage_Core_Model_Abstract $object
     * @throws Mage_Core_Exception
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _saveOption(Mage_Core_Model_Abstract $object)
    {
        $option = $object->getOption();

        if (is_array($option)) {
            $write = $this->_getWriteAdapter();
            $read = $this->_getReadAdapter();
            $optionTable = $this->getTable('attribute_option');
            $optionValueTable = $this->getTable('attribute_option_value');
            $stores = Mage::getModel('core/store')->getResourceCollection()
                ->setLoadDefault(true)
                ->load();

            if (isset($option['value'])) {
                $attributeDefaultValue = [];

                if (!is_array($object->getDefault())) {
                    $object->setDefault([]);
                }

                foreach ($option['value'] as $optionId => $values) {
                    $hideInStores = '';
                    $intOptionId = is_int($optionId) ? $optionId : false;

                    if (!empty($option['delete'][$optionId])) {
                        if ($intOptionId) {
                            $condition = $write->quoteInto('option_id=?', $intOptionId);
                            $write->delete($optionTable, $condition);
                        }

                        continue;
                    }

                    if (isset($option['hidden'][$optionId])) {
                        $hideInStores = implode(',', $option['hidden'][$optionId]);
                    }

                    if ($intOptionId === false) {
                        $data = [
                            'attribute_id' => $object->getId(),
                            'sort_order' => $option['order'][$optionId] ?? 0,
                            'image' => $option['image'][$optionId] ?? '',
                            'thumb' => $option['thumb'][$optionId] ?? '',
                            'hide_in_stores' => $hideInStores
                        ];
                        $write->insert($optionTable, $data);

                        $intOptionId = (int) $write->lastInsertId();
                    } else {
                        $data = [
                            'image' => $option['image'][$optionId] ?? '',
                            'thumb' => $option['thumb'][$optionId] ?? '',
                            'hide_in_stores' => $hideInStores
                        ];

                        if (isset($option['order'][$optionId])) {
                            $data['sort_order'] = $option['order'][$optionId];
                        }

                        if (empty($data['image'])) {
                            unset($data['image']);
                        }

                        if (empty($data['thumb'])) {
                            unset($data['thumb']);
                        }

                        $write->update($optionTable, $data, $write->quoteInto('option_id=?', $intOptionId));
                    }

                    if (in_array($optionId, $object->getDefault())) {
                        if ($object->getFrontendInput() == 'multiselect') {
                            $attributeDefaultValue[] = $intOptionId;
                        } elseif ($object->getFrontendInput() == 'select') {
                            $attributeDefaultValue = [$intOptionId];
                        }
                    }

                    // Default value
                    if (!isset($values[0])) {
                        Mage::throwException(Mage::helper('eav')->__('Default option value is not defined.'));
                    }

                    foreach ($stores as $store) {
                        $storeId = (int) $store->getId();

                        if (isset($values[$storeId])
                            && (!empty($values[$storeId]) || $values[$storeId] == '0')
                        ) {
                            $data = [
                                'option_id' => $intOptionId,
                                'store_id' => $storeId,
                                'value' => $values[$storeId]
                            ];

                            $select = $read->select()->from($optionValueTable)
                                ->where('option_id=?', $intOptionId)
                                ->where('store_id=?', $storeId);
                            $row = $read->fetchOne($select);

                            if ($row) {
                                $write->update(
                                    $optionValueTable,
                                    $data,
                                    [
                                        $write->quoteInto('option_id=?', $intOptionId),
                                        $write->quoteInto('store_id=?', $storeId)
                                    ]
                                );
                            } else {
                                $write->insert($optionValueTable, $data);
                            }
                        } else {
                            $write->delete(
                                $optionValueTable,
                                [
                                    $write->quoteInto('option_id=?', $intOptionId),
                                    $write->quoteInto('store_id=?', $storeId)
                                ]
                            );
                        }
                    }
                }

                $write->update(
                    $this->getMainTable(),
                    ['default_value' => implode(',', $attributeDefaultValue)],
                    $write->quoteInto($this->getIdFieldName() . '=?', $object->getId())
                );
            }
        }

        return $this;
    }
}
