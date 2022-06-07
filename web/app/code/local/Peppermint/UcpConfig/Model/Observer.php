<?php
/**
 * @category  Peppermint
 * @package   Peppermint_UcpConfig
 * @author    Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_UcpConfig_Model_Observer extends Varien_Event_Observer
{
    /**
     * Force empty collection if ucpConfigSkip param is present
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function modifyCollection(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getRequest()->getParam('ucpConfigSkip')) {
            if ($collection = $observer->getCollection()) {
                $collection->addFieldToFilter('entity_id', 0);
            }
        }

        return $this;
    }

    /**
     * Generate and redirect to a deep link based on dsp and configurator data mapping
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function generateUrl(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('Peppermint_UcpConfig');

        if (!$helper->getApiEndpoint()
            || !$configId = Mage::app()->getRequest()->getParam(Peppermint_UcpConfig_Helper_Data::CONFIG_ID)
        ) {
            return $this;
        }

        $redirect = $helper->getRedirect($configId);
        Mage::app()->getResponse()->setRedirect(Mage::getUrl($redirect['url'], ['_query' => $redirect['query']]));

        return $this;
    }
}
