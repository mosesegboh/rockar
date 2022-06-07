<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Alexander Metzgen <alexander.metzgen@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Block_Adminhtml_Booking_Grid extends Rockar_YouDrive_Block_Adminhtml_Booking_Grid
{
    /**
     * Rewrites parent function to add booking placed column
     *
     * {@inheritDoc}
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter(
            'booking_placed',
            [
                'header' => $this->__('Booking Placed'),
                'index' => 'booking_placed',
                'type' => 'options',
                'options' => array_reverse(Mage::helper('peppermint_customer')->getRegistrationValues())
            ],
            'booked_at'
        );

        $this->addColumnAfter(
            'dealer_id',
            [
                'header' => $this->__('Booked By'),
                'index' => 'dealer_id',
                'type' => 'string'
            ],
            'booking_placed'
        );

        return $this;
    }
}
