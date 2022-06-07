<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_Applicant extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for applicant.
     *
     * @param  integer $orderId
     * @return object
     */
    public function setApplicantData($orderId)
    {
        $customerId = Mage::getModel('sales/order')->load($orderId)
            ->getCustomerId();

        return (new Peppermint_Dfe_Soap_Application_Applicant())->setApplicantReferenceId($customerId)
            ->setApplicantType('ATCST');
    }
}
