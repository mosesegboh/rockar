<?php
/**
 * @category     Peppermint
 * @package      Peppermint\Customer
 * @author       Craig Goodspeed <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Resource_Document_Type extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Magento post __construct init.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_customer/document_type', 'id');
    }
}