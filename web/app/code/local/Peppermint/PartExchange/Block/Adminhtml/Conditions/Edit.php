<?php

/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */
class Peppermint_PartExchange_Block_Adminhtml_Conditions_Edit extends Rockar_PartExchange_Block_Adminhtml_Conditions_Edit
{
    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        return $this->__('Trade-in Slider/Checkbox Conditions');
    }
}
