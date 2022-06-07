<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Helper_Booking_Progress extends Rockar_YouDrive_Helper_Booking_Progress
{
    /**
     * Default values for initialization
     */
    protected $_data = [
        'carId' => [],
        'id' => 0,
        'modelIds' => [],
        'options' => [],
        'image' => null,
        'bookingId' => 0,
        'bookingDatetime' => null,
        'bookingDatetimeSet' => false,
        'title' => '',
        'subtitle' => '',
        'assignedTo' => null
    ];
}
