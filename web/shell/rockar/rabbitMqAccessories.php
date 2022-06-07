<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once dirname(__FILE__) . '/../../app/Mage.php';

Mage::app();
Mage::helper('peppermint_importer/mq_accessories')->rabbitMqWorker();
