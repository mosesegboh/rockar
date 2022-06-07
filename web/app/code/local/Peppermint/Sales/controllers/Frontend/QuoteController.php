<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Sales_Frontend_QuoteController extends Rockar_All_Controller_Front_Ajax
{
    /**
     * @var boolean $_checkAjax
     */
    protected $_checkAjax = false;

    /**
     * @var null|Peppermint_sales_Helper_QuoteMail $_helper
     */
    protected $_helper = null;

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_helper = Mage::helper('peppermint_sales/quoteMail');
    }

    /**
     * Pre dispatch
     * @return $this
     */
    public function preDispatch()
    {
        parent::preDispatch();

        // Customer must be logged in
        if (!$this->_getCustomerSession()->isLoggedIn()) {
            $this->_restrictDispatch();
        }

        return $this;
    }

    /**
     * Get Quote Action
     * @return $this
     */
    public function getQuoteAction()
    {
        try {
            if (!$this->_helper->getConfigEnabled()) {
                $this->_restrictDispatch();

                return $this;
            }

            $request = $this->getRequest();

            $customer = $this->_getCustomerSession()
                ->getCustomer();

            // configurable
            $productId = $request->getParam('product_id');
            // Need to set store and customer group to get the product price with catalog price rule
            $product = $productId
                ? Mage::getModel('catalog/product')->load($productId)
                    ->setStoreId($customer->getStoreId())
                    ->setCustomerGroupId($customer->getGroupId())
                : null;

            if (!$product || !$product->getId()) {
                throw new Exception("Cannot find product (id=${productId})");
            }

            $vehicleId = $request->getParam('vehicle_id');

            // use first simple product for this configurable if none specified
            $vehicle = $vehicleId ? Mage::getModel('catalog/product')->load($vehicleId)
                : Mage::helper('rockar_catalog/vehicle')->getFirstVehicle($product);

            if (!$vehicle || !$vehicle->getId()) {
                throw new Exception("Cannot find vehicle for product (id=${productId})");
            }

            $quoteName = $request->getParam('quote_name');
            $productId = $product->getId();
            $vehicleId = $vehicle->getId();

            $quote = new Varien_Object([
                'name' => $quoteName
            ]);

            if ($itemId = Mage::helper('rockar_mysavedcars')->isInWishlist($productId)) {
                Mage::getModel('wishlist/item')->load($itemId)->delete();
            }

            $catalogHelper = Mage::helper('rockar_catalog/vehicle');
            $options = [];
            $optionsData = $catalogHelper->getOptionsData($product)['options'];

            foreach ($optionsData as $optionsGroup) {
                foreach ($optionsGroup as $index => $option) {
                    $options[$option['id']][$index] = $option['value'];
                }
            }

            $superAttributes = $catalogHelper->getConfigurableAttributes($product, $vehicle);

            $wishlist = $this->_helper->addProductToWishlist($customer, $product, new Varien_Object([
                'product'         => $productId,
                'vehicleId'       => $vehicleId,
                'name'            => $quoteName,
                'options'         => $options,
                'super_attribute' => $superAttributes
            ]));

            $this->_helper->sendQuote($customer, $product, $quote, $wishlist);
            $this->setResponseHttpStatusCodeOk();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->setResponseHttpStatusCodeBadRequest();
        }

        return $this;
    }
}
