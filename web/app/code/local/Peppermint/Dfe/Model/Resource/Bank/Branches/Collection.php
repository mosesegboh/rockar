<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Sergejs Plisko <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Model_Resource_Bank_Branches_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('peppermint_dfe/bank_branches');
    }
}
