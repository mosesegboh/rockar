<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Helper_Mq_AccessoriesMapping extends Peppermint_Importer_Helper_Abstract_Mq_Read
{
    const NS_RMQ_QUEUE_NAME = 'peppermint_import/general/rmq_accessories_mapping_queue';
    const PROCESS_NAME = 'digestAccessoriesMapping';

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
            Mage::getModel('peppermint_importer/accessoriesMapping')->processAccessoriesMapping($message->getBody());
        } catch (Mage_Core_Exception $e) {
            $e->setMessage('Process accessories mapping has encountered an exception', true);
            Mage::logException($e);
            throw $e;
        }

        return $this;
    }
}
