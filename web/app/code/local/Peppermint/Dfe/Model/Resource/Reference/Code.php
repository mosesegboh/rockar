<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Model_Resource_Reference_Code extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('peppermint_dfe/reference_code', 'id');

        $this->addUniqueField([
            'field' => ['category', 'code'],
            'title' => Mage::helper('peppermint_dfe')->__('Unique combo for category/code')
        ]);
    }

    /**
     * Insert and updates table based on provided data
     *
     * @param [] $rows
     * @return Peppermint_Dfe_Model_Resource_Reference_Code
     */
    public function sync(array $rows)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        $adapter->update($this->getMainTable(), ['is_deleted' => 1]);

        $adapter->insertOnDuplicate(
            $this->getMainTable(),
            $rows,
            ['description', 'is_deleted']
        );

        $adapter->commit();

        return $this;
    }
}
