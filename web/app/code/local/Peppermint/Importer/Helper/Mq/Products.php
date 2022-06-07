<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Helper_Mq_Products extends Peppermint_Importer_Helper_Abstract_Mq_Read
{
    const NS_RMQ_QUEUE_NAME = 'peppermint_import/general/rmq_products_queue';
    const PROCESS_NAME = 'digestProducts';

    /**
     * Adding extra blocking processes.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_addBlockingProcessName(Peppermint_Importer_Helper_Mq_Attributes::PROCESS_NAME);
        $this->_addBlockingProcessName(Peppermint_Importer_Helper_Mq_Orders::PROCESS_NAME);
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
            Mage::getModel('peppermint_importer/product')->processProducts($message->getBody());
        } catch (Mage_Core_Exception $e) {
            $e->setMessage('Importer of products has encountered an exception', true);
            Mage::logException($e);
            throw $e;
        }

        return $this;
    }
}
