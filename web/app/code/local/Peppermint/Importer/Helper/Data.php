<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * XML path for sap cron retry limit
     */
    const XML_PATH_SAP_CRON_RESEND_RETRY_LIMIT = 'peppermint_import/sap_log/cron_retry_limit';

    /**
     * XML path for product queue retry retry limit
     */
    const XML_PATH_PRODUCT_FAIL_CRON_RETRY_LIMIT = 'peppermint_import/product_fail/cron_retry_limit';

    /**
     * Setup default stock_data array.
     *
     * @return []
     */
    public function productDefaultStockData()
    {
        return [
            'manage_stock' => '0',
            'original_inventory_qty' => '0',
            'qty' => '0',
            'min_qty' => '0',
            'min_sale_qty' => '0',
            'max_sale_qty' => '0',
            'is_qty_decimal' => '0',
            'is_decimal_divided' => 0,
            'backorders' => '0',
            'notify_stock_qty' => '0',
            'enable_qty_increments' => '0',
            'qty_increments' => '0',
            'is_in_stock' => '1',
            'use_config_manage_stock' => 0
        ];
    }

    /**
     * Get Max retry limit for sap retry cron
     *
     * @return int
     */
    public function getSapRetryCronLimitConfig()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_SAP_CRON_RESEND_RETRY_LIMIT);
    }

    /**
     * Get Max retry limit for product queue fail retry cron
     *
     * @return int
     */
    public function getProductFailCronRetryLimitConfig()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_PRODUCT_FAIL_CRON_RETRY_LIMIT);
    }

    /**
     * Mark old images for deletion and merging with new data
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  array $data
     * @return array
     */
    public function getUpdatedMediaGalleryImagesArray($product, $data)
    {
        Mage::helper('rockar_catalog/images')->removeMediaGalleryImages($product);

        return array_merge(
            $product->getData('media_gallery')['images'] ?? [],
            $data['media_gallery']['images']
        );
    }
}
