<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Sergejs Plisko <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Model_Resource_Bank_Branches extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_dfe/bank_branches', 'id');
    }

    /**
     * Insert and updates table based on provided data
     *
     * @param array $data
     *
     * @return Peppermint_Dfe_Model_Resource_Bank_Branches $this
     */
    public function sync(array $data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $adapter->insertOnDuplicate(
                $this->getMainTable(),
                $data,
                [
                    'bank_name',
                    'bank_code',
                    'branch_name'
                ]
            );

            $adapter->commit();
        } catch (Exception $e) {
            Mage::logException($e);
            $adapter->rollBack();
        }

        return $this;
    }
}
