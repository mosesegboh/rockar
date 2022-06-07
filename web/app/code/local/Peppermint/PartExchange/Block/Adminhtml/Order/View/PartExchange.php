<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Block_Adminhtml_Order_View_PartExchange extends Rockar_PartExchange_Block_Adminhtml_Order_View_PartExchange
{
    /**
     * Retrieve text fieldset header element
     *
     * @return string
     */
    public function getEntityTitle()
    {
        return $this->__('Trade-in Information');
    }
}