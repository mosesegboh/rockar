<?php
/**
 * @category     Peppermint
 * @package      Peppermint\Customer
 * @author       Craig Goodspeed <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Document_Finance_Group extends Mage_Core_Model_Abstract
{
    /**
     * Magento post __construct init.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_customer/document_finance_group');
    }
}