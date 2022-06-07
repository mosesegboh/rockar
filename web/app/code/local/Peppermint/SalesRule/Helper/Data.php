<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Helper_Data extends Rockar_SalesRule_Helper_Data
{
    /**
     * {@inheritDoc}
     * Added default date format, to not rely on browser locale
     *
     * @param string|null $date
     * @return Zend_Date
     * @throws Zend_Date_Exception
     */
    protected function _getDateObject($date = null)
    {
        return new Zend_Date($date, 'yyyy-MM-dd');
    }
}
