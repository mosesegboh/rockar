<?php
 /**
 * @category  Peppermint
 * @package   Peppermint\OrderCap
 * @author    Lika Sikharula <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OrderCap_Model_OrderCap extends Mage_Reports_Model_Mysql4_Order_Collection 
{
    /**
     * {@inheritdoc}
     */
    public function __construct() {
        parent::__construct();
        $this->setResourceModel('rockar_localstores/stores');
        $this->_init('rockar_localstores/stores');
    }
}