<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Helper_Mq_SalesData extends Peppermint_Importer_Helper_Abstract_Mq_Read
{
    const NS_RMQ_QUEUE_NAME = 'peppermint_import/general/rmq_sales_data_queue';
    const PROCESS_NAME = 'digestSalesData';

    /**
     * Adding extra blocking processes.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_addBlockingProcessName(Peppermint_Importer_Helper_Mq_Products::PROCESS_NAME);
    }

    /**
     * The actual process of messages implementation.
     *
     * @param PhpAmqpLib\Message\AMQPMessage $message
     * @throws Mage_Core_Exception
     * @return Peppermint_Importer_Helper_Abstract_Mq_Read
     */
    protected function _doProcessQueue(PhpAmqpLib\Message\AMQPMessage $message)
    {
        try {
            Mage::getModel('peppermint_importer/salesData')->processSalesData($message->getBody());
        } catch (Mage_Core_Exception $e) {
            $e->setMessage('Process sales data has encountered an exception', true);
            Mage::logException($e);
            throw $e;
        }

        return $this;
    }
}
