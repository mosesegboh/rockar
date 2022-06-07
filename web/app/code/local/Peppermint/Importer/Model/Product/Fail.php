<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Product_Fail extends Mage_Core_Model_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'peppermint_importer_product_fail';

    /**
     * Init model
     */
    protected function _construct()
    {
        $this->_init('peppermint_importer/product_fail');
    }
}
