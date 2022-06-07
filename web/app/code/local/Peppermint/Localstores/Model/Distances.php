<?php
/**
 * @category  Rockar
 * @package   Rockar_Localstores
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Localstores_Model_Distances extends Mage_Core_Model_Abstract
{
    /**
     * Constructor. Set basic parameters.
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_localstores/distances');
    }

    /**
     * Calculate all distances for all possible combinations of dealerships.
     *
     * @return Peppermint_Localstores_Model_Distances
     */
    public function calculateAllDistances()
    {
        /** @var Rockar_Localstores_Model_Address[] $dealersAddressess */
        $dealersAddressess = Mage::getModel('rockar_localstores/address')->getCollection()
            ->addFieldToSelect(['latitude', 'longitude', 'store_id'])
            ->addFieldToFilter('latitude', ['notnull' => true])
            ->addFieldToFilter('longitude', ['notnull' => true])
            ->getItems();

        $dealersDistances = Mage::helper('peppermint_localstores')->calculateDistancesBetweenDealers($dealersAddressess);

        $this->getResource()
            ->sync($dealersDistances);

        return $this;
    }
}
