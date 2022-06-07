<?php

/**
 * @category Rockar
 * @package Rockar\Setup
 * @author Dmitrijs Sitovs <dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2017 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
class Rockar_Setup_Model_Resource_Setup
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
{
    protected $_ioObject = null;
    protected $_destinationMediaDirectory = null;
    protected $_productTypeId = null;

    /**
     * Creates the IO object and optionally creates the directory.
     *
     * @return Varien_Io_File
     */
    protected function _getIoObject()
    {
        if ($this->_ioObject === null) {
            $this->_ioObject = new Varien_Io_File();
            $destDirectory = $this->_destinationMediaDirectory;

            try {
                $this->_ioObject->open(array('path' => $destDirectory));
            } catch (Exception $e) {
                $this->_ioObject->mkdir($destDirectory, 0777, true);
                $this->_ioObject->open(array('path' => $destDirectory));
            }
        }

        return $this->_ioObject;
    }

    /**
     * Gets the directory from which media files are copied.
     *
     * @param string $package
     * @param string $theme
     *
     * @return string
     */
    protected function _getSourceMediaDirectory($package, $theme)
    {
        $themePath = 'base' . DS . 'default';

        if ($package && $theme) {
            $themePath = $package . DS . $theme;
        }

        return Mage::getBaseDir('skin') . DS . 'frontend' . DS . $themePath . DS . 'images' . DS . 'temp_media' . DS;
    }

    /**
     * Gets the directory in which media files are copied to.
     *
     * @param string $folderPath
     *
     * @return string
     */
    protected function _getDestinationMediaDirectory($folderPath = 'wysiwyg')
    {
        if (!$folderPath) {
            return Mage::getBaseDir('media') . DS;
        }

        return Mage::getBaseDir('media') . DS . $folderPath . DS;
    }

    /**
     * Copies an array of files from a source to a destination media directory.
     *
     * @param array $files
     * @param string $package
     * @param string $theme
     * @param string $folderPath
     *
     * @return Rockar_Setup_Model_Resource_Setup
     */
    public function copyMediaFiles($files, $package, $theme, $folderPath = null)
    {
        $sourceDirectory = $this->_getSourceMediaDirectory($package, $theme);
        $destDirectory = $this->_getDestinationMediaDirectory($folderPath);
        $this->_destinationMediaDirectory = $destDirectory;

        foreach ($files as $file) {
            $subfolderDestination = $this->_destinationMediaDirectory;
            $sourceFile = $sourceDirectory . $file;
            $pathWithoutFile = $this->_getArrayWithoutLastElement(explode('/', $file));

            /**
             * create necessary subfolders if these are missing
             */
            foreach ($pathWithoutFile as $subFolder) {
                $subfolderDestination .= $subFolder . DS;
                $this->_getIoObject()->checkAndCreateFolder($subfolderDestination);
            }

            /**
             * copy images into media folder
             */
            if ($this->_getIoObject()->fileExists($sourceFile)) {
                $this->_getIoObject()->cp($sourceFile, $destDirectory . $file);
            }
        }

        return $this;
    }

    /**
     * @param $array
     *
     * @return mixed
     */
    protected function _getArrayWithoutLastElement($array)
    {
        $arrayElementsCount = count($array);
        unset($array[$arrayElementsCount - 1]);

        return $array;
    }

    /**
     * Add or update existing static block
     *
     * @param string $identifier
     * @param string $title
     * @param string $content
     * @param array $stores
     */
    public function addStaticBlock($identifier, $title, $content, $stores = array(0))
    {
        $block = Mage::getModel('cms/block')
            ->load($identifier, 'identifier')
            ->setIdentifier($identifier)
            ->setContent($content)
            ->setTitle($title)
            ->setIsActive(1)
            ->setStores($stores)
            ->save();

        return $block;
    }

    /**
     * @param $prefix
     *
     * @return Mage_Catalog_Model_Category
     */
    public function createRootCategory($prefix)
    {
        $category = Mage::getModel('catalog/category');
        $category->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->setName($prefix . ' Root Category')
            ->setIsActive(1)
            ->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT)
            ->setPath($this->getTreeRootCategory()->getPath())
            ->save();

        return $category;
    }

    /**
     * @param $data
     *
     * Example:
     * $data = array(
     * 'code' => 'code',
     * 'website_id' => '1',
     * 'group_id' => '1',
     * 'name' => 'Name',
     * 'is_active' => '1',
     * 'sort_order' => '0',
     * );
     *
     * @return Mage_Core_Model_Store
     */
    public function createStoreView($data)
    {
        $storeModel = Mage::getModel('core/store');
        $storeModel->addData($data)
            ->save();

        return $storeModel;
    }

    /**
     * @param $data
     *
     * Example:
     * $data = array(
     * 'website_id' => '1',
     * 'name' => 'Store Group Name',
     * 'root_category_id' => '1',
     * );
     *
     * @return Mage_Core_Model_Store_Group
     */
    public function createStoreGroup($data)
    {
        $storeGroupModel = Mage::getModel('core/store_group');
        $storeGroupModel->addData($data)
            ->save();

        return $storeGroupModel;
    }

    /**
     * @param $data
     *
     * Example:
     * $data = array(
     * 'code' => 'website_code',
     * 'name' => 'Website Name',
     * 'sort_order' => '100',
     * );
     *
     * @return Mage_Core_Model_Website
     */
    public function createWebsite($data)
    {
        $websiteModel = Mage::getModel('core/website');
        $websiteModel->addData($data)
            ->save();

        return $websiteModel;
    }

    /**
     * @param $prefix
     *
     * @return mixed|string
     */
    public function prepareCodeFromPrefix($prefix)
    {
        $prefix = str_replace(' ', '', $prefix);
        $prefix = strtolower($prefix);
        $prefix = trim($prefix);

        return $prefix;
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getTreeRootCategory()
    {
        $parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        $parentCategory = Mage::getModel('catalog/category')->load($parentId);

        return $parentCategory;
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

            if ($row['_options'] != '') {
                $options = explode(';;;', $row['_options']);
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
                $row['apply_to'],
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
            $attributeId = $this->getAttribute($entityTypeId, $attributeCode, 'attribute_id');

            if ($attributeId === false) {
                unset($row['frontend_label']);
                $this->_createAttribute($label, $attributeCode, $row, $productTypes, $options);
            } else {
                $this->updateAttribute($entityTypeId, $attributeId, $row);

                if (is_array($options)) {
                    $this->_cleanAttributeValues($attributeId, $options);
                    foreach ($options as $option) {
                        $this->_addAttributeValue($attributeCode, $option);
                    }
                }
            }

            $this->_updateSet($attributeCode, $setInfo);
        }

        return $this;
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
     * @return mixed
     */
    protected function _createAttribute($label, $attributeCode, $values = false, $productTypes = false, $options = false)
    {
        $label = trim($label);
        $attributeCode = trim($attributeCode);

        if ($label == '' || $attributeCode == '') {
            echo "Can't import the attribute with an empty label or code.  LABEL= [$label]  CODE= [$attributeCode]"
                . "<br/>";
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
            'is_global'                     => '0',
            'frontend_input'                => 'text',
            'default_value_text'            => '',
            'default_value_yesno'           => '0',
            'default_value_date'            => '',
            'default_value_textarea'        => '',
            'is_unique'                     => '0',
            'is_required'                   => '0',
            'frontend_class'                => '',
            'is_searchable'                 => '1',
            'is_visible_in_advanced_search' => '1',
            'is_comparable'                 => '1',
            'is_used_for_promo_rules'       => '0',
            'is_html_allowed_on_front'      => '1',
            'is_visible_on_front'           => '0',
            'used_in_product_listing'       => '0',
            'used_for_sort_by'              => '0',
            'is_configurable'               => '0',
            'is_filterable'                 => '0',
            'is_filterable_in_search'       => '0',
            'backend_type'                  => 'varchar',
            'default_value'                 => '',
            'is_user_defined'               => '0',
            'is_visible'                    => '1',
            'is_used_for_price_rules'       => '0',
            'position'                      => '0',
            'is_wysiwyg_enabled'            => '0',
            'backend_model'                 => '',
            'attribute_model'               => '',
            'backend_table'                 => '',
            'frontend_model'                => '',
            'source_model'                  => '',
            'note'                          => '',
            'frontend_input_renderer'       => '',
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
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::log("Attribute [$attributeCode] could not be saved: {$e->getMessage()}");
            return false;
        }

        if (is_array($options)) {
            foreach ($options as $option) {
                $this->_addAttributeValue($attributeCode, $option);
            }
        }

        $id = $model->getId();

        return $id;
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
            $setModel = Mage::getModel('eav/entity_setup','core_setup');
            foreach ($setInfo as $set) {
                $setModel->addAttributeToSet($entityTypeId, $set['set'], $set['group'], $attributeCode);
            }
        }

        return $this;
    }

    /**
     * Remove attribute options that are not present in a new value list
     *
     * @param $attributeId
     * @param $newOptions
     * @return $this
     */
    public function _cleanAttributeValues($attributeId, $newOptions)
    {
        $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
        $attributeOptionsModel = Mage::getModel('eav/entity_attribute_source_table');
        $attributeOptionsModel->setAttribute($attribute);
        $existingOptions = $attributeOptionsModel->getAllOptions(false);

        $deleteOptions = array();

        foreach ($existingOptions as $option) {
            if (!in_array($option['label'], $newOptions)) {
                $deleteOptions['delete'][$option['value']] = true;
                $deleteOptions['value'][$option['value']] = true;
            }
        }

        $this->addAttributeOption($deleteOptions);

        return $this;
    }

    /**
     * Saves attribute option if it does not exist
     *
     * @param $attributeCode
     * @param $optionValue
     * @return bool
     * @throws Exception
     */
    protected function _addAttributeValue($attributeCode, $optionValue)
    {
        $attributeModel = Mage::getModel('eav/entity_attribute');
        $attributeId = $attributeModel->getIdByCode('catalog_product', $attributeCode);
        $attribute = $attributeModel->load($attributeId);

        $existingValue = $this->_attributeValueExists($attribute, $optionValue);

        if (!$existingValue) {
            $value['option'] = array(0 => $optionValue);
            $result = array('value' => $value);
            $attribute->setData('option', $result);
            $attribute->save();

            $existingValue = $this->_attributeValueExists($attribute, $optionValue);
        }

        return $existingValue;
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
     * Deletes attributes by codes in CSV file. One attribute code per row
     *
     * @param $fileName
     * @return $this
     */
    public function massRemoveAttributes($fileName)
    {
        $file = fopen($fileName, 'r');
        $entityTypeId = $this->_getProductTypeId();

        while (!feof($file)) {
            $code = trim(fgets($file));
            $attributeId = $this->getAttribute($entityTypeId, $code, 'attribute_id');
            if ($attributeId) {
                $this->removeAttribute($entityTypeId, $code);
            }
        }

        return $this;
    }

    /**
     * Method to create menu manager items via migration script.
     *
     * @param $data
     * @param $position
     * @param $menu
     * @param int $parentId
     *
     * @throws Exception
     */
    public function createMenuManagerMenu($data, $position, $menu, $parentId = 0)
    {
        if (!Mage::getModel('scandi_menumanager/item')) {
            throw new Exception('Scandi Menu Manager module should be installed.');
        }

        foreach ($data AS $title => $item) {
            $class = array_key_exists('css_class', $item) ? $item['css_class'] : '';
            $type = array_key_exists('type', $item) ? $item['type'] : '';
            $url = array_key_exists('url', $item) ? $item['url'] : 'javascript:void(0);';
            $isActive = array_key_exists('is_active', $item) ? $item['is_active'] : 'javascript:void(0);';
            $itemPosition = array_key_exists('position', $item) ? $item['position'] : $position;
            $customParams = array_key_exists('custom_params', $item) ? $item['custom_params'] : false;

            $parentItem = Mage::getModel('scandi_menumanager/item')->load($item['identifier'])
                ->setIdentifier($item['identifier'])
                ->setMenuId($menu->getId())
                ->setParentId($parentId)
                ->setTitle($title)
                ->setUrl($url)
                ->setType($type)
                ->setCssClass($class)
                ->setPosition($itemPosition)
                ->setCustomParams($customParams)
                ->setIsActive($isActive)
                ->save();

            $position += 10;

            if (isset($item['sub'])) {
                /* Add Menu Sub Items */
                $this->createMenuManagerMenu($item['sub'], 10, $menu, $parentItem->getId());
            }
        }
    }

    /**
     * @return int
     */
    public function getMitsubishiWebsiteId()
    {
        $id = 0;
        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            if ($website->getCode() === 'mitsubishi') {
                $id = $website->getId();
            }
        }

        return $id;
    }
}
