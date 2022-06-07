<?php
/**
 * @category  Peppermint
 * @package   Peppermint_MySavedCars
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Peppermint_FinancingOptions') . DS . 'AjaxController.php';

class Peppermint_MySavedCars_Financing_AjaxController extends Peppermint_FinancingOptions_AjaxController
{
    /**
     * Return Options and Finance Data
     * Rewrite core controller to extends Peppermint_FinancingOptions_AjaxController
     * To prepare finance prarams with new PEP finance variables, balloon percentage
     *
     * @return array
     */
    protected function _getOptions()
    {
        $request = $this->getRequest();
        $wishlistId = (int) $request->getParam('productId', 0);

        if ($wishlistId) {
            /**
             * @var $wishlistItem Mage_Wishlist_Model_Item
             */
            $wishlistItem = Mage::getModel('wishlist/item')->loadWithOptions($wishlistId);
            $productId = Mage::helper('rockar_mysavedcars')->getSavedProduct($wishlistItem)
                ->getId();

            $request->setParam('productId', $productId);
            Mage::register('current_wishlist_item', $wishlistItem);
        }

        return parent::_getOptions();
    }
}
