<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Observer_Vehicle extends Rockar2_Catalog_Model_Observer_Vehicle
{
    /**
     * Register active associated product
     */
    protected function _registerVehicle()
    {
        Mage::register('active_vehicle', $this->_getProduct());
    }
}
