<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class Peppermint_Importer_Helper_Abstract_Mq extends Mage_Core_Helper_Abstract
{
    const NS_RMQ_HOST = 'peppermint_import/general/rmq_host';
    const NS_RMQ_PORT = 'peppermint_import/general/rmq_port';
    const NS_RMQ_USER = 'peppermint_import/general/rmq_user';
    const NS_RMQ_PASS = 'peppermint_import/general/rmq_password';
    const NS_RMQ_VHOST = 'peppermint_import/general/rmq_vhost';
    const NS_RMQ_LOGIN_METHOD = 'peppermint_import/general/rmq_login_method';
    const NS_RMQ_LOCALE = 'peppermint_import/general/rmq_locale';
    const NS_RMQ_CONN_TIMEOUT = 'peppermint_import/general/rmq_connection_timeout';
    const NS_RMQ_READ_WRITE_TIMEOUT = 'peppermint_import/general/rmq_read_write_timeout';
    const NS_RMQ_HEARTBEAT = 'peppermint_import/general/rmq_heartbeat';
    const NS_RMQ_QUEUE_NAME = 'XML_PATH_DOESNT_EXIST';
    const NS_RMQ_CONSUMER_TAG = 'peppermint_import/general/rmq_consumer_tag';
    const NS_SLEEP_TIME = 'peppermint_import/general/sleep_time';
    const PROCESS_NAME = 'PROCESS_NAME_TO_BE_DEFINED';

    /**
     * @var string[]
     */
    protected $_blockingProcessesNames = [];

    /**
     * @var null|AMQPStreamConnection
     */
    protected $_rmqConnection;

    /**
     * @var null|AMQPChannel
     */
    protected $_rmqChannel;

    /**
     * @var null|Mage_Index_Model_Lock
     */
    protected $_lockInstance;

    /**
     * Initialization of the channel.
     *
     * @throws Exception
     * @return void
     */
    public function __construct()
    {
        if (static::NS_RMQ_QUEUE_NAME === self::NS_RMQ_QUEUE_NAME) {
            Mage::throwException('Please specify a custom name for the queue name');
        }

        $this->_connect();
    }

    /**
     * Closes channel and connection.
     *
     * @return void
     */
    protected function _shutdown()
    {
        if ($this->_rmqChannel) {
            $this->_rmqChannel->close();
        }

        if ($this->_rmqConnection) {
            $this->_rmqConnection->close();
        }
    }

    /**
     * Operation (read/write) based init functionality.
     *
     * @return void
     */
    protected function _init()
    {
        register_shutdown_function(function () {
            $this->_shutdown();
            $this->_handleUnlock();
        });
    }

    /**
     * Checks if blocking processes are running.
     *
     * @return boolean
     */
    protected function _canRun()
    {
        foreach ($this->_blockingProcessesNames as $processName) {
            if ($this->_getLockInstance()->isLockExists($processName, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Lock implementation.
     *
     * @return Peppermint_Importer_Helper_Abstract_Mq
     */
    protected function _handleLock()
    {
        $this->_getLockInstance()
            ->setLock(static::PROCESS_NAME, true, true);

        return $this;
    }

    /**
     * Unlock implementation.
     *
     * @return Peppermint_Importer_Helper_Abstract_Mq
     */
    protected function _handleUnlock()
    {
        $this->_getLockInstance()
            ->releaseLock(static::PROCESS_NAME, true);

        return $this;
    }

    /**
     * Cancels the current digestion from queue and stops the process.
     *
     * @param AMQPMessage $message
     * @return Peppermint_Importer_Helper_Abstract_Mq
     */
    protected function _cancel(AMQPMessage $message)
    {
        $this->_rmqChannel->basic_cancel($message->getDeliveryTag());

        return $this;
    }

    /**
     * Rejects the message and puts it back to queue.
     *
     * @param AMQPMessage $message
     * @return Peppermint_Importer_Helper_Abstract_Mq
     */
    protected function _reject(AMQPMessage $message)
    {
        $this->_rmqChannel->basic_nack($message->getDeliveryTag(), false, true);

        return $this;
    }

    /**
     * Returns Lock object.
     *
     * @return Mage_Index_Model_Lock
     */
    protected function _getLockInstance()
    {
        if (!$this->_lockInstance) {
            $this->_lockInstance = Mage_Index_Model_Lock::getInstance();
        }

        return $this->_lockInstance;
    }

    /**
     * Adds blocking process based on name, usually class::PROCESS_NAME.
     *
     * @param string $name
     *
     * @return Peppermint_Importer_Helper_Abstract_Mq
     */
    protected function _addBlockingProcessName($name)
    {
        $this->_blockingProcessesNames[] = $name;

        return $this;
    }

    /**
     * Connect to MQ.
     *
     * @return Peppermint_Importer_Helper_Abstract_Mq
     */
    protected function _connect()
    {
        // RabbitMq connection
        $this->_rmqConnection = new AMQPStreamConnection(
            Mage::getStoreConfig(self::NS_RMQ_HOST),
            Mage::getStoreConfig(self::NS_RMQ_PORT),
            Mage::getStoreConfig(self::NS_RMQ_USER),
            Mage::getStoreConfig(self::NS_RMQ_PASS),
            Mage::getStoreConfig(self::NS_RMQ_VHOST),
            false,
            Mage::getStoreConfig(self::NS_RMQ_LOGIN_METHOD),
            null,
            Mage::getStoreConfig(self::NS_RMQ_LOCALE),
            Mage::getStoreConfig(self::NS_RMQ_CONN_TIMEOUT),
            Mage::getStoreConfig(self::NS_RMQ_READ_WRITE_TIMEOUT),
            null,
            false,
            Mage::getStoreConfig(self::NS_RMQ_HEARTBEAT)
        );

        // RabbitMq channel
        $this->_rmqChannel = $this->_rmqConnection->channel();

        /*
         * 1. #queue name, the same as the sender
         * 2. #passive
         * 3. #durable
         * 4. #exclusive
         * 5. #autodelete
         */
        $this->_rmqChannel->queue_declare(Mage::getStoreConfig(static::NS_RMQ_QUEUE_NAME), false, true, false, false);

        $this->_init();

        // Adding self to the blocking list
        $this->_addBlockingProcessName($this::PROCESS_NAME);

        return $this;
    }

    /**
     * Disconnect from MQ.
     *
     * @return Peppermint_Importer_Helper_Abstract_Mq
     */
    protected function _disconnect()
    {
        $this->_shutdown();
        $this->_rmqChannel = null;
        $this->_rmqConnection = null;

        return $this;
    }
}
