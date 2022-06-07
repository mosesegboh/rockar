<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020  Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Sales_Model_Order_Document extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model's event name
     * @var string $_eventPrefix
     */
    protected $_eventPrefix = 'peppermint_sales_order_document';

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $this->setUpdatedAt(time());

        return parent::_beforeSave();
    }

    /**
     * _construct
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('peppermint_sales/order_document');
    }
}
