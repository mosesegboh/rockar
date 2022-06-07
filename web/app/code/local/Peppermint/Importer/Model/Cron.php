<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Cron
{
    /**
     * Retry saving/update fail product
     *
     * @return void
     */
    public function productFailRun()
    {
        // Get all fail product msg where retry limit has not been exceeded
        $failProducts = Mage::getModel('peppermint_importer/product_fail')->getCollection()
            ->addFieldToSelect('product_data')
            ->addFieldToFilter(
                'retry_count',
                ['lteq' => Mage::helper('peppermint_importer')->getProductFailCronRetryLimitConfig()]
            );

        $data = [];
        $allHelper = Mage::helper('rockar_all');

        // Building array of products to be re-save, ['add'=> [...decode product data]]
        foreach ($failProducts as $failProduct) {
            $productData = $failProduct->getProductData();

            if ($productData) {
               $data['add'][] = $allHelper->jsonDecode($productData);
            }
        }

        if (!empty($data['add'])) {
            Mage::getModel('peppermint_importer/product')->processProducts($data);
        }
    }
}
