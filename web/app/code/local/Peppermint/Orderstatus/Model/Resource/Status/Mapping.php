<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderstatus_Model_Resource_Status_Mapping extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Magento post __construct init.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_orderstatus/status_mapping', 'id');
    }
}
