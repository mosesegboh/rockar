<?php
require_once dirname(__FILE__) . '/../abstract.php';

/**
 *
 * @category Rockar
 * @package Rockar\Shell
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2016 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
class Rockar_Shell_Abstract extends Mage_Shell_Abstract
{
    /**
     * Store response
     *
     * @var     array
     */
    protected $_response = array();

    /**
     * Colour mapping
     *
     * @var     array
     */
    protected $_colours = array(
        'grey' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'white' => 37,
    );

    /**
     * Colour response
     *
     * @param   string $message
     * @param   string $colour
     *
     * @return  string
     */
    protected function _colourResponse($message, $colour)
    {
        if (!array_key_exists($colour, $this->_colours)) {
            $colour = 'white';
        }

        if ($colour == 'white') {
            return $message;
        }

        return sprintf("\033[%dm%s\033[37m", $this->_colours[$colour], $message);
    }

    /**
     * Display message
     *
     * @param        $response
     * @param string $colour
     */
    protected function _showMessage($response, $colour = 'white')
    {
        echo $this->_colourResponse($response, $colour) . PHP_EOL;
    }

    /**
     * Append response
     *
     * @param        $response
     * @param string $colour
     *
     * @return $this
     */
    protected function _appendResponse($response, $colour = 'white')
    {
        $this->_response[] = $this->_colourResponse($response, $colour);

        return $this;
    }

    /**
     * Display response
     *
     * @return  void
     */
    protected function _displayResponse()
    {
        foreach ($this->_response as $response) {
            echo $response . PHP_EOL;
        }
    }

    /**
     * Return array of CLI commands
     *
     * @return  array
     */
    protected function _getCliCommand()
    {
        $cliCommands = [];

        foreach ($_SERVER['argv'] as $arg) {
            if (substr($arg, 0, 2) != '--' && strpos($arg, '=') === false) {
                $cliCommands[] = $arg;
            }
        }

        /**
         * Remove executed file name from result
         */
        unset($cliCommands[0]);

        return array_key_exists(1, $cliCommands) ? implode(' ', $cliCommands) : false;
    }

    /**
     * Return passed CLI option (2nd argument)
     *
     * @return  array
     */
    protected function _getCliOption()
    {
        $cliCommands = [];

        foreach ($_SERVER['argv'] as $arg) {
            if (substr($arg, 0, 2) != '--' && strpos($arg, '=') === false) {
                $cliCommands[] = $arg;
            }
        }

        return array_key_exists(2, $cliCommands) ? $cliCommands[2] : false;
    }

    /**
     * Return array of CLI options
     *
     * @return  array
     */
    protected function _getCliOptions()
    {
        $cliOptions = [];

        foreach ($_SERVER['argv'] as $arg) {
            if (substr($arg, 0, 2) == '--' && strpos($arg, '=') !== false) {
                $option = substr($arg, 2);
                $option = explode('=', $option);

                $cliOptions[$option[0]] = $option[1];
            }
        }

        return $cliOptions;
    }

    /**
     * @return $this
     */
    public function run()
    {
        return $this;
    }

    /**
     * @return int
     */
    protected function _getAdminStoreId()
    {
        return Mage_Core_Model_App::ADMIN_STORE_ID;
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * convert bytes into megabytes
     *
     * @param $bytes
     * @param int $precision
     *
     * @return string
     */
    protected function _formatBytes($bytes, $precision = 2)
    {
        $kilobyte = 1024;
        $megabyte = 1024 * 1024;

        if ($bytes >= 0 && $bytes < $kilobyte) {
            return $bytes . " b";
        }

        if ($bytes >= $kilobyte && $bytes < $megabyte) {
            return round($bytes / $kilobyte, $precision) . " kb";
        }

        return round($bytes / $megabyte, $precision) . " mb";
    }

    /**
     * Get website model by code.
     *
     * @param $websiteCode
     *
     * @return bool|Mage_Core_Model_Website
     */
    protected function _getWebsite($websiteCode)
    {
        $website = Mage::getModel('core/website')->load($websiteCode, 'code');

        if ($website->getId()) {
            return $website;
        }

        return false;
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
     * Create root category for new brand
     *
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
     * Create car finder category for new brand
     *
     * @param $rootCategory
     *
     * @return Mage_Catalog_Model_Category
     */
    public function createCarFinderCategory($rootCategory)
    {
        $category = Mage::getModel('catalog/category');
        $category->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->setName('Car Finder')
            ->setIsActive(1)
            ->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT)
            ->setPath($rootCategory->getPath())
            ->save();

        return $category;
    }

    /**
     * Method to create menu manager items via migration script.
     *
     * @param $data
     * @param $position
     * @param $menu
     * @param int $parentId
     *
     * @return $this
     */
    public function createMenuManagerMenu($data, $position, $menu, $parentId = 0)
    {
        if (!Mage::getModel('scandi_menumanager/item')) {
            $this->_appendResponse('Scandi Menu Manager module should be installed.', 'red');

            return $this;
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

        return $this;
    }

    /**
     * @param $code
     *
     * @return string
     */
    public function getStoreViewByCode($code)
    {
        return ($code !== '') ? strtolower(sprintf('%s_store_view', $code)) : false;
    }
}

/**
 * Class to process CSV import by chunks
 */
class CsvIterator
{
    /**
     * @var resource
     */
    protected $_file;

    /**
     * CsvIterator constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        $this->_file = fopen($file, 'r');
    }

    /**
     * Parse CSV row
     *
     * @return Generator|void
     */
    public function parse()
    {
        $headers = array_map('trim', fgetcsv($this->_file, 4096));
        while (!feof($this->_file)) {
            $row = array_map('trim', (array)fgetcsv($this->_file, 4096));

            if (count($headers) !== count($row)) {
                continue;
            }

            $row = array_combine($headers, $row);
            yield $row;
        }

        return;
    }
}
