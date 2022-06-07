<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_Rule_Product extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('peppermint_catalogrule/rule_product');
    }
}
