<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once dirname(__FILE__) . '/../../app/Mage.php';

Mage::app();
Mage::register('peppermint_importer/mq_sales_data_batch', true);
Mage::helper('peppermint_importer/mq_salesData')->rabbitMqWorker();
