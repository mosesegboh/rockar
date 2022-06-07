<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Gcdm
 * @author    Alexander Metzgen <alexander.metzgen@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$mappedErros = [
    0 => [
        "error_code" => "1014-103",
        "error_message" => "You do not have the necessary access rights to use the application. Please contact BMW " .
        "customer service on 0800 600 555 or customer.service@bmw.co.za for assistance. {Error code:  1014-103}"
    ],
    1 => [
        "error_code" => "1014-102",
        "error_message" => "Your account has been locked. Please contact BMW customer service on 0800 600 555 or " .
        "customer.service@bmw.co.za for assistance. {Error code:  1014-102}"
    ],
    2 => [
        "error_code" => "1014-101",
        "error_message" => "The credentials you've entered cannot be authenticated at this time. " .
        "Please re-enter your details and try again. Kindly contact BMW customer service on 0800 600 555 " .
        "or customer.service@bmw.co.za if the issue continues. {Error code:  1014-101}"
    ],
    3 => [
        "error_code" => "1014-406",
        "error_message" => "Your account has been locked. " .
        "Please contact BMW customer service on 0800 600 555 or customer.service@bmw.co.za for assistance. " .
        "{Error code:  1014-406}"
    ],
    4 => [
        "error_code" => "1014-404",
        "error_message" => "Your account cannot be authenticated. " .
        "Please contact BMW customer service on 0800 600 555 or customer.service@bmw.co.za for assistance. " .
        "{Error code:  1014-404}"
    ],
    5 => [
        "error_code" => "1014-405",
        "error_message" => "Your session has timed out. Please log in again. {Error code:  1014-405}"
    ],
    6 => [
        "error_code" => "1014-403",
        "error_message" => "The information provided is not valid. " .
        "Please re-enter the required information. " .
        "Alternatively, you can contact BMW customer service on 0800 600 555 " .
        "or customer.service@bmw.co.za for assistance. " .
        "{Error code: 1014-403}"
    ],
    7 => [
        "error_code" => "1014-401",
        "error_message" => "Your customer profile could not be retrieved. " .
        "Please refresh your page and try again. " .
        "If the issue persists, please contact BMW customer service on 0800 600 555 or " .
        "customer.service@bmw.co.za for assistance. {Error code:  1014-401}"
    ],
    8 => [
        "error_code" => "1014-402",
        "error_message" => "You have exceeded the number of changes allowed to your customer profile. " .
        "Please contact BMW customer service on 0800 600 555 or customer.service@bmw.co.za for assistance. " .
        "{Error code:  1014-402}"
    ],
    9 => [
        "error_code" => "1014-200",
        "error_message" => "Your customer profile could not be retrieved. " .
        "Please contact BMW customer service on 0800 600 555 or customer.service@bmw.co.za for assistance. " .
        "{Error code:  1014-200}"
    ],
    10 => [
        "error_code" => "1014-300",
        "error_message" => "The information provided is not valid. " .
        "Please re-enter the required information and try again. " .
        "If the issue persists, please contact BMW customer service on 0800 600 555 or " .
        "customer.service@bmw.co.za for assistance. {Error code:  1014-300}"
    ],
    11 => [
        "error_code" => "2014-000",
        "error_message" => "An unexpected error occurred. " .
        "Please refresh your page and try again. " .
        "If the issue persists, please contact BMW customer service on 0800 600 555 or " .
        "customer.service@bmw.co.za for assistance.{Error code: 2014-000}"
    ],
    12 => [
        "error_code" => "Not one of the above",
        "error_message" => "An unexpected error occurred. " .
        "Please refresh your page and try again.  If the issue persists, " .
        "please contact BMW customer service on 0800 600 555 or customer.service@bmw.co.za for assistance. "
    ]
];
$installer->setConfigData('peppermint_gcdm/error_mapping/errors', serialize($mappedErros));

$installer->endSetup();
