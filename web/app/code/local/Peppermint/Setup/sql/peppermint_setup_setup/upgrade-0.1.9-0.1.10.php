<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Aleksejs Oboruns <techteam@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/**
 * Update column values for accepted order amend status setup
 */
$this->setConfigData('order_amend/order_status/accepted_amendment_order_logic', 2);
$this->setConfigData('order_amend/order_status/accepted_amendment_order_status', 'processing');
$this->setConfigData('order_amend/order_status/order_can_be_amended', 'processing,pending');
