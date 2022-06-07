<?php
/**
 * @category     Rockar
 * @package      Rockar\Shell
 * @author       Sergejs Anfimovs <sergejsa@scandiweb.com>
 * @copyright    Copyright (c) 2016 Scandiweb, Inc (http://scandiweb.com)
 * @license      http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
require_once __DIR__.'/../abstract.php';

class Rockar_Attribute_Import extends Rockar_Shell_Abstract
{
    /**
     * Admin store ID
     */
    const ADMIN_STORE_ID = 0;

    protected $_productTypeId;

    /**
     * specific brand store view code
     *
     * @var string
     */
    protected $_brandStoreView;

    public function run()
    {
        $currentStore = Mage::app()->getStore()->getId();

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $fileName = __DIR__ . '/attributes.csv';
        $brand = $this->_getCliCommand();
        $this->_brandStoreView = $this->getStoreViewByCode($brand);

        switch ($brand) {
            case ($brand !== ''):
                $this->_showMessage(sprintf('Starting "%s" Website Import Process', $brand), 'magenta');

                $this->processAttributeCsv($fileName);

                $this->_showMessage('==========================================', 'yellow');
                $this->_showMessage('DONE.', 'green');
                Mage::helper('peppermint_catalogrule')->createProductAttributesForPriceRulesFlatIdx();
                $this->_showMessage('Attributes views renewed.', 'green');
                break;
            case 'help':
                /* Usage help or unrecognised command */
                $this->_appendResponse($this->usageHelp());
                break;
            default:
                $this->_appendResponse('Invalid command', 'red');
                $this->_appendResponse('Please add "-- help" parameter to show script usage.', 'green');
        }

        Mage::app()->setCurrentStore($currentStore);

        $this->_displayResponse();
    }

    /**
     * Goes through CSV file with attribute definitions and imports them to the new instance
     *
     * @param $fileName
     * @return $this
     */
    public function processAttributeCsv($fileName)
    {
        $file = fopen($fileName, 'r');
        while (!feof($file)) {
            $csv[] = fgetcsv($file);
        }
        $keys = array_shift($csv);
        foreach ($csv as $i => $row) {
            if (is_array($row)) {
                $csv[$i] = array_combine($keys, $row);
            } else {
                unset($csv[$i]);
            }
        }

        foreach ($csv as $row) {
            // Skip default and marked for deletion attributes
            if (isset($row['_type']) && in_array($row['_type'], array('default', 'delete'))) {
                continue;
            }

            $label = $row['frontend_label'];
            $attributeCode = $row['attribute_code'];
            $importedCodes[] = $attributeCode;
            $defaultOption = null;

            if ($row['_options'] != '') {
                $options = explode(';;;', $row['_options']);
                $groupedOptions = array();

                foreach ($options as $key => $option) {
                    if (strpos($option, '>>') !== false) {
                        $explode = explode('>>', $option);
                        $groupedOptions[$explode[0]] = array(
                            'code' => $explode[0],
                            'value' => $explode[1],
                        );
                    } elseif (strpos($option, '|def') !== false) {
                        $explode = explode('|def', $option);
                        $options[$key] = $explode[0];
                        $defaultOption = $explode[0];
                    }
                }

            } else {
                $options = false;
            }

            if ($row['apply_to'] != '') {
                $productTypes = explode(',', $row['apply_to']);
            } else {
                $productTypes = false;
            }

            $setInfo = array();
            if ($row['_attribute_sets'] != '') {
                $sets = explode(';;;', $row['_attribute_sets']);
                foreach ($sets as $set) {
                    $s = explode('>>>', $set);
                    $setInfo[] = array('set' => $s[0], 'group' => $s[1]);
                }
            } else {
                $setInfo = false;
            }

            unset(
                $row['attribute_code'],
                $row['_options'],
                $row['attribute_id'],
                $row['entity_type_id'],
                $row['search_weight'],

                $row['_attribute_sets'],
                $row['_type'],
                $row['_rockar_comments'],

                $row['attribute_group'],
                $row['attribute_subgroup'],
                $row['image'],
                $row['short_description']
            );

            $entityTypeId = $this->_getProductTypeId();
            $eavSetup = Mage::getModel('eav/entity_setup', 'core_setup');
            $attributeId = $eavSetup->getAttribute($entityTypeId, $attributeCode, 'attribute_id');
            $options = !empty($groupedOptions) ? $groupedOptions : $options;

            if ($attributeId === false) {
                unset(
                    $row['frontend_label'],
                    $row['apply_to']
                );
                $this->_createAttribute($label, $attributeCode, $row, $productTypes, $options, $defaultOption);
            } else {
                try {
                    $eavSetup = Mage::getModel('eav/entity_setup', 'core_setup');
                    $eavSetup->updateAttribute($entityTypeId, $attributeId, $row);
                    $this->_showMessage(sprintf('Attribute %s has been updated', $attributeCode), 'magenta');

                    if (is_array($options)) {
                        $this->_cleanAttributeValues($attributeId, $options);
                        foreach ($options as $option) {
                            $this->_addAttributeValue($attributeCode, $option);
                        }

                        if ($defaultOption) {
                            $this->_setDefaultOption($attributeCode, $defaultOption);
                        }
                    }
                } catch (Exception $e) {
                    $this->_showMessage(sprintf('Cannot update attribute: %s, Message: %s', $attributeCode, $e->getMessage()), 'magenta');
                }
            }

            $this->_updateSet($attributeCode, $setInfo);
        }

        return $this;
    }

    public function _cleanAttributeValues($attributeId, $newOptions)
    {
        $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
        $attributeOptionsModel = Mage::getModel('eav/entity_attribute_source_table');
        $attributeOptionsModel->setAttribute($attribute);
        $existingOptions = $attributeOptionsModel->getAllOptions(false);

        $deleteOptions = array();

        foreach ($existingOptions as $option) {

            if (in_array($option['label'], $newOptions) || isset($newOptions[$option['label']])) {
                continue;
            }

            $deleteOptions['delete'][$option['value']] = true;
            $deleteOptions['value'][$option['value']] = true;
        }
        $eavSetup = Mage::getModel('eav/entity_setup', 'core_setup');
        $eavSetup->addAttributeOption($deleteOptions);

        return $this;
    }

    /**
     * Saves attribute option if it does not exist
     *
     * @param $attributeCode
     * @param $optionValue
     * @return $this
     * @throws Exception
     */
    protected function _addAttributeValue($attributeCode, $optionValue)
    {
        $adminStoreId = Mage::app()->getStore(self::ADMIN_STORE_ID)->getId();
        $brandStoreView = Mage::app()->getStore($this->_brandStoreView)->getId();
        $data = array($adminStoreId => $optionValue);

        if (is_array($optionValue)) {
            $data = array(
                $adminStoreId => $optionValue['code'],
                $brandStoreView => $optionValue['value']
            );
        }

        $attributeModel = Mage::getModel('catalog/resource_eav_attribute');
        $attributeId = $attributeModel->getIdByCode('catalog_product', $attributeCode);
        $attribute = $attributeModel->load($attributeId);

        $existingValue = $this->_attributeValueExists($attribute, $optionValue);

        if (!$existingValue) {
            $value['option'] = $data;
            $result = array('value' => $value);

            $attribute->setData('option', $result);
            $attribute->save();
        }

        return $this;
    }

    /**
     * Get catalog_product entity type ID
     *
     * @return int
     */
    protected function _getProductTypeId()
    {
        if ($this->_productTypeId === null) {
            $this->_productTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        }
        return $this->_productTypeId;
    }

    /**
     * Create an attribute.
     *
     * For reference, see Mage_Adminhtml_Catalog_Product_AttributeController::saveAction().
     *
     * @param string $label
     * @param string $attributeCode
     * @param bool|array $values
     * @param bool|array $productTypes
     * @param bool|array $options
     * @param bool|string $defaultOption
     * @return mixed
     */
    protected function _createAttribute($label, $attributeCode, $values = false, $productTypes = false, $options = false, $defaultOption = false)
    {
        $label = trim($label);
        $attributeCode = trim($attributeCode);

        if ($label == '' || $attributeCode == '') {
            $this->_showMessage(sprintf('Can not import the attribute with an empty label or code. Label: "%s", Attribute code: "%s"', $label, $attributeCode));
            return false;
        }

        if ($values === false) {
            $values = array();
        }

        if ($productTypes === false) {
            $productTypes = array();
        }

        // Build the data structure that will define the attribute
        $data = array(
            'is_global' => '0',
            'frontend_input' => 'text',
            'default_value_text' => '',
            'default_value_yesno' => '0',
            'default_value_date' => '',
            'default_value_textarea' => '',
            'is_unique' => '0',
            'is_required' => '0',
            'frontend_class' => '',
            'is_searchable' => '1',
            'is_visible_in_advanced_search' => '1',
            'is_comparable' => '1',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '1',
            'is_visible_on_front' => '0',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '0',
            'is_configurable' => '0',
            'is_filterable' => '0',
            'is_filterable_in_search' => '0',
            'backend_type' => 'varchar',
            'default_value' => '',
            'is_user_defined' => '0',
            'is_visible' => '1',
            'is_used_for_price_rules' => '0',
            'position' => '0',
            'is_wysiwyg_enabled' => '0',
            'backend_model' => '',
            'attribute_model' => '',
            'backend_table' => '',
            'frontend_model' => '',
            'source_model' => '',
            'note' => '',
            'frontend_input_renderer' => '',
        );

        // Now, overlay the incoming values on to the defaults
        foreach ($values as $key => $newValue) {
            if (isset($data[$key])) {
                $data[$key] = $newValue;
            }
        }

        // Valid product types: simple, grouped, configurable, virtual, bundle, downloadable, giftcard
        $data['apply_to'] = $productTypes;
        $data['attribute_code'] = $attributeCode;
        $data['frontend_label'] = array(0 => $label);

        $entityTypeId = $this->_getProductTypeId();

        $model = Mage::getModel('catalog/resource_eav_attribute');
        $model->addData($data);
        $model->setEntityTypeId($entityTypeId);
        $model->setIsUserDefined(1);

        // Save
        try {
            $model->save();
            $this->_showMessage(sprintf('Attribute %s has been created successfully', $attributeCode));
        } catch (Exception $e) {
            $this->_showMessage(sprintf('Attribute %s could not be saved, Message: %s', $attributeCode, $e->getMessage()));
            return false;
        }

        if (is_array($options)) {
            foreach ($options as $option) {
                $this->_addAttributeValue($attributeCode, $option);
            }
        }

        if ($defaultOption) {
            $this->_setDefaultOption($attributeCode, $defaultOption);
        }

        $id = $model->getId();

        return $id;
    }

    /**
     * Updating attribute default value with option id
     *
     * @param $attributeCode
     * @param $optionValue
     * @return $this
     */
    protected function _setDefaultOption($attributeCode, $optionValue)
    {
        $attributeModel = Mage::getModel('catalog/resource_eav_attribute');
        $attributeId = $attributeModel->getIdByCode('catalog_product', $attributeCode);
        $attribute = $attributeModel->load($attributeId);

        $existingValue = $this->_attributeValueExists($attribute, $optionValue, true);

        if ($existingValue) {
            $attribute->setData('default_value', $existingValue);
            $attribute->save();
        }

        return $this;
    }

    /**
     * Get attribute option value (or false if it does not exist)
     *
     * @param $attribute
     * @param $optionValue
     * @return bool
     */
    protected function _attributeValueExists($attribute, $optionValue)
    {
        if (is_array($optionValue)) {
            $optionValue = $optionValue['code'];
        }

        $attributeOptionsModel = Mage::getModel('eav/entity_attribute_source_table');
        $attributeOptionsModel->setAttribute($attribute);
        $options = $attributeOptionsModel->getAllOptions(false);

        foreach ($options as $option) {
            if ($option['label'] == $optionValue) {
                return $option['value'];
            }
        }

        return false;
    }

    /**
     * Update set and tab info for the attribute
     *
     * @param $attributeCode
     * @param $setInfo
     * @return $this
     */
    protected function _updateSet($attributeCode, $setInfo)
    {
        if (is_array($setInfo)) {
            $entityTypeId = $this->_getProductTypeId();
            $setModel = Mage::getModel('eav/entity_setup', 'core_setup');
            foreach ($setInfo as $set) {
                /**
                 * Create attribute set if not exists
                 */
                if (!$setModel->getAttributeSet($entityTypeId, $set['set'])) {
                    $setModel->addAttributeSet($entityTypeId, $set['set']);
                }

                /**
                 * Create attribute group if not exists
                 */
                if (!$setModel->getAttributeGroup($entityTypeId, $set['set'], $set['group'])) {
                    try {
                        $setId = $setModel->getAttributeSetId($entityTypeId, $set['set']);
                        $groupModel = Mage::getModel('eav/entity_attribute_group');
                        $groupModel->setAttributeSetId($setId)
                            ->setAttributeGroupName($set['group']);
                        $groupModel->save();

                        $this->_showMessage(sprintf('Attribute group "%s" has been created successfully', $set['group']));
                    } catch (Exception $e) {
                        $this->_showMessage(sprintf('Failed to create attribute group "%s"', $set['group']));
                    }
                }
                $setModel->addAttributeToSet($entityTypeId, $set['set'], $set['group'], $attributeCode);
            }
        }

        return $this;
    }

    /**
     * Retrieve Usage Help Message
     *
     * @return  string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f attribute_import.php -- [BRAND_NAME],

Available commands:
help                                    This help

Example:
php -f attribute_import.php -- Hyundai

USAGE;
    }
}

$export = new Rockar_Attribute_Import();
$export->run();
