<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_LeadTime_Model_Lead_Time extends Rockar_LeadTime_Model_Lead_Time
{
    /**
     * Set amount to '0' or '1' only
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if ((int) $this->getData('amount') >= 1) {
            $this->setData('amount', 1);
        } else {
            $this->setData('amount', 0);
        }

        return parent::_beforeSave();
    }
}
