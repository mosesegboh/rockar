<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Resource_Accessories_Missing_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * init model
     */
    public function _construct()
    {
        $this->_init('peppermint_importer/accessories_missing');
    }
}
