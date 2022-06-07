<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Helper_Data extends Rockar_PartExchange_Helper_Data
{
    /**
     * Prepare Px data
     *
     * @var array
     */
    protected $_preparePxData;

    /**
     *  Px Object
     *
     * @var Varien_Object
     */
    protected $_pxObject;

    /**
     * Session key
     */
    const SESSION_PX_STATE_KEY = 'px_removed';

    /**
     * Maps data from session object to savable array
     *
     * @param Varien_Object $partExchange
     *
     * @return array
     */
    protected function _preparePartExchange(Varien_Object $partExchange)
    {
        if (!$this->_preparePxData || $partExchange !== $this->_pxObject) {
            $this->_pxObject = $partExchange;
            $additionalInfo = [];
            $helper = Mage::helper('rockar_all');
            $data = $partExchange->getData();
            $getPartExchangeAdditionalInfo = $data['additional_info'] ?? '';

            if (is_array($getPartExchangeAdditionalInfo)) {
                foreach ($getPartExchangeAdditionalInfo as $checkbox) {
                    if (filter_var($checkbox['checked'], FILTER_VALIDATE_BOOLEAN)) {
                        $additionalInfo[] = (int) $checkbox['id'];
                    }
                }
            }

            $carAttributes = $data['cap_extended'] ?? [];
            $carAttributes['derivative'] = $data['cap']['derivative'] ?? '';

            $this->_preparePxData = [
                'license_plate' => !empty($data['vrm']) ? $data['vrm'] : null,
                'cap_id' => $data['cap']['capid'] ?? '',
                'car_model' => $data['cap_extended']['product_name'] ?? '',
                'car_mileage' => $data['mileage'] ?? '',
                'car_year' => $data['plate_year'] ?? '',
                'car_condition' => $data['car_condition'] ?? '',
                'checkboxes' => $helper->jsonEncode($additionalInfo),
                'customer_id' => Mage::getSingleton('customer/session')->getCustomerId(),
                'car_attributes' => $helper->jsonEncode($carAttributes),
                'outstanding_finance' => $data['outstanding_finance'] ?? '',
                'part_exchange_value' => $data['part_exchange_value'] ?? '',
                'first_date_of_registration' => $data['cap']['first_date_of_registration'] ?? '',
                'make_name' => $data['cap']['make_name'] ?? '',
                'make_code' => $data['cap']['make_code'] ?? '',
                'original_valuation' => $data['cap']['original_valuation'] ?? '',
                'vin' => $data['cap']['vin'] ?? '',
                'ip' => $_SERVER['REMOTE_ADDR'],
                'browser' => $this->_getBrowserInfo(),
                'location' => $this->_getLocation(),
                'is_active' => $data['is_active'] ?? 0,
                'is_expired' => 0,
                'px_id' => $data['px_id'] ?? '',
                'status' => (int) ($data['status'] ?? '')
            ];

            if ($partExchange->getVrm()) {
               $this->_preparePxData['serialized_object'] = serialize($partExchange);
            }
        }

        return $this->_preparePxData;
    }

    /**
     * Check if customer is logged in
     *
     * @return mixed
     */
    public function getCustomerIsLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Returns a Temporary Part Exchange VRM and Mileage from Session
     *
     * @return string
     */
    public function getTemporaryPartExchangeJson()
    {
        $tempPx = Mage::getSingleton('customer/session')->getPartExchange();

        if (!empty($tempPx) && isset(
                $tempPx['vrm'],
                $tempPx['mileage'],
                $tempPx['cap']['capid'],
                $tempPx['cap']['model'],
                $tempPx['cap_extended']['product_name'],
                $tempPx['plate_year']
            )) {
            $result = [
                'vrm' => $tempPx['vrm'],
                'mileage' => $tempPx['mileage'],
                'capId' => $tempPx['cap']['capid'],
                'model' => $tempPx['cap']['model'],
                'title' => $tempPx['cap_extended']['product_name'],
                'derivative' => $tempPx['cap']['capid'], // It seems that capid and derivative are the same
                'registrationYear' => $tempPx['plate_year']
            ];

            if (isset($tempPx['car_condition'])) {
                $result['car_condition'] = $tempPx['car_condition'];
            }
        } else {
            $result = null;
        }

        return htmlspecialchars(Mage::helper('rockar_all')->jsonEncode($result));
    }

    /**
     * Returns if Part Exchange expired
     *
     * @return string
     */
    public function getIsExpiredPartExchange()
    {
        $isExpired = 0;

        if ($this->getCustomerIsLoggedIn()) {
            $partExchange = Mage::helper('rockar_partexchange')->loadPartExchangeFromSession(
                Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY
            );

            $isExpired = $partExchange->getIsExpired();
        }

        return $isExpired;
    }

    /**
     * Returns array with customer's part exchanges
     *
     * @return string
     */
    public function getAllPartExchanges()
    {
        $partExchanges = [];
        $customerSession = Mage::getSingleton('customer/session');

        if ($customerSession->isLoggedIn()) {
            $collection = Mage::helper('rockar_partexchange')->getCustomerPartExchanges();

            foreach ($collection as $partExchange) {
                $partExchanges[] = ['id' => $partExchange->getId(), 'title' => $partExchange->getLicensePlate()];
            }
        }

        return htmlspecialchars(Mage::helper('core')->jsonEncode($partExchanges, ENT_QUOTES));
    }

    /**
     * Returns part exchange id from session
     *
     * @return String|number
     */
    public function getPartExchangeIdFromSession()
    {
        $partExchangeId = 0;
        $partExchange = Mage::helper('rockar_partexchange')->loadPartExchangeFromSession(
            Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY
        );

        if ($partExchange) {
            $partExchangeId = $partExchange->getPxId();
        }

        return $partExchangeId;
    }

    /**
     * Get trade-in state from session
     *
     * @return bool
     */
    public function getPxCurrentState()
    {
        return Mage::getSingleton('customer/session')->getData(
            self::SESSION_PX_STATE_KEY
        );
    }

    /**
     * Returns state key in session
     *
     * @return string
     */
    public function getPxStateSessionKey()
    {
        return self::SESSION_PX_STATE_KEY;
    }

    /**
     * Returns part exchange expiration date
     *
     * @return null|string
     */
    public function getExpireDate()
    {
        $partExchangeHelper = Mage::helper('rockar_partexchange');
        $partExchanges = $partExchangeHelper->getCustomerPartExchanges();
        $expirationDate = null;

        foreach ($partExchanges as $partExchange) {
            $expirationDate = $partExchangeHelper->getExpirationDate($partExchange);
        }

        if (!$expirationDate) {
            $tempPx = Mage::getSingleton('customer/session')->getPartExchange();
            $expirationDate = $tempPx['timestamp'] ? $this->getExpirationDateFromSession($tempPx['timestamp']) : null;
        }

        return $expirationDate;
    }

    /**
     * Returns part exchange expiration date from session timestamp
     *
     * @param $timestamp
     *
     * @return null|string
     */
    public function getExpirationDateFromSession($timestamp)
    {
        $expiryTime =
            sprintf(
                ' + %s day',
                Mage::getStoreConfig(
                    Rockar_PartExchange_Helper_PartExchangeExpiry::PART_EXCHANGE_EXPIRY_TIME_PATH
                )
            );

        $expireDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', $timestamp) . $expiryTime));
        $currentDate = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        if ($expireDate > $currentDate) {
            return Mage::helper('core')->formatDate($expireDate, 'short', false);
        }

        return null;
    }
}
