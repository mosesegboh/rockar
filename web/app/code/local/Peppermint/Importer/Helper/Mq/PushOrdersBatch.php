<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Helper_Mq_PushOrdersBatch extends Peppermint_Importer_Helper_Mq_PushOrders
{
    const EXCHANGE_NAME = parent::EXCHANGE_NAME . '-BATCH';
}
