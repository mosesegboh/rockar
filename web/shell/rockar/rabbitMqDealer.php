<?php
require_once dirname(__FILE__) . '/../../app/Mage.php';
Mage::app();
Mage::helper('peppermint_importer/mq_dealers')->rabbitMqWorker();
