<?php
/**
 * @category     Rockar
 * @package      Rockar\Shell
 * @author       Taras Kapushchak <info@scandiweb.com>
 * @copyright    Copyright (c) 2016 Scandiweb, Ltd (http://scandiweb.com)
 * @license      http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

require_once '../abstract.php';

class Rockar_Attribute_Export extends Mage_Shell_Abstract
{
    /**
     * Collects all product attributes with their options
     *
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();

        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $selectAttributes = $connection->select()
            ->from(array('ea' => $resource->getTableName('eav/attribute')))
            ->join(
                array('c_ea' => $resource->getTableName('catalog/eav_attribute')),
                'ea.attribute_id = c_ea.attribute_id'
            );

        $selectProductAttributes = $selectAttributes->where('ea.entity_type_id = ' . $entityTypeId)
            ->order('ea.attribute_id ASC');

        $productAttributes = $connection->fetchAll($selectProductAttributes);

        $selectAttributesOptions = $selectAttributes
            ->joinLeft(
                array('e_ea' => $resource->getTableName('eav/entity_attribute')),
                'ea.attribute_id = e_ea.attribute_id',
                array('attribute_set_id', 'attribute_group_id', 'attribute_id' => 'ea.attribute_id')
            )
            ->joinLeft(
                array('e_as' => $resource->getTableName('eav/attribute_set')),
                'e_ea.attribute_set_id = e_as.attribute_set_id',
                array('attribute_set_name')
            )
            ->joinLeft(
                array('e_ag' => $resource->getTableName('eav/attribute_group')),
                'e_ea.attribute_group_id = e_ag.attribute_group_id',
                array('attribute_group_name')
            )
            ->joinLeft(
                array('e_ao' => $resource->getTableName('eav/attribute_option'), array('option_id')),
                'ea.attribute_id = e_ao.attribute_id',
                array('attribute_id_for_options' => 'e_ao.attribute_id')
            )
            ->joinLeft(
                array('e_aov' => $resource->getTableName('eav/attribute_option_value'), array('value')),
                'e_ao.option_id = e_aov.option_id and store_id = 0'
            )
            ->order('ea.attribute_id ASC');

        $productAttributeOptions = $connection->fetchAll($selectAttributesOptions);

        $attributesCollection = $this->_mergeCollections($productAttributes, $productAttributeOptions);

        return $attributesCollection;
    }

    /**
     * Filter for cases when attribute is not assigned to any set and tab
     *
     * @param $value
     * @return bool
     */
    protected function _filterSets($value)
    {
        if ($value == '>>>') {
            return false;
        }
        return true;
    }

    /**
     * Merge options and set/group info into one CSV row
     *
     * @param $productAttributes
     * @param $productAttributeOptions
     * @return mixed
     */
    protected function _mergeCollections($productAttributes, $productAttributeOptions)
    {
        foreach ($productAttributes as $key => $productAttribute) {
            $valuesOpt = array();
            $valuesSet = array();
            $attributeId = $productAttribute['attribute_id'];

            foreach ($productAttributeOptions as $option) {
                if ($option['attribute_id'] == $attributeId) {
                    $valuesOpt[] = $option['value'];
                    $valuesSet[] = $option['attribute_set_name'] . '>>>' . htmlspecialchars_decode(
                            $option['attribute_group_name']
                        );
                }
            }

            $valuesOpt = array_unique($valuesOpt);
            $valuesSet = array_unique($valuesSet);
            $valuesSet = array_filter($valuesSet, array($this, '_filterSets'));

            if (count($valuesOpt) > 0) {
                $values = implode(';;;', $valuesOpt);
                $productAttributes[$key]['_options'] = $values;
            } else {
                $productAttributes[$key]['_options'] = '';
            }

            if (count($valuesSet) > 0) {
                $values = implode(';;;', $valuesSet);
                $productAttributes[$key]['_attribute_sets'] = $values;
            } else {
                $productAttributes[$key]['_attribute_sets'] = '';
            }
        }

        return $productAttributes;
    }

    /**
     * Output attribute collection as CSV
     *
     * @param        $attributesCollection
     * @param string $filename
     * @param string $delimiter
     * @param string $enclosure
     */
    protected function _prepareCsv($attributesCollection, $filename = 'attributes.csv', $delimiter = '|', $enclosure = '"')
    {
        $f = fopen('php://memory', 'w');
        $first = true;
        foreach ($attributesCollection as $line) {
            if ($first) {
                $titles = array();
                foreach ($line as $field => $val) {
                    $titles[] = $field;
                }
                fputcsv($f, $titles, $delimiter, $enclosure);
                $first = false;
            }

            fputcsv($f, $line, $delimiter, $enclosure);
        }
        fseek($f, 0);
        fpassthru($f);
    }

    /**
     * Run script
     */
    public function run()
    {
        $attributesCollection = $this->_prepareCollection();
        $this->_prepareCsv($attributesCollection);
    }
}

$export = new Rockar_Attribute_Export();
$export->run();
