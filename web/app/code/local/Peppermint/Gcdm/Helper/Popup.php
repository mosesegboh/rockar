<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Gcdm
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Gcdm_Helper_Popup extends Mage_Core_Helper_Abstract
{
    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_EXTERNAL_AUTHENTICATED = 'EXTERNAL_AUTHENTICATED';
    const CUSTOMER_ACTIVETED_SESS_KEY = 'customer_gcdm_activation_status';

    /**
     * Show activation popup if GCDM customer status is not active
     *
     * @return boolean
     */
    public function isCustomerActivated()
    {
        $customerSess = Mage::getSingleton('customer/session');

        if (!$customerSess->getData(self::CUSTOMER_ACTIVETED_SESS_KEY)) {
            $authData = Mage::getModel('peppermint_gcdm/customer_access')->load($customerSess->getCustomer()->getId());

            try {
                $gcdmCustomerInfoResp = Mage::helper('peppermint_gcdm/externalCommunication')
                    ->setAccessToken($authData->getAccessToken())
                    ->setRefreshToken($authData->getRefreshToken())
                    ->getGcdmCustomerInfo();

                $gcdmCustomerStatus = $gcdmCustomerInfoResp[0]->userAccount->status ?? null;

                $this->setCustomerActivationStatus(
                    in_array(
                        $gcdmCustomerStatus,
                        [
                            self::STATUS_ACTIVATED,
                            self::STATUS_EXTERNAL_AUTHENTICATED
                        ],
                        true
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);

                return false;
            }
        }

        return $customerSess->getData(self::CUSTOMER_ACTIVETED_SESS_KEY);
    }

    /**
     * Set activation flag to customer session
     *
     * @param bool $isActivated
     * @return void
     */
    public function setCustomerActivationStatus(bool $isActivated)
    {
        Mage::getSingleton('customer/session')->setData(self::CUSTOMER_ACTIVETED_SESS_KEY, $isActivated);
    }

    /**
     * Clear customer GCDM activation status session data
     *
     * @return void
     */
    public function clearCustomerSessionActivationStatus()
    {
        Mage::getSingleton('customer/session')->unsetData(self::CUSTOMER_ACTIVETED_SESS_KEY);
    }
}
