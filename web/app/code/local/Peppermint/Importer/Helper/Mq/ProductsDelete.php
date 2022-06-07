<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Taras Kapushchak <info@scandiweb.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Helper_Mq_ProductsDelete extends Peppermint_Importer_Helper_Mq_Products
{
    const NS_RMQ_QUEUE_NAME = 'peppermint_import/general/rmq_products_delete_queue';
    const PROCESS_NAME = 'digestProductsDelete';
}
