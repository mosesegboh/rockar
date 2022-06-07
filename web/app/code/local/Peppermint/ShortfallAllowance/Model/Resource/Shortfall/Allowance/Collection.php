<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ShortfallAllowance_Model_Resource_Shortfall_Allowance_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Class constructor
     */
    public function _construct()
    {
        $this->_init('peppermint_shortfallallowance/shortfall_allowance');
    }
}
