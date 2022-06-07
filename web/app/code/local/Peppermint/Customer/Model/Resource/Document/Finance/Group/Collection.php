<?php
/**
 * @category  Peppermint
 * @package   Peppermint/Customer
 * @author    Craig Goodspeed
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Resource_Document_Finance_Group_Collection extends Peppermint_Customer_Model_Resource_Document_LookupAbstract
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