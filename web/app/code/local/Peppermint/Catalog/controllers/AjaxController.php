<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar2_Catalog') . DS . 'AjaxController.php';

class Peppermint_Catalog_AjaxController extends Rockar2_Catalog_AjaxController
{
    /**
     * Rewrite of a parent function to remove check on choose_car action
     *
     * {@inheritDoc}
     */
    public function chooseVehicleAction()
    {
        try {
            $action = $this->getRequest()->getParam('action', '');
            $vehicle = Mage::getModel('catalog/product')->load((int) $this->getRequest()->getParam('id'));
            $productId = $this->getRequest()->getParam('configurable_id', null);
            $accessoriesParams = $this->getRequest()->getParam('accessories', []);
            $accessoriesCollection = Mage::getModel('rockar_accessories/accessories')->getCollection()
                ->addFieldToFilter('main_table.id', ['in' => $accessoriesParams]);

            if ($productId) {
                $parentProduct = Mage::getModel('catalog/product')->load($productId);
                Mage::register('product', $parentProduct);

                $accessoriesCollection->addProductFilterBySku($parentProduct->getSku());
            }

            $accessories = [];

            foreach ($accessoriesCollection as $accessory) {
                $accessories[$accessory->getId()] = $accessory->toArray();
                $accessories[$accessory->getId()]['original_name'] = $accessory->getFinalName('name');
            }

            $result = Mage::helper('rockar_catalog/images')->getProductMedia($vehicle);
            $result['success'] = true;

            if ($productId) {
                $accessories = $this->_getWishlistAccessories($productId, $accessories, $result);
                Mage::helper('rockar_accessories')->saveSelectedAccessoriesPerProduct($productId, $accessories);
            }

            /**
             * Do not remove accessories if finance_update action
             */
            if ($action === 'update_finance') {
                $result['no_accessories_reset'] = true;
            }

            $eventResult = new Varien_Object();
            Mage::dispatchEvent('catalog_controller_product_update_after', [
                'vehicle' => $vehicle,
                'accessories' => $accessories,
                'event_return' => $eventResult,
            ]);

            $result = array_merge($result, $eventResult->getData());
        } catch (Mage_Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Mage::logException($e);
            $result = [
                'success' => false,
                'message' => $this->__('There was an error while updating product'),
            ];
        }

        $this->sendJson($result);
    }
}