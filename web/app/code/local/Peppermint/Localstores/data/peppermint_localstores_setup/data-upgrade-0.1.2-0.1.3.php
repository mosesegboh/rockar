<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstore
 * @author    Lilian Codreanu <lilian.codreanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();

try {
    $localStoresAddress = Mage::getModel('rockar_localstores/address')->getCollection();

    foreach ($localStoresAddress as $address) {
        $address->setCountryCode('ZA')
            ->save();
    }
} catch (Exception $e) {
    Mage::logException($e);
}
$this->endSetup();
