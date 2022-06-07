<?php
require_once dirname(__FILE__) . '/../abstract.php';

/**
 * Script to remove values from Entity Integer Table which matches in Entity Varchar Table
 *
 * @category Rockar
 * @package Rockar\Shell
 * @author Arturs Paklins <info@scandiweb.com>
 * @copyright Copyright (c) 2017 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */
class Rockar_Shell_Remove_Attribute_Duplicates extends Rockar_Shell_Abstract
{
    /**
     * Run the script
     */
    public function run()
    {
        echo "Running the query...\n";
        $queryString = "DELETE FROM `catalog_product_entity_int` WHERE `attribute_id` IN (SELECT `attribute_id` FROM `catalog_product_entity_varchar`);";
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $writeConnection->query($queryString);
        echo "Done. \n";
    }
}

$shell = new Rockar_Shell_Remove_Attribute_Duplicates();
$shell->run();
