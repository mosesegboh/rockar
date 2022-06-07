<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Helper_Booking_Status extends Rockar_YouDrive_Helper_Booking_Status
{
    /**
     * Booking statuses
     */
    const BOOKING_STATUS_REQUESTED = 8;

    /**
     * get booking statuses as JSON
     *
     * @return array
     */
    public function getBookingStatusesArray()
    {
        return parent::getBookingStatusesArray() + [self::BOOKING_STATUS_REQUESTED => $this->__('Requested')];
    }

    /**
     * Get requested test drive statuses
     *
     * @return array
     */
    public function getRequestedStatuses()
    {
        return [self::BOOKING_STATUS_REQUESTED];
    }

}