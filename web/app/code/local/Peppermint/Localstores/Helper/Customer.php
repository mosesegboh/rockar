<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstores
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Localstores_Helper_Customer extends Rockar_Localstores_Helper_Customer
{
    /**
     * {@inheritDoc}
     *
     * @param Sales_Order_Grid_Collection $collection
     *
     * @return void
     */
    public function filterOrdersByLocalStore(&$collection)
    {
        $localStores = Mage::helper('rockar_localstores')->getUserLocalStores(true);

        if ($localStores) {
            $collection->addFieldToFilter('dealer_code', ['in' => $localStores]);
        }
    }
}
