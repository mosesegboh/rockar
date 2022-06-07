<?php
/**
 * Script to change km attribute type to varchar
 *
 * @category Rockar
 * @package Rockar_Shell
 * @author Osama Ahmed <osama.ahmed@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Rockar_Shell_Fix_Km_Attribute extends Rockar_Shell_Abstract
{
    /**
     * Run the script
     */
    public function run()
    {
        $connection = $this->_getReadAdapter();

        $query = $connection->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('eav/attribute'))
            ->where('attribute_code = "km"')
            ->limit(1);

        $attr = $productAttributes = $connection->fetchRow($query);
        $attributeId = $attr['attribute_id'] ?? false;

        if (!$attributeId) {
            echo "Attribute not found.\n";

            return;
        }

        $fillIntValuesSql = "INSERT INTO catalog_product_entity_int
        (entity_type_id, attribute_id, store_id, entity_id, value)
        (
        SELECT
            cpev.entity_type_id,
            cpev.attribute_id,
            cpev.store_id,
            cpev.entity_id,
            cpev.value
            FROM catalog_product_entity_varchar cpev
            WHERE cpev.attribute_id = $attributeId
        )";

        $updateTypeSql = "UPDATE eav_attribute
        SET
            backend_type = 'int',
            frontend_input = 'text',
            source_model = null
        WHERE attribute_id = $attributeId
        ";

        $clearOldValuesSql = "DELETE FROM catalog_product_entity_varchar WHERE attribute_id = $attributeId";

        $this->_showMessage('Inserting values to catalog_product_entity_int', 'magenta');
        $this->_getWriteAdapter()->query($fillIntValuesSql);
        $this->_showMessage('Done', 'green');

        $this->_showMessage('Updating km attribute properties', 'magenta');
        $this->_getWriteAdapter()->query($updateTypeSql);
        $this->_showMessage('Done', 'green');

        $this->_showMessage('Deleting values from catalog_product_entity_varchar', 'magenta');
        $this->_getWriteAdapter()->query($clearOldValuesSql);
        $this->_showMessage('Done', 'green');

        $this->_showMessage('Complete', 'green');
    }
}

$shell = new Rockar_Shell_Fix_Km_Attribute();
$shell->run();
