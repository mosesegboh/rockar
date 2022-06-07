<?php
/**
 * @category  Rockar
 * @package   Rockar_Localstores
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Localstores_Model_Resource_Distances_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Constructor. Set basic parameters.
     * @return void
     */
    public function _construct()
    {
        $this->_init('peppermint_localstores/distances');
    }
}
