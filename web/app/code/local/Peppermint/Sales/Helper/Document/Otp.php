<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Sales_Helper_Document_Otp extends Mage_Core_Helper_Abstract implements Peppermint_Sales_Helper_Document_Interface
{
    const HELPER_IDENTIFIER = 'peppermint_sales/document_otp';

    /**
     * Get the OTP document
     * @param string $incrementId
     * @return string|false
     */
    public function getDocument(string $identifier)
    {
        try {
            // identifier is incrementId for OTP documents
            $path = Mage::helper('peppermint_checkout/pdf')->getOtpFilePath($identifier);

            return file_exists($path)
                ? file_get_contents($path)
                : false;
        } catch (Exception $e) {
            Mage::logException($e);

            return false;
        }
    }

    /**
     * Get this helper's xml path identifier
     */
    public function getHelperIdentifier()
    {
        return self::HELPER_IDENTIFIER;
    }

    /**
     * Create/Update Document Record for an OTP document
     * with this helper
     * @param int $orderId
     * @param string $incrementId
     * @return Peppermint_Sales_Model_Order_Document
     */
    public function createRecord($orderId, $identifier)
    {
        $helperId = $this->getHelperIdentifier();

        $record = Mage::getModel('peppermint_sales/order_document')->getCollection()
            ->addFieldToFilter('file_param', $identifier)
            ->addFieldToFilter('file_helper', $helperId)
            ->addFieldToFilter('order_id', $orderId)
            ->getFirstItem();

        return $record
            ->addData([
                'name'        => $identifier . '.pdf',
                'order_id'    => $orderId,
                'file_param'  => $identifier,
                'file_helper' => $helperId
            ])
            ->save();
    }
}
