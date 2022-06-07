<?php
/**
 * Script to change vin_number attribute type to varchar
 *
 * @category Rockar
 * @package Rockar\Shell
 * @author Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Rockar_Shell_Fix_Vin_Attribute extends Rockar_Shell_Abstract
{
    /**
     * Run the script
     */
    public function run()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $this->_getReadAdapter();
        $query = $connection->select()
            ->from($resource->getTableName('eav/attribute'))
            ->where('attribute_code = "vin_number"')->limit(1);
        $attr = $productAttributes = $connection->fetchRow($query);
        $attributeId = $attr['attribute_id'] ?? false;

        if (!$attributeId) {
            echo "Attribute not found.\n";

            return;
        }

        $fillVarcharValuesSql = "INSERT INTO catalog_product_entity_varchar 
        (entity_type_id, attribute_id, store_id, entity_id, value)
        (
        SELECT 
            cpei.entity_type_id, 
            cpei.attribute_id, 
            cpei.store_id, 
            cpei.entity_id, 
            eaov.value
            FROM eav_attribute_option eao
            JOIN eav_attribute_option_value eaov
                ON eao.option_id = eaov.option_id
            JOIN catalog_product_entity_int cpei
                ON eao.option_id = cpei.value
            WHERE cpei.attribute_id = $attributeId
        )";

        $updateTypeSql = "UPDATE eav_attribute
        SET 
            backend_type = 'varchar',
            frontend_input = 'text',
            source_model = null
        WHERE attribute_id = $attributeId
        ";

        $cleanOptionValuesSql = "DELETE FROM eav_attribute_option_value 
        WHERE option_id IN (
            SELECT option_id FROM eav_attribute_option 
            WHERE attribute_id = $attributeId
        )";

        $cleanOptionsSql = "DELETE FROM eav_attribute_option WHERE attribute_id = $attributeId";

        $clearOldValuesSql = "DELETE FROM catalog_product_entity_int WHERE attribute_id = $attributeId";

        echo "insert varchar values \n";
        $this->_getWriteAdapter()->query($fillVarcharValuesSql);

        echo "update attribute \n";
        $this->_getWriteAdapter()->query($updateTypeSql);

        echo "clean option values \n";
        $this->_getWriteAdapter()->query($cleanOptionValuesSql);

        echo "clean options \n";
        $this->_getWriteAdapter()->query($cleanOptionsSql);

        echo "clean obsolete int values \n";
        $this->_getWriteAdapter()->query($clearOldValuesSql);

        echo "Done. \n";
    }
}

$shell = new Rockar_Shell_Fix_Vin_Attribute();
$shell->run();
