<?php
require_once dirname(__FILE__) . '/../abstract.php';

/**
 * Script to remove website related data:
 * - stores
 * - products
 *
 * @category Rockar
 * @package Rockar\Shell
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2016 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
class Rockar_Global_Website_Removal extends Rockar_Shell_Abstract
{
    /**
     * Remove all products from particular website.
     *
     * @param $brand
     *
     * @return int
     */
    protected function _removeProducts($brand)
    {
        $code = $this->prepareCodeFromPrefix($brand);
        $this->_showMessage('Removing Product Data', 'magenta');
        $size = 0;

        if ($website = $this->_getWebsite($code)) {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addWebsiteFilter($website->getId());

            if ($collection instanceof Mage_Catalog_Model_Resource_Product_Collection) {
                $size = $collection->getSize();
                $collection->walk('delete');
            }
        }

        $this->_showMessage(sprintf('%s products were deleted', $size), 'yellow');

        return $this;
    }

    /**
     * @param $brand
     */
    protected function _removeCategories($brand)
    {
        $this->_showMessage('Removing Categories', 'magenta');
        $size = 0;

        if ($rootCategory = $this->_getRootCategory($brand)) {
            $childCategories = $rootCategory->getChildrenCategoriesWithInactive();
            $size += $childCategories->getSize();

            if ($childCategories instanceof Mage_Catalog_Model_Resource_Category_Collection) {
                $childCategories->walk('delete');
            }

            $size += $rootCategory->delete();
        }

        $this->_showMessage(sprintf('%s categories were deleted', $size), 'yellow');
    }

    /**
     * @param $brand
     *
     * @return bool|Mage_Catalog_Model_Category
     */
    protected function _getRootCategory($brand)
    {
        $rootCategory = Mage::getModel('catalog/category')->getCollection()
            ->addFieldToFilter('name', array('like' => '%' . $brand . '%'))
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();

        if ($rootCategory && $rootCategory->getId()) {
            return $rootCategory;
        }

        return false;
    }

    /**
     * @param $brand
     */
    protected function _removeWebsiteData($brand)
    {
        $this->_showMessage('Removing Website Data', 'magenta');

        /**
         * @var $storeGroup Mage_Core_Model_Store_Group
         */
        $storeGroup = Mage::getModel('core/store_group')->getCollection()
            ->addFieldToFilter('name', array('like' => '%' . $brand . '%'))
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();

        if ($storeGroup && $storeGroup->getId()) {
            $websiteToDelete = $storeGroup->getWebsite();
            $storesToDelete = $storeGroup->getStoreCollection();

            if ($websiteToDelete) {
                $websiteToDelete->delete();
                $this->_showMessage('Website was deleted.', 'yellow');
            }

            if ($storesToDelete instanceof Mage_Core_Model_Resource_Store_Collection) {
                $storeAmount = $storesToDelete->getSize();
                $this->_showMessage(sprintf('%s stores were deleted.', $storeAmount), 'yellow');
                $storesToDelete->walk('delete');
            }

            $storeGroup->delete();
            $this->_showMessage('Store group was deleted.', 'yellow');
        }

        $this->_showMessage('Website Data was deleted successfully.', 'yellow');
    }

    /**
     * Remove Scandi Menu Manager navigation
     *
     * @param $brand
     *
     * @return $this
     */
    protected function _removeMainNavigation($brand)
    {
        $code = $this->prepareCodeFromPrefix($brand);
        $this->_showMessage(sprintf('Removing main navigation for "%s" brand.', $brand), 'magenta');

        $navigation = Mage::getModel('scandi_menumanager/menu')->load(sprintf('%s-main-navigation', $code), 'identifier');

        if ($navigation->getId()) {
            $navigation->delete();
        }

        $this->_showMessage(sprintf('Main navigation has been removed.', $brand), 'yellow');

        return $this;
    }

    public function run()
    {
        $brand = $this->_getCliCommand();

        switch ($brand) {
            case ($brand !== ''):
                $this->_showMessage(sprintf('Starting "%s" Website Removal Process', $brand), 'magenta');

                $this->_removeProducts($brand);
                $this->_removeWebsiteData($brand);
                $this->_removeCategories($brand);
                $this->_removeMainNavigation($brand);

                $this->_showMessage('==========================================', 'yellow');
                $this->_showMessage('DONE.', 'green');
                break;
            case 'help':
                /* Usage help or unrecognised command */
                $this->_appendResponse($this->usageHelp());
                break;
            default:
                $this->_appendResponse('Invalid command', 'red');
                $this->_appendResponse('Please add "-- help" parameter to show script usage.', 'green');
        }

        $this->_displayResponse();
    }

    /**
     * Retrieve Usage Help Message
     *
     * @return  string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f website_removal.php -- [BRAND_NAME],

Available commands:
help                                    This help

Example:
php -f website_create.php -- Hyundai

USAGE;
    }
}

$shell = new Rockar_Global_Website_Removal();
$shell->run();
