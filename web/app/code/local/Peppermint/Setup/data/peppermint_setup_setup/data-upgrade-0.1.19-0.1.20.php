<?php
/**
 * @category  Setup
 * @package   Peppermint_Setup
 * @author    Bogdan Gafitescu <bogdan.gafitescu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/**
 * Set transactional emails templates.
 */

$this->startSetup();

$this->setConfigData('sales_email/customer_documents/template_customer', '7', 'default', 0);
$this->setConfigData('sales_email/customer_documents/template_customer_from_admin', '6', 'default', 0);
$this->setConfigData('sales_email/order/enabled', '1', 'default', 0);
$this->setConfigData('sales_email/order/template', '11', 'default', 0);
$this->setConfigData('sales_email/rockar_order_cancel/template', '21', 'default', 0);
$this->setConfigData('sales_email/rockar_collect/enabled', '1', 'default', 0);
$this->setConfigData('sales_email/rockar_collect/email', '22', 'default', 0);
$this->setConfigData('partexchange/partexchange_expiry/part_exchange_lead_time_email_status', '1', 'default', 0);
$this->setConfigData('partexchange/partexchange_expiry/part_exchange_lead_time', '1', 'default', 0);
$this->setConfigData('partexchange/partexchange_expiry/part_exchange_lead_time_email', '34', 'default', 0);

$this->endSetup();
