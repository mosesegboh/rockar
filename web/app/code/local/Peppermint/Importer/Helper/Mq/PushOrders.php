<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Helper_Mq_PushOrders extends Peppermint_Importer_Helper_Abstract_Mq_Write
{
    const NS_RMQ_QUEUE_NAME = 'peppermint_import/general/rmq_order_push_queue';
    const PROCESS_NAME = 'pushOrders';
    const EXCHANGE_NAME = parent::EXCHANGE_NAME . '.PUSH-ORDERS';
}
