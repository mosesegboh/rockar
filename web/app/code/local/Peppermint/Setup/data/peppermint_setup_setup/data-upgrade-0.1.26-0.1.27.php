<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Ivans Zuks <info@scandiweb.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();
$connection = $this->getConnection();

$attributeCodes = ['title', 'subtitle', 'short_title', 'short_subtitle'];
$attributeModel = Mage::getModel('eav/entity_attribute');

foreach ($attributeCodes as $attributeCode) {
    $attributeId = $attributeModel->load($attributeCode, 'attribute_code')
        ->getId();
    $connection->update(
        $this->getTable('eav/entity_attribute'),
        ['sort_order' => $attributeModel->getPosition()],
        'attribute_id = ' . $attributeId
    );

    $attributeModel->unsetData();
}

$this->endSetup();
