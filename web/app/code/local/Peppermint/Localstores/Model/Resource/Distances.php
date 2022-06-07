<?php
/**
 * @category  Rockar
 * @package   Rockar_Localstores
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Localstores_Model_Resource_Distances extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor. Set basic parameters.
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_localstores/distances', 'id');

        $this->addUniqueField([
            'field' => [
                'from_store_id',
                'to_store_id'
            ],
            'title' => Mage::helper('peppermint_localstores')->__('Unique combo for from_store_id/to_store_id')
        ]);
    }

    /**
     * Insert and updates table based on provided data.
     *
     * @param [] $rows
     * @return Peppermint_Localstores_Model_Resource_Distances
     */
    public function sync(array $rows)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $adapter->insertOnDuplicate(
                $this->getMainTable(),
                $rows,
                ['distance']
            );
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $adapter->commit();

        return $this;
    }
}
