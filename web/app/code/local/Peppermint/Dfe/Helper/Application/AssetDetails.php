<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_AssetDetails extends Mage_Core_Helper_Abstract
{
    /**
     * @var array DFE vehicle conditions mapping
     */
    protected $_vehicleConMap = [
        'N' => 'PCNEW',
        'New' => 'PCNEW',
        'U' => 'PCUSD',
        'Used' => 'PCUSD',
        'D' => 'PCUSD',
        'Demo' => 'PCUSD'
    ];

    /**
     * Set data for asset details.
     *
     * @param  Peppermint_Sales_Model_Order $order
     * @return object
     */
    public function setAssetDetailsData($order)
    {
        $data = $this->_prepareData($order);

        return (new Peppermint_Dfe_Soap_Application_AssetDetails())->setAssetId($data['cap_code'] ?? '')
            ->setChassisNo($data['vin_number'] ?? '')
            ->setEngineNumber($data['bmw_engine'] ?? '')
            ->setCurrentMileage($data['km'] ?? '')
            ->setAssetStatus($data['vehicle_condition'] ?? '');
    }

    /**
     * Prepare collection data by order.
     *
     * @param  Peppermint_Sales_Model_Order $order
     * @return array|void
     */
    protected function _prepareData($order)
    {
        $productId = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToSelect('product_id')
            ->addFieldToFilter('order_id', $order->getId())
            ->getLastItem()
            ->getProductId();
        $product = Mage::getModel('catalog/product')->load($productId);

        if ($product) {
            return [
                'cap_code' => $product->getCapCode(),
                'vin_number' => $product->getData('vin_number'),
                'bmw_engine' => $product->getAttributeText('bmw_engine'),
                'km' => $product->getKm(),
                'vehicle_condition' => $this->_vehicleConMap[$product->getAttributeText('vehicle_condition')] ?? ''
            ];
        }
    }
}
