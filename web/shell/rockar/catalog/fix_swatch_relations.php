<?php
/**
 * @category Rockar
 * @package Rockar_Shell
 * @author Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (https://rockar.com)
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Rockar_Shell_Fix_Swatch_Relations extends Rockar_Shell_Abstract
{
    private const TRIM_FINISHER = 'trim_finisher';

    /**
     * @var mixed
     */
    private $adminWebsiteId;

    /**
     * Fixes swatch relations to products, adds missing image, thumb and removes options without relations to product
     */
    public function run()
    {
        $connection = $this->_getReadAdapter();
        $resource = Mage::getSingleton('core/resource');

        $query = $connection->select()
            ->distinct()
            ->from(
                ['a' => $resource->getTableName('eav/attribute')],
                'c.option_id'
            )->join(
                ['b' => $connection->getTableName('catalog_product_entity_int')],
                'a.attribute_id = b.attribute_id',
                null
            )->join(
                ['c' => $connection->getTableName('eav_attribute_option')],
                'b.attribute_id = c.attribute_id AND b.value = c.option_id',
                null
            )->where(
                sprintf(
                    'a.attribute_code = "%s"',
                    self::TRIM_FINISHER
                )
            )->where(
                'c.image = ""'
            );

        foreach ($connection->fetchAll($query) as $row) {
            foreach ($row as $optionId) {
                $attributeOptionData = [];
                $storeData = [];

                $query = $connection->select()
                    ->from(
                        ['a' => $connection->getTableName('eav_attribute_option_value')],
                        ['c.image', 'c.thumb', 'c.sort_order', 'd.store_id', 'd.value']
                    )->join(
                        ['b' => $connection->getTableName('eav_attribute_option_value')],
                        'a.value = b.value',
                        null
                    )->join(
                        ['c' => $connection->getTableName('eav_attribute_option')],
                        'b.option_id = c.option_id',
                        null
                    )->join(
                        ['d' => $connection->getTableName('eav_attribute_option_value')],
                        'c.option_id = d.option_id',
                        null
                    )->join(
                        ['e' => $resource->getTableName('eav/attribute')],
                        'c.attribute_id = e.attribute_id',
                        null
                    )->where(
                        'a.option_id = ?',
                        $optionId
                    )->where(
                        'c.image != ""'
                    )->where(
                        sprintf(
                            'e.attribute_code = "%s"',
                            self::TRIM_FINISHER
                        )
                    );

                foreach ($connection->fetchAll($query) as $row) {
                    if (!$attributeOptionData) {
                        $attributeOptionData['image'] = $row['image'];
                        $attributeOptionData['thumb'] = $row['thumb'];
                        $attributeOptionData['sort_order'] = $row['sort_order'];
                    }

                    $storeData[$row['store_id']] = $row['value'];
                }

                if ($attributeOptionData) {
                    $this->updateAttributeOptionData($attributeOptionData, $optionId);
                }

                if ($storeData) {
                    $this->addStoreEntries($storeData, $optionId);
                }
            }
        }

        $this->deleteUnassignedOptions();
    }

    /**
     * Update missing image data
     *
     * @param array $attributeOptionData
     * @param string $optionId
     */
    private function updateAttributeOptionData(array $attributeOptionData, string $optionId)
    {
        $connection = $this->_getWriteAdapter();

        $connection->update(
            $connection->getTableName('eav_attribute_option'),
            [
                'image' => $attributeOptionData['image'],
                'thumb' => $attributeOptionData['thumb'],
                'sort_order' => $attributeOptionData['sort_order']
            ],
            [
                'option_id = ?' => $optionId
            ]
        );
    }

    /**
     * Add missing store entries
     *
     * @param array $storeData
     * @param string $optionId
     */
    private function addStoreEntries(array $storeData, string $optionId)
    {
        $connection = $this->_getWriteAdapter();

        foreach ($storeData as $storeId => $value) {
            if ($storeId == $this->getAdminWebsiteId()) {
                continue;
            }

            $connection->insert(
                $connection->getTableName('eav_attribute_option_value'),
                [
                    'option_id' => $optionId,
                    'store_id' => $storeId,
                    'value' => $value
                ]
            );
        }
    }

    /**
     * Deletes options which don't have any relations to product
     */
    private function deleteUnassignedOptions()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $this->_getReadAdapter();
        $ids = [];

        $query = $connection->select()
            ->distinct()
            ->from(
                ['a' => $resource->getTableName('eav/attribute')],
                'b.option_id'
            )->join(
                ['b' => $connection->getTableName('eav_attribute_option')],
                'a.attribute_id = b.attribute_id',
                null
            )->joinLeft(
                ['c' => $connection->getTableName('catalog_product_entity_int')],
                'a.attribute_id = c.attribute_id AND b.option_id = c.value',
                null
            )->where(
                sprintf(
                    'a.attribute_code = "%s"',
                    self::TRIM_FINISHER
                )
            )->where(
                'c.value IS NULL'
            );

        foreach ($connection->fetchAll($query) as $row) {
            $ids[] = $row['option_id'];
        }

        if ($ids) {
            $connection->delete(
                $connection->getTableName('eav_attribute_option'),
                ['option_id IN (?)' => $ids]
            );
        }
    }

    /**
     * Get Admin Website ID
     *
     * @return mixed
     */
    private function getAdminWebsiteId()
    {
        if (!$this->adminWebsiteId) {
            $this->adminWebsiteId = Mage::getModel('core/website')->load('admin')->getId();
        }

        return $this->adminWebsiteId;
    }
}

$shell = new Rockar_Shell_Fix_Swatch_Relations();
$shell->run();
