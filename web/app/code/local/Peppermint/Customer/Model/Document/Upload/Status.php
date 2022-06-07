<?php
/**
 * @category     Peppermint
 * @package      Peppermint\Customer
 * @author       Craig Goodspeed <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Document_Upload_Status extends Mage_Core_Model_Abstract
{
    private $_allStatuses = null;
    public const COMPLETED = 'Completed';
    public const PROCESSING = 'Processing';
    public const NOT_STARTED = 'NotStarted';
    public const ERROR = 'Error';

    /**
     * Magento post __construct init.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_customer/document_upload_status');
    }

    /**
     * @return object
     */
    public function getAllStatuses() {
        if (!$this->_allStatuses) {
            $this->_allStatuses = $this->getCollection();
        }

        return $this->_allStatuses;
    }

    /**
     * @param $status
     * @return mixed|null
     */
    public function getStatusByName($status) {
        if ($status) {
            $toReturn = $this->getAllStatuses();
            foreach ($toReturn as $item) {
                if ($item->getName() === $status) {
                    return $item;
                }
            }
        }

        return null;
    }
}
