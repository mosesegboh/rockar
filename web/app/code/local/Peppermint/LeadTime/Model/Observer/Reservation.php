<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Jevgenijs Goreliks
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_LeadTime_Model_Observer_Reservation
{
    /**
     * Removes Vin reservations of the customer if order is cancelled or placed
     *
     * @param Varien_Event_Observer $observer
     */
    public function removeCustomerReservation(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        Mage::helper('peppermint_leadtime')->removeVinReservation($order->getCustomerId());
    }

     /**
     * Remove old vin reservations
     *
     * @return void
     */
    public function cleanupVinReservation()
    {
        $collection = Mage::getModel('peppermint_leadtime/reservation')->getCollection()
            ->addFieldToFilter('created_at',
                [
                    'lt' => date('Y-m-d H:i:s', strtotime('-2 minute'))
                ]
            );

        foreach ($collection->getItems() as $item) {
            $item->delete();
        }
    }
}
