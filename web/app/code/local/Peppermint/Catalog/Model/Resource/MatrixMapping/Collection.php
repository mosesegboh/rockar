<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Resource_MatrixMapping_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('peppermint_catalog/matrixMapping');
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = count($this->getConnection()->fetchall($sql, $this->_bindParams));
        }

        return intval($this->_totalRecords);
    }
}
