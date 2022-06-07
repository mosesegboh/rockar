<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales/Order/ShipmentController.php';
class Peppermint_Sales_Adminhtml_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    /**
     * Save shipment and order in one transaction.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Mage_Adminhtml_Sales_Order_ShipmentController
     */
    protected function _saveShipment($shipment)
    {
        parent::_saveShipment($shipment);
        Mage::dispatchEvent('peppermint_sales_order_ship', ['order' => $shipment->getOrder()]);

        return $this;
    }
}
