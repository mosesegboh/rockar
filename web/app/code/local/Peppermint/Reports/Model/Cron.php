<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Reports_Model_Cron
{
    /**
     *  Transfer records from 'peppermint_vin_product_pricing_log' table to 'peppermint_vin_product_pricing_log_archive'
     *
     * @return void
     * @throws Exception
     */
    public function transferVinProductPricingLogsToArchive()
    {
        $numberOfDays = Mage::helper('peppermint_reports')->getDaysTransferToArchive();
        $date = new DateTime('-' . $numberOfDays . ' day');
        $date->setTime(23, 59, 59);

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $pricingLogsTable = $resource->getTableName('peppermint_reports/vin_product_pricing_log');
        $pricingLogsArchiveTable = $resource->getTableName('peppermint_reports/vin_product_pricing_log_archive');
        $fields = [
            'created_at',
            'product_id',
            'vin_number',
            'action',
            'price',
            'mplan_price',
            'co2_tax',
            'final_price',
            'price_rules',
            'customer_group_id',
            'customer_group',
            'published_to_ds_date',
            'vehicle_condition',
            'cap_code'
        ];

        $select = $readConnection->select()
            ->from($pricingLogsTable, $fields)
            ->where('created_at <= (?)', $date->format(Varien_Date::DATETIME_PHP_FORMAT));

        $writeConnection->query(
            $select->insertFromSelect($pricingLogsArchiveTable, $fields, false)
        );

        $writeConnection->query($select->deleteFromSelect($pricingLogsTable));
    }
}
