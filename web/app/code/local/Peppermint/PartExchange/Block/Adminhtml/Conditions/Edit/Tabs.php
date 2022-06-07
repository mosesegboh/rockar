<?php

/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */
class Peppermint_PartExchange_Block_Adminhtml_Conditions_Edit_Tabs extends Rockar_PartExchange_Block_Adminhtml_Conditions_Edit_Tabs
{
    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTitle($this->__('Trade-in Conditions'));
    }
}
