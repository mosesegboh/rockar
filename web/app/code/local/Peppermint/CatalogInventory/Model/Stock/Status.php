<?php
/**
 * @category     Peppermint
 * @package      Peppermint_CatalogInventory
 * @author       Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogInventory_Model_Stock_Status extends Mage_CatalogInventory_Model_Stock_Status
{
    public function prepareCatalogProductIndexSelect(Varien_Db_Select $select, $entityField, $websiteField)
    {
        return $this;
    }
}
