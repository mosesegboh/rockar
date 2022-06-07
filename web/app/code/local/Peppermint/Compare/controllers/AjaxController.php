<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Compare
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar2_Compare') . DS . 'AjaxController.php';

class Peppermint_Compare_AjaxController extends Rockar2_Compare_AjaxController
{
    /**
     * Get Compare Data
     */
    public function getCompareDataAction()
    {
        $data = Mage::getBlockSingleton('rockar_compare/catalog_product_compare_list')->getCompareDataJson();
        $this->sendJson($data);
    }

    /**
     * Remove all items from comparison list
     *
     * @return void
     */
    public function clearAction()
    {
        $result = [];
        $items = Mage::getResourceModel('catalog/product_compare_item_collection');

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $items->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
        } else {
            $items->setVisitorId(Mage::getSingleton('log/visitor')->getId());
        }

        try {
            $items->clear();
            $result['message'] = $this->__('The comparison list was cleared.');
            Mage::helper('catalog/product_compare')->calculate();
        } catch (Exception $e) {
            $result['errorMessage'] = $e->getMessage();
            $this->setResponseHttpStatusCodeBadRequest();
        }

        $this->sendJson($result);
    }
}
