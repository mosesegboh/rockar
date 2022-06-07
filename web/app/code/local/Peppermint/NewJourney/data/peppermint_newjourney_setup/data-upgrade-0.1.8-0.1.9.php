<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ProductPods
 * @author    Nikoloz Gabunia <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$bmwStoreId = Mage::getModel('core/store')->load('bmw_store_view', 'code')->getId();
$exteriorAttribute = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'exterior');

$storeLabels = $exteriorAttribute->getStoreLabels();
$storeLabels[$bmwStoreId] = 'Colour';

$exteriorAttribute->setStoreLabels($storeLabels);
$exteriorAttribute->save();
