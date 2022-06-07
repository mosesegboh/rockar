<?php
/**
 * Script that triggers MQ import for products delete operation.
 *
 * @category  Peppermint
 * @package   Peppermint_Shell
 * @author    Taras Kapushchak <info@scandiweb.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once dirname(__FILE__) . '/../../app/Mage.php';

Mage::app();
Mage::helper('peppermint_importer/mq_productsDelete')->rabbitMqWorker();
