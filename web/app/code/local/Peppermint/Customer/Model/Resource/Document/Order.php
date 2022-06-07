<?php
/**
 * @category     Peppermint
 * @package      Peppermint\Customer
 * @author       Craig Goodspeed <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Resource_Document_Order extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Magento post __construct init.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_customer/document_order', 'id');
    }

    /**
     * {@inheritDoc}
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $dateNow = Mage::getSingleton('core/date')->gmtDate();
        $object['date_updated'] = $dateNow;
        $object['date_created'] = $object['date_created'] ?? $dateNow;

        return parent::_beforeSave($object);
    }
}
