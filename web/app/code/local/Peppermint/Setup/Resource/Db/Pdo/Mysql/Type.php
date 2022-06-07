<?php

class Peppermint_Setup_Resource_Db_Pdo_Mysql_Type extends Mage_Core_Model_Resource_Type_Db_Pdo_Mysql
{
    /**
     * Retrieve DB adapter class name
     *
     * @return string
     */
    protected function _getDbAdapterClassName()
    {
        return 'Peppermint_Setup_Resource_Db_Pdo_Mysql_Adapter';
    }
}
