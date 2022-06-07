<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Catalog_Product_Update_Configurable extends Rockar_Catalog_Model_Catalog_Product_Update_Configurable
{
    /**
     * Re-write of core function to save the configurable product and not run re-index when import is running
     *
     * {@inheritdoc}
     */
    public function updateVisibilityAndStatus()
    {
        $product = $this->getConfigurableProduct();
        $vehicles = Mage::helper('rockar_catalog/vehicle')->getVehicles($product);
        $statuses = [];

        foreach ($vehicles as $vehicle) {
            $visibleIn = $vehicle->getVisibleIn();

            if ($vehicle->isSalable()) {
                $statuses[$visibleIn][] = $visibleIn;
            }
        }

        if ($finalVisibleIn = $this->_getProductVisibleIn($statuses)) {
            $status = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
            $product->setData('visible_in', $finalVisibleIn);
        } else {
            $status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
            $product->setData('visible_in', Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::NOT_VISIBLE);
        }

        $product->setData('status', $status);

        if (
            !Mage::registry('rockar_full_import')
            && !Mage::registry('product_import')
            && !Mage::registry('peppermint_import')
            ) {
                Mage::getModel('catalog/product_status')->updateProductStatus(
                    $product->getId(),
                    Mage_Core_Model_App::ADMIN_STORE_ID,
                    $product->getStatus()
                );
        } else {
            $product->save();
        }

        return $this;
    }
}
