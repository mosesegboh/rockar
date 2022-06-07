<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

/**
 * Update Finance Variable Data
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$variablesData = [
    'balloon_percentage' => [
        'value_suffix' => ' %'
    ]
];

foreach ($variablesData as $variableCode => $data) {
    $variable = Mage::getModel('rockar_financingoptions/variables')->load($variableCode, 'variable');

    if ($variable && $variable->getId()) {
        foreach ($data as $key => $value) {
            $variable->setData($key, $value);
        }

        $variable->save();
    }
}
