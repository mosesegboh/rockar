<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Model_AdditionalData extends Mage_Core_Model_Abstract
{
    /**
     * Constansts for locking and unlocking amendment
     */
    public const CAN_AMEND_UNLOCK = 1;
    public const CAN_AMEND_LOCK = 0;

    protected function _construct()
    {
        $this->_init('peppermint_sales/additionalData');
    }
}
