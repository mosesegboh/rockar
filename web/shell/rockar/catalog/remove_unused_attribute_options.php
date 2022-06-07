<?php
/**
 * @category Rockar
 * @package Rockar_Shell
 * @author Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (https://rockar.com)
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Rockar_Shell_Remove_Unused_Attribute_Options extends Rockar_Shell_Abstract
{
    /**
     * Executes the script
     *
     * @return Rockar_Shell_Remove_Unused_Attribute_Options|void
     */
    public function run()
    {
        $input = $this->_getCliCommand();

        switch ($input) {
            case ($input !== ''):
                $attribute = Mage::getSingleton('eav/config')
                    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $input);

                if (!$attribute->getAttributeId()) {
                    $this->_appendResponse(
                        sprintf('Attribute %s not found', $input),
                        'red'
                    );
                    break;
                }

                $ids = $this->getUnassignedOptions($attribute);

                if (!$ids) {
                    $this->_appendResponse(
                        sprintf('Attribute %s has no unassigned options', $attribute->getAttributeCode()),
                        'green'
                    );
                    break;
                }

                $this->deleteUnassignedOptions($ids);
                $this->_appendResponse(
                    sprintf('%s unassigned options removed for %s', count($ids), $attribute->getAttributeCode()),
                    'green'
                );
                break;
            case 'help':
                $this->_appendResponse($this->usageHelp());
                break;
            default:
                $this->_appendResponse('Invalid command', 'red');
                $this->_appendResponse('Please add "-- help" parameter to show script usage.', 'green');
                break;
        }

        $this->_displayResponse();
    }

    /**
     * Gets unassigned options for attribute
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return array
     */
    private function getUnassignedOptions(Mage_Catalog_Model_Resource_Eav_Attribute $attribute)
    {
        $connection = $this->_getReadAdapter();
        $ids = [];

        $query = $connection->select()
            ->from(
                ['a' => $connection->getTableName('eav_attribute_option')],
                ['a.option_id']
            )->joinLeft(
                ['b' => $connection->getTableName(sprintf('catalog_product_entity_%s', $attribute->getBackendType()))],
                'a.attribute_id = b.attribute_id AND a.option_id = b.value',
                null
            )->where(
                'a.attribute_id = ?', $attribute->getAttributeId()
            )->where(
                'b.value IS NULL'
            );

        foreach ($connection->fetchAll($query) as $row) {
            $ids[] = $row['option_id'];
        }

        return $ids;
    }

    /**
     * Deletes unassigned options
     *
     * @param array $ids
     */
    private function deleteUnassignedOptions(array $ids)
    {
        $connection = $this->_getReadAdapter();

        $connection->delete(
            $connection->getTableName('eav_attribute_option'),
            ['option_id IN (?)' => $ids]
        );
    }

    /**
     * Retrieve Usage Help Message
     *
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f remove_unused_attribute_options.php -- [attributeCode]

Available commands:
help                                    This help

Example:
php -f remove_unused_attribute_options.php -- color

USAGE;
    }
}

$shell = new Rockar_Shell_Remove_Unused_Attribute_Options();
$shell->run();
