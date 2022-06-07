<?php

/**
 * @category     Peppermint
 * @package      Peppermint_FinancingOptions
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Resource_Options_Variables_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_financingoptions/options_variables');
    }
}
