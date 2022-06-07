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
class Rockar_Global_Website_Create extends Rockar_Shell_Abstract
{
    /**
     * Argument that is used to update global data
     */
    const GLOBAL_CONFIG_ARGUMENT = 'general';

    /**
     * Rockar theme fallback for global scope
     */
    const DEFAULT_THEME_FALLBACK = <<<EOF
[current]:[current]
[current]:default
base:default
EOF;

    /**
     * Brand specific theme fallback for website scope
     */
    const BRAND_THEME_FALLBACK = <<<EOF
[current]:%s
[current]:[current]
[current]:default
base:default
EOF;


    /**
     * Guest navigation menu items
     */
    const DEFAULT_NAVIGATION_ITEMS = array(
        'Car Finder' => array(
            'url' => 'car-finder',
            'identifier' => 'car_finder-guest',
            'css_class' => 'nav-link',
            'is_active' => true,
        ),
        'Test Drive' => array(
            'url' => 'test-drives',
            'identifier' => 'test_drives-guest',
            'css_class' => 'nav-link',
            'is_active' => true,
        ),
        'About Rockar' => array(
            'url' => 'about-rockar',
            'identifier' => 'about_rockar-guest',
            'css_class' => 'nav-link',
            'is_active' => true,
        ),
        'Service' => array(
            'url' => 'service-not-chore',
            'identifier' => 'service-guest',
            'css_class' => 'nav-link',
            'is_active' => true,
        ),
    );

    /**
     * Setup Magento global configs (currencies, locale, etc)
     */
    private function setGlobalConfigs()
    {
        $this->_showMessage('Setting Global Magento Configs', 'magenta');

        $dataToUpgrade = array(
            'currency/options/base' => 'GBP',
            'currency/options/default' => 'GBP',
            'currency/options/allow' => 'GBP',

            'general/locale/code' => 'en_GB',
            'general/locale/timezone' => 'Europe/London',
            'general/locale/firstday' => '1',

            'general/region/state_required' => 'GB',
            'general/region/display_all' => '0',

            'general/country/default' => 'GB',
            'general/country/allow' => 'GB',
            'general/store_information/name' => 'Rockar',
            'general/store_information/merchant_country' => 'GB',

            'design/fallback/fallback' => self::DEFAULT_THEME_FALLBACK,
            'design/package/name' => 'rockar',
            'design/theme/default' => 'default',

            'design/header/logo_src' => 'images/rockar-logo.png',
            'design/header/logo_alt' => 'Rockar Logo',

            'catalog/frontend/flat_catalog_category' => '1',
            'catalog/frontend/flat_catalog_product' => '1',

            'catalog/product_image/base_width' => '1800',
            'catalog/product_image/small_width' => '403',
            'catalog/product_image/small_height' => '206',
            'catalog/product_image/max_dimension' => '5000',

            'catalog/seo/product_url_suffix' => '',
            'catalog/seo/category_url_suffix' => '',
            'web/seo/use_rewrites' => '1',

            // Tax configurations
            'tax/classes/shipping_tax_class' => '2',
            'tax/classes/wrapping_tax_class' => '2',
            'tax/calculation/price_includes_tax' => '1',
            'tax/calculation/shipping_includes_tax' => '1',
            'tax/calculation/discount_tax' => '1',
            'tax/defaults/country' => 'GB',

            'tax/display/type' => '2',
            'tax/display/shipping' => '2',

            'tax/cart_display/price' => '2',
            'tax/cart_display/subtotal' => '2',
            'tax/cart_display/shipping' => '2',
            'tax/cart_display/gift_wrapping' => '2',
            'tax/cart_display/printed_card' => '2',
            'tax/cart_display/grandtotal' => '1',

            'tax/sales_display/price' => '2',
            'tax/sales_display/subtotal' => '2',
            'tax/sales_display/shipping' => '2',
            'tax/sales_display/gift_wrapping' => '2',
            'tax/sales_display/printed_card' => '2',
            'tax/sales_display/grandtotal' => '1',

            // Trade in
            'partexchange/requests_limit/default' => '3000',
            'partexchange/requests_limit/instore' => '3000',
            'partexchange/requests_limit/duration' => '360',
        );

        foreach ($dataToUpgrade as $node => $value) {
            Mage::getConfig()->saveConfig($node, $value);
        }

        $this->_showMessage('Global Magento configs has been updated.', 'yellow');

        return $this;
    }

    /**
     * Update brand specific system config data.
     *
     * @param $prefix
     *
     * @return $this
     */
    private function setBrandConfigs($prefix)
    {
        $code = $this->prepareCodeFromPrefix($prefix);
        $website = Mage::getModel('core/website')->load($code, 'code');

        $this->_showMessage('Updating brand specific config data', 'magenta');

        if ($website->getId()) {
            $dataToUpgrade = array(
                'design/fallback/fallback' => sprintf(self::BRAND_THEME_FALLBACK, $code),
                'design/head/default_title' => $prefix,
            );

            foreach ($dataToUpgrade as $node => $value) {
                Mage::getConfig()->saveConfig($node, $value, 'websites', $website->getId());
            }

            $this->_showMessage('Brand specific system config data has been updated', 'yellow');
        } else {
            $this->_showMessage(sprintf('"%s" website was not found', $code), 'red');
        }

        return $this;
    }

    /**
     * Generate brand specific website, store, store view, category data
     *
     * @param $prefix
     *
     * @return $this
     */
    private function generateWebsite($prefix)
    {
        $code = $this->prepareCodeFromPrefix($prefix);

        /**
         * If such website already exists, skip website generation process.
         */
        if ($this->_getWebsite($code)) {
            $this->_showMessage(sprintf('"%s" website already exists in the system, skipping this step.', $prefix), 'red');
            return $this;
        }

        $this->_showMessage(sprintf('Creating website/store/store view data for "%s" brand.', $prefix), 'magenta');

        $rootCategory = $this->createRootCategory($prefix);

        $websiteData = array(
            'code' => $code,
            'name' => $prefix . ' Website',
        );

        $website = $this->createWebsite($websiteData);

        $carFinderCat = $this->createCarFinderCategory($rootCategory);
        Mage::getConfig()->saveConfig('rockar_catalog/general/default_category', $carFinderCat->getId(), 'websites', $website->getId());

        $storeGroupData = array(
            'website_id' => $website->getId(),
            'name' => $prefix . ' Store Group',
            'root_category_id' => $rootCategory->getId(),
        );

        $storeGroup = $this->createStoreGroup($storeGroupData);

        $storeViewData = array(
            'code' => $code . '_store_view',
            'website_id' => $website->getId(),
            'group_id' => $storeGroup->getId(),
            'name' => $prefix . ' Store View',
            'is_active' => '1',
        );

        $store = $this->createStoreView($storeViewData);
        $storeGroup->setDefaultStoreId($store->getId())
            ->save();

        $this->_showMessage('Website data has been generated.', 'yellow');

        return $this;
    }

    /**
     * Generate main navigation for guest/registered user
     *
     * @param $prefix
     *
     * @return $this
     */
    private function generateMainNavigation($prefix)
    {
        $code = $this->prepareCodeFromPrefix($prefix);

        $this->_showMessage(sprintf('Creating main navigation for "%s" brand.', $prefix), 'magenta');
        $navigationModel = Mage::getModel('scandi_menumanager/menu');

        /**
         * If there is such navigation already, skip this step
         */
        if ($navigationModel->load(sprintf('%s-main-navigation', $code), 'identifier')->getId()) {
            $this->_showMessage(sprintf('"%s" navigation identifier already exists.', sprintf('%s-main-navigation', $code)), 'red');

            return $this;
        }

        $navigation = Mage::getModel('scandi_menumanager/menu')
            ->setIdentifier(sprintf('%s-main-navigation', $code))
            ->setTitle(sprintf('Main Navigation (%s)', $prefix))
            ->setStores(array(Mage::app()->getStore($this->getStoreViewByCode($code))->getId()))
            ->setType('none')
            ->setCssClass('navigation')
            ->setIsActive(true)
            ->save();

        $this->createMenuManagerMenu(self::DEFAULT_NAVIGATION_ITEMS, 10, $navigation);
        $this->_showMessage('Main navigation has been generated.', 'yellow');

        return $this;
    }

    /**
     * Run script that will prepare initial website data
     */
    public function run()
    {
        $brand = $this->_getCliCommand();

        switch ($brand) {
            case self::GLOBAL_CONFIG_ARGUMENT:
                $this->setGlobalConfigs();
                $this->_showMessage('==========================================', 'yellow');
                $this->_showMessage('DONE.', 'green');
                break;
            case ($brand !== ''):
                $this->_showMessage(sprintf('Starting "%s" Website Creation Process', $brand), 'magenta');

                $this->generateWebsite($brand);
                $this->generateMainNavigation($brand);
                $this->setBrandConfigs($brand);

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
Usage:  php -f website_create.php -- [BRAND_NAME],

Available commands:
help                                    This help

Example:
php -f website_create.php -- Hyundai

USAGE;
    }
}

$shell = new Rockar_Global_Website_Create();
$shell->run();
