<?php

/**
 * @category     Peppermint
 * @package      Peppermint_PartialPayment
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_PartialPayment_Model_Payment extends Rockar_PartialPayment_Model_Payment
{

    /**
     * Trigger created_at, updated_at column & payment status updates on record save.
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getId()) {
            $this->setData('created_at', Varien_Date::now());
            $this->setData('payment_status', 'paid');
        }
        $this->setData('updated_at', Varien_Date::now());

        return $this;
    }
}
