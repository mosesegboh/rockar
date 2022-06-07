<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Resource_Product_Fail_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * init model
     */
    public function _construct()
    {
        $this->_init('peppermint_importer/product_fail');
    }
}
