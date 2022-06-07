<?php
/**
 * @category  Peppermint
 * @package   Peppermint/Customer
 * @author    Craig Goodspeed
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Resource_Document_LookupAbstract extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @var array, key value pairs for lookup information.
     */
    private $_cache;

    /**
     * Default method, can be overwritten by inheriting classes.
     * @param Mage_Core_Model_Abstract $toIndex
     * @return mixed String
     */
    public function getHashMapIndexer(Mage_Core_Model_Abstract $toIndex)
    {
        return $toIndex->getName();
    }

    /**
     * Creates an in memory object - so we dont have to go to the database to fetch the table
     * designed for small lookup tables.
     *
     * @return mixed | object describing the table.
     */
    public function getLookupCache()
    {
        if (!$this->_cache) {
            foreach ($this as $item) {
                $this->_cache[$this->getHashMapIndexer($item)] = $item;
            }
        }

        return $this->_cache;
    }
}