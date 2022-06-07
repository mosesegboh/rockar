<?php

/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */
class Peppermint_PartExchange_Block_Adminhtml_Reports extends Rockar_PartExchange_Block_Adminhtml_Reports
{
    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('rockar_partexchange')->__('Trade-in Valuations Reports');
    }
}
