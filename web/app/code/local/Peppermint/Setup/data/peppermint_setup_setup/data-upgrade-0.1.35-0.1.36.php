<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/**
 * Set php-amqplib heartbeat and read write timeout value
 * It is recommended for read write timeout value to be double that of heartbeat
 * `vendor/php-amqplib/php-amqplib/PhpAmqpLib/Wire/IO/StreamIO.php:47`
 */
$this->setConfigData('peppermint_import/general/rmq_read_write_timeout', 720);
$this->setConfigData('peppermint_import/general/rmq_heartbeat', 360);
