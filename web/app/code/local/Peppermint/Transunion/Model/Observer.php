<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Transunion
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Transunion_Model_Observer
{
    /**
     * Hide "Rockar Cap Network" tab
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function hideCapNetworkTab(Varien_Event_Observer $observer)
    {
        if (Mage::helper('peppermint_transunion')->isConfiguredAsActive()) {
            $sections = $observer->getEvent()
                ->getConfig()
                ->getNode('sections');

            if ($sections->rockar_cap_network) {
                unset($sections->rockar_cap_network);
            }
        }

        return $this;
    }

    /**
     * Adds TransUnion as Vehicle details provided
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function registerTransApiDetailsProvider(Varien_Event_Observer $observer)
    {
        Mage::helper('peppermint_transunion')->registerTransApiDetailsProvider($observer);

        return $this;
    }

    /**
     * Adds TransUnion as Vehicle valuation provided
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function registerTransApiValuationProvider(Varien_Event_Observer $observer)
    {
        Mage::helper('peppermint_transunion')->registerTransApiValuationProvider($observer);

        return $this;
    }

    /**
     * Intercepts and prints json output with the right data which comes from Transunion
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function printModelListJson(Varien_Event_Observer $observer)
    {
        /** @var Rockar_ExtendedRules_Adminhtml_Extendedrules_ExceptionBrandController $controller */
        $controller = $observer->getControllerAction();
        $request = $controller->getRequest();
        $brandId = reset($request->getParam('brand_id', null));
        if ($request->isAjax() && $brandId) {
            $controller->setFlag($request->getActionName(), Mage_Core_Controller_Front_Action::FLAG_NO_DISPATCH, true)
                ->getResponse()
                ->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json')
                ->setBody(Mage::helper('rockar_all')->jsonEncode(Mage::helper('peppermint_transunion/details')->getModelList($brandId)));
        }

        return $this;
    }
}
