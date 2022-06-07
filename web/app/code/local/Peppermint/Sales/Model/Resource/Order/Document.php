<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020  Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Sales_Model_Resource_Order_Document extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * _construct
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('peppermint_sales/order_document', 'id');
    }
}
