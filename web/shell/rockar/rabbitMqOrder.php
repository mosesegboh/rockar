<?php
require_once dirname(__FILE__) . '/../../app/Mage.php';
Mage::app();
Mage::register('peppermint_importer/mq_orders_batch', true);
Mage::helper('peppermint_importer/mq_orders')->rabbitMqWorker();
