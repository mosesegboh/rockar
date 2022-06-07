<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->getConnection()
    ->addIndex(
        $installer->getTable('rockar_financingoptions/variables'),
        $installer->getIdxName(
            'rockar_financingoptions/variables',
            ['variable'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        ['variable'],
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
$installer->endSetup();
