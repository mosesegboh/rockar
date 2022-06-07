<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

use PhpAmqpLib\Message\AMQPMessage;

abstract class Peppermint_Importer_Helper_Abstract_Mq_Read extends Peppermint_Importer_Helper_Abstract_Mq
{
    /**
     * Operation (read/write) based init functionality.
     *
     * @return void
     */
    protected function _init()
    {
        /*
         * 1. #prefetch size - prefetch window size in octets, null meaning "no specific limit"
         * 2. #prefetch count - prefetch window in terms of whole messages,how many messages to retrieve per worker
         *                      before sending back an acknowledgement. This will make the worker to process 1 message at a time.
         * 3. #global - global=null to mean that the QoS settings should apply per-consumer,
         *              global=true to mean that the QoS settings should apply per-channel,
         *              global=null means that above settings will apply to this consumer only
         */
        $this->_rmqChannel->basic_qos(null, 1, null);

        parent::_init();
    }

    /**
     * Listens RabbitMq for incoming messages, this method is called by magento cron.
     *
     * @return void
     */
    public function rabbitMqWorker()
    {
        /*
         * 1. #queue
         * 2. #consumer tag - Identifier for the consumer, valid within the current channel. just string
         * 3. #no local - TRUE: the server will not send messages to the connection that published them
         * 4. #no ack - send a proper acknowledgment from the worker, once we're done with a task
         * 5. #exclusive - queues may only be accessed by the current connection
         * 6. #no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
         * 7. #callback - method that will receive the message
         */
        $this->_rmqChannel->basic_consume(
            Mage::getStoreConfig(static::NS_RMQ_QUEUE_NAME),
            Mage::getStoreConfig(static::NS_RMQ_CONSUMER_TAG),
            false,
            false,
            false,
            false,
            [
                $this,
                'processQueue'
            ]
        );

        while ($this->_rmqChannel->is_consuming()) {
            $this->_rmqChannel->wait();
        }
        $this->_shutdown();
    }

    /**
     * The actual process of messages implementation.
     *
     * @param AMQPMessage $message
     * @throws Mage_Core_Exception
     * @return Peppermint_Importer_Helper_Abstract_Mq_Read
     */
    abstract protected function _doProcessQueue(AMQPMessage $message);

    /**
     * Callback method that will receive queue message and sending to be processed.
     *
     * @param AMQPMessage $message
     * @throws Mage_Core_Exception
     * @return void
     */
    final public function processQueue(AMQPMessage $message)
    {
        if ($this->_canRun()) {
            $this->_handleLock()
                ->_doProcessQueue($message)
                ->_postProcess($message)
                ->_handleUnlock();
        } else {
            $this->_reject($message);
        }

        usleep(Mage::getStoreConfig(self::NS_SLEEP_TIME));
    }

    /**
     * Callback method that must be called for acknowledge processed messages.
     *
     * @param AMQPMessage $message
     * @return Peppermint_Importer_Helper_Abstract_Mq_Read
     */
    protected function _postProcess(AMQPMessage $message)
    {
        // very important line for message acknowledgment
        $this->_rmqChannel->basic_ack($message->getDeliveryTag());

        // Send a message with the string "quit" to cancel the consumer.
        if ($message->getBody() === 'quit') {
            $this->_cancel($message);
        }

        return $this;
    }
}
