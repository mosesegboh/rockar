<?php

/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */
class Peppermint_PartExchange_Block_Adminhtml_Reports_View_Form extends Rockar_PartExchange_Block_Adminhtml_Reports_View_Form
{
    /**
     * Change field label and title
     *
     * @return Rockar_PartExchange_Block_Adminhtml_Reports_View_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $label = Mage::helper('rockar_partexchange')->__('Trade-in Value');
        $this->getForm()
            ->getElement('part_exchange_value')
            ->setLabel($label)
            ->setTitle($label);

        return $this;
    }
}
