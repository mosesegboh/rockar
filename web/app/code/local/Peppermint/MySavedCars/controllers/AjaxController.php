<?php
/**
 * @category  Peppermint
 * @package   Peppermint_MySavedCars
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar2_MySavedCars'). DS .'AjaxController.php';

class Peppermint_MySavedCars_AjaxController extends Rockar2_MySavedCars_AjaxController
{
    /**
     * Rewrite of parent function to add form key validation
     *
     * {@inheritDoc}
     */
    public function removeAction()
    {
        if (!$this->_validateFormKey()) {
            $result = [
                'error' => true,
                'message' => $this->__('There was an error with form submit.')
            ];

            $this->sendJson($result);

            return;
        }

        parent::removeAction();
    }

    /**
     * Remove from wishlist by product id
     */
    public function removeByProductIdAction()
    {
        $result = $this->_isFormKeyValid()
            ? $this->_processRemoveByProductId()
            : ['errorMessage' => $this->__('Invalid form key')];

        if (isset($result['errorMessage'])) {
            $this->setResponseHttpStatusCodeBadRequest();
        }

        $this->sendJson($result);
    }

    /**
     * Process remove from wishlist by product Id
     *
     * @return array
     */
    protected function _processRemoveByProductId()
    {
        $result = [];
        $productId = (int) $this->getRequest()->getParam('product_id');
        $wishlist = Mage::helper('rockar_mysavedcars')->getWishlist();

        if (!$wishlist) {
            return [
                'errorMessage' => $this->__('Something went wrong, please try again')
            ];
        }

        if (!$productId) {
            return [
                'errorMessage' => $this->__('Invalid product id, please try again')
            ];
        }

        $wishlistItems = Mage::getModel('wishlist/item')->getCollection()
            ->addFieldToFilter('wishlist_id', $wishlist->getId())
            ->addFieldToFilter('product_id', $productId)
            ->setPageSize(1)
            ->setCurPage(1);

        $item = $wishlistItems->getFirstItem();

        if (!$item || !$item->getId()) {
            $result['errorMessage'] = $this->__('There is no product in wishlist with such id.');

            return $result;
        }

        try {
            $item->delete();
            $wishlist->save();
            Mage::dispatchEvent(
                'rockar_wishlist_remove_item',
                [
                    'wishlist' => $wishlist,
                    'item' => $item
                ]
            );
        } catch (Mage_Core_Exception $e) {
            $result['errorMessage'] = $this->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage());
            Mage::logException($e);
        } catch (Exception $e) {
            $result['errorMessage'] = $this->__('An error occurred while deleting the item from wishlist.');
            Mage::logException($e);
        }

        Mage::helper('wishlist')->calculate();

        return $result;
    }

    /**
     * Rewrite of parent function to remove checking for duplicate description
     *
     * {@inheritDoc}
     */
    protected function _processAddToWishlist()
    {
        $request = $this->getRequest();
        $params = $request->getParams();
        $session = Mage::getSingleton('customer/session');
        $customer = $session->getCustomer();
        $helper = Mage::helper('rockar_mysavedcars');
        $wishlistHelper = Mage::helper('wishlist');
        $wishlist = $helper->getWishlist();
        $productId = isset($params['product']) ? $params['product'] : false;

        if (!$wishlist) {
            return [
                'errorMessage' => $wishlistHelper->__('Something went wrong, please try again')
            ];
        }

        if (!$productId) {
            return [
                'errorMessage' => $wishlistHelper->__('Invalid product id, please try again')
            ];
        }

        // Need to set store and customer group to get the product price with catalog price rule
        $product = Mage::getModel('catalog/product')->load($productId)
            ->setStoreId($customer->getStoreId())
            ->setCustomerGroupId($customer->getGroupId());

        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            return [
                'errorMessage' => $wishlistHelper->__('Cannot specify product.')
            ];
        }

        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            $request->setParam('vehicleId', $productId);
        }

        if ($errorMessage = $helper->validateCarName($params)) {
            return [
                'errorMessage' => $errorMessage
            ];
        }

        try {
            $requestParams = $helper->prepareRequestParams($request, $product);

            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }

            $buyRequest = new Varien_Object($requestParams);

            if ($itemId = $helper->isInWishlist($buyRequest['vehicleId'], true)) {
                Mage::getModel('wishlist/item')->load($itemId)->delete();
            }

            $item = $wishlist->addNewItem($product, $buyRequest);

            if (is_string($item)) {
                Mage::throwException($item);
            }

            Mage::dispatchEvent(
                'wishlist_add_product',
                [
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $item
                ]
            );

            $item->setDescription($params['name'])
                ->setProductPrice($product->getFinalPrice())
                ->save();

            $wishlistHelper->calculate();

            $message = $wishlistHelper->__(
                'Vehicle has been added to <a href="%s">My Account</a>',
                Mage::getUrl('customer/account') . '#my-saved-cars'
            );

            $customerId = $session->getCustomerId();
            $wishlistItems = Mage::getSingleton('wishlist/wishlist')->loadByCustomer($customerId)
                ->getItemCollection();

            $wishlistNames = [];

            if ($wishlistItems->getData()) {
                foreach ($wishlistItems as $wishlistItem) {
                    /* @var $product Mage_Catalog_Model_Product */
                    $wishlistNames[] = $wishlistItem->getProduct()->getName();
                }
            }

            $result = [
                'message' => $message,
                'wishlist_link' => Mage::getUrl('customer/account') . '#MySavedCars',
                'id' => $item->getId(),
                'added_vehicles' => $wishlistNames
            ];
        } catch (Mage_Core_Exception $e) {
            $result = [
                'errorMessage' => $wishlistHelper->__('An error occurred while adding item to wishlist: %s',
                $e->getMessage())
            ];

            Mage::logException($e);
        } catch (Exception $e) {
            $result = [
                'errorMessage' => $wishlistHelper->__('An error occurred while adding item to wishlist.')
            ];

            Mage::logException($e);
        }

        return $result;
    }
}
