<?php

 /**
 * Script that triggers MQ import for attributes
 *
 * @category     Peppermint
 * @package      Peppermint\Shell
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once dirname(__FILE__) . '/../../../app/Mage.php';

Mage::app();
Mage::helper('peppermint_importer/mq_attributes')->rabbitMqWorker();
