<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Model_Order_Status_History extends Mage_Sales_Model_Order_Status_History
{
    /**
     * {@inheritDoc}
     */
    protected function _beforeSave()
    {
        if ($this->getOrder() && $this->getOrder()->getId()) {
            $this->setParentId($this->getOrder()->getId());

            if (!$this->_validateParentId()) {
                $this->_dataSaveAllowed = false;
            }
        }

        return parent::_beforeSave();
    }

    /**
     * Validate if order Id exists
     *
     * @return int
     */
    protected function _validateParentId()
    {
        return Mage::getModel('sales/order')->getCollection()
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter('entity_id', $this->getOrder()->getId())
            ->setCurPage(1)
            ->setPageSize(1)
            ->getSize();
    }
}
