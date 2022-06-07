<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

abstract class Peppermint_Importer_Helper_Abstract_Mq_Write extends Peppermint_Importer_Helper_Abstract_Mq
{
    const EXCHANGE_NAME = 'DSP-TO-SPC';

    /**
     * Operation (read/write) based init functionality.
     *
     * @return void
     */
    protected function _init()
    {
        /*
         * name: static::EXCHANGE_NAME
         * type: direct
         * passive: false
         * durable: false // the exchange will survive server restarts
         * auto_delete: false //the exchange won't be deleted once the channel is closed.
         */

        $this->_rmqChannel->exchange_declare(static::EXCHANGE_NAME, AMQPExchangeType::DIRECT, false, false, false);
        $this->_rmqChannel->queue_bind(Mage::getStoreConfig(static::NS_RMQ_QUEUE_NAME), static::EXCHANGE_NAME);

        parent::_init();
    }

    /**
     * The actual preparation of message implementation.
     *
     * @param [] $messageParts
     * @throws Mage_Core_Exception
     * @return string
     */
    protected function _doPrepareMessage(array $messageParts)
    {
        return json_encode($messageParts);
    }

    /**
     * Write message final method.
     *
     * @param [] $messageParts
     * @throws Mage_Core_Exception
     * @return Peppermint_Importer_Helper_Abstract_Mq_Write
     */
    final public function writeMessage(array $messageParts)
    {
        try {
            $this->_rmqChannel->basic_publish(
                new AMQPMessage(
                    $this->_doPrepareMessage($messageParts),
                    [
                        'content_type' => 'text/json',
                        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                    ]
                ),
                static::EXCHANGE_NAME
            );
        } catch (Exception $e) {
            Mage::logException($e);
            usleep(Mage::getStoreConfig(self::NS_SLEEP_TIME));
            $this->_disconnect()
                ->_connect()
                ->writeMessage($messageParts);
        }

        return $this;
    }
}
