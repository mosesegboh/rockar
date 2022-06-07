<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourney
 * @author    Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();

$bmwWebsiteId = Mage::getModel('core/website')->load('bmw')->getId();

Mage::getModel('core/config')->saveConfig(
    'rockar_catalog/general/exterior_images_indexes',
    null,
    'websites',
    $bmwWebsiteId
)->cleanCache();

$this->endSetup();
