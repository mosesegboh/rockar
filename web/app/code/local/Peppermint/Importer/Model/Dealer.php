<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Mircea Chetan <mircea.chetan@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Dealer extends Mage_Core_Model_Abstract
{
    /**
     * @var string[]
     */
    protected $_status = [];

    /**
     * @var string
     */
    protected $_processStarted = '';

    /**
     * @var Rockar_Localstores_Model_Stores
     */
    protected $_modelStore;

    /**
     * @var Rockar_Localstores_Model_Address
     */
    protected $_modelAddress;

    /**
     * @var Peppermint_Localstores_Model_Distances
     */
    protected $_localstoresDistances;

    /**
     * @var Rockar_All_Helper_Data
     */
    protected $_rockarAllHelper;

    /** @var string */
    protected const DEALER_ADDRESS_DEFAULT_COUNTRY_CODE = 'ZA';

    /**
     * Peppermint_Importer constructor.
     */
    public function __construct()
    {
        $this->_modelStore = Mage::getModel('rockar_localstores/stores');
        $this->_modelAddress = Mage::getModel('rockar_localstores/address');
        $this->_localstoresDistances = Mage::getModel('peppermint_localstores/distances');
        $this->_rockarAllHelper = Mage::helper('rockar_all');
    }

    /**
     * Disable a dealer.
     *
     * @param array $data
     *
     * @throws Mage_Core_Exception
     * @return string[]
     */
    public function disableDealer($data)
    {
        $this->_processStarted = 'disable';

        foreach ($data as ['code' => $dealerCode]) {
            $dealer = $this->_modelStore->load($dealerCode, 'code');

            if ($dealer->getEntityId()) {
                try {
                    $dealer->setStatus(0)
                        ->save();
                    $this->_status[$this->_processStarted]['success']['dealer_code'][$dealerCode] = 'disabled';
                } catch (Exception $e) {
                    $this->_status[$this->_processStarted]['error']['dealer_code'][$dealerCode] = $e->getMessage();
                }
            }
        }

        return $this->_status;
    }

    /**
     * Create dealer.
     *
     * @param array $data
     *
     * @return string[]
     */
    public function createDealer($data)
    {
        $this->_processStarted = 'create_store';

        if (!is_array($data)) {
            $this->_status[$this->_processStarted]['error'] = 'No store data';

            return $this->_status;
        }

        foreach ($data as ['code' => $code, 'dealer_data' => $dealerData]) {
            $dealer = $this->_modelStore->load($code, 'code');

            if ($dealer->getCode() === $code) {
                $this->_status[$this->_processStarted]['error']['code'][$code] = 'already exist';
                continue;
            }

            try {
                $insertId = $this->_modelStore->setData($dealerData)
                    ->setName($dealerData['dealer_name'] ?? '')
                    ->setStatus(1)
                    ->save()
                    ->getId();
                $this->_modelAddress->setData($dealerData)
                    ->setStoreId($insertId)
                    ->setCountryCode(self::DEALER_ADDRESS_DEFAULT_COUNTRY_CODE)
                    ->save();
            } catch (Exception $e) {
                $this->_status[$this->_processStarted]['error']['code'][$code] = $e->getMessage();
            }

            unset($dealer); // clean php memory
        }
        unset($data); // clean php memory

        return $this->_status;
    }

    /**
     * Update dealer.
     *
     * @param array $data
     *
     * @return string[]
     */
    public function updateDealer($data)
    {
        $this->_processStarted = 'update_store';

        if (is_array($data)) {
            foreach ($data as ['code' => $code, 'dealer_data' => $dealerData]) {
                $dealers = $this->_modelStore->getCollection()
                    ->addFieldToFilter('code', $code);
                $dealerName = $dealerData['dealer_name'];

                foreach ($dealers as $dealer) {
                    $entityId = $dealer->getEntityId();

                    if ($entityId) {
                        $this->_modelStore->load($entityId)
                            ->addData($dealerData)
                            ->setName($dealerName)
                            ->setCountryCode(self::DEALER_ADDRESS_DEFAULT_COUNTRY_CODE)
                            ->save();
                        $this->_modelAddress->load($entityId, 'store_id')
                            ->addData($dealerData)
                            ->save();
                        $this->_status[$this->_processStarted]['success']['code'][$code] = 'updated';
                    } else {
                        $this->_status[$this->_processStarted]['error']['code'][$code] = 'not found';
                    }
                }
            }
        } else {
            $this->_status[$this->_processStarted]['error']['code'][] = 'No data sent';
        }
        unset($data); // clean php memory

        return $this->_status;
    }

    /**
     * Callback method that will receive queue message and sending to be processed.
     *
     * @param string $message
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function processDealersOptions($message)
    {
        foreach (json_decode($message, true) as $key => $dealer) {
            if (!empty($dealer)) {
                switch ($key) {
                    case 'delete':
                        echo "Running disabled dealers...\n";
                        $this->disableDealer($dealer);
                        echo $this->_rockarAllHelper->jsonEncode($this->_status['disable']) . "\n";
                        break;
                    case 'update':
                        echo "Running update dealers...\n";
                        $status = $this->updateDealer($dealer);
                        $this->_localstoresDistances->calculateAllDistances();
                        echo $this->_rockarAllHelper->jsonEncode($this->_status['update_store']) . "\n";
                        break;
                    case 'add':
                        echo "Running create dealers...\n";
                        $status = $this->createDealer($dealer);
                        $this->_localstoresDistances->calculateAllDistances();
                        echo $this->_rockarAllHelper->jsonEncode($this->_status['create_store']) . "\n";
                        break;
                }
            }
        }
    }
}
