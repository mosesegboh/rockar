<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstore
 * @author    Jez Horton <jez.horton@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();
$connection = $this->getConnection();
$dealersArray = [
    'VDC' => [
        '30773',
        '21730',
        '02487',
        '23389',
        '02980',
        '26582',
        '48548',
        '28003',
        '33724',
        '45848',
        '09613',
        '09725',
        '21819',
        '45176',
        '33302',
        '32717',
        '10304',
        '46784',
        '45877',
        '44046',
        '08384',
        '23228',
        '02979',
        '10237',
        '28951',
        '31954'
    ],
    'CTOWN' => [
        '28153',
        '44456',
        '09616',
        '09620',
        '02460',
        '33519',
        '45230',
        '21757',
        '47524'
    ],
    'UMLAAS' => [
        '42174',
        '45849',
        '27191',
        '46199',
        '44643',
        '43057',
        '02485',
        '45850',
        '43760',
        '45851'
    ]
];

foreach($dealersArray as $compoundCode => $dealerCode) {
    $connection->update(
        $this->getTable('rockar_localstores/stores'),
        ['associated_compound_dealer' => $compoundCode],
        ['dealer_code IN (?)' => $dealerCode]
    );
}

$this->endSetup();