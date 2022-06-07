<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstores
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Localstores_Model_Observer_Adminhtml_Grid extends Rockar_Localstores_Model_Observer_Adminhtml_Grid
{
    /**
     * {@inheritDoc}
     * Remove local_store_code from the filter
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function addDealerFilterToOrdersGrid(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('rockar_localstores');
        $localStores = $helper->getUserLocalStores(true);

        if ($helper->getLocalStoreUserStatus() && $localStores) {
            $observer->getCollection()
                ->addFieldToFilter('dealer_code', ['in' => $localStores]);
        }

        return $this;
    }
}
