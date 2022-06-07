<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_SalesData extends Mage_Core_Model_Abstract
{
    /**
     * Callback method that will receive queue message and sending to be processed.
     *
     * @param string $message
     * @return $this
     * @throws Exception
     */
    public function processSalesData(string $message)
    {
        $messageData = json_decode($message, true);
        $dataToInsert = [];

        foreach ($messageData as $data) {
            if (!empty($data['vin'])) {
                $dataToInsert[] = [
                    'vin' => $data['vin'],
                    'rfs_date' => $data['rfsDate'],
                    'can_amend' => $data['can_amend']
                ];
            }
        }

        $resourceModel = Mage::getSingleton('core/resource');
        $write = $resourceModel->getConnection('core_write');
        $write->insertOnDuplicate($resourceModel->getTableName('peppermint_sales/additional_data'), $dataToInsert);

        return $this;
    }
}
