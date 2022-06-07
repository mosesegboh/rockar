<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Model_Resource_Resend_Order extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Init.
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_dfe/resend_order', 'id');
    }
}
