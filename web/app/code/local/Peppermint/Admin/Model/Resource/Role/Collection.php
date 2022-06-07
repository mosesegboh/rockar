<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Model_Resource_Role_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Peppermint_Admin_Model_Resource_Role_Collection constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_admin/role');
    }
}
