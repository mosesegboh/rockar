<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_Action_Index_Refresh_Row extends Mage_CatalogRule_Model_Action_Index_Refresh_Row
{
    /**
     * Prepare temporary data select
     *
     * @param Mage_Core_Model_Website $website
     * @return Varien_Db_Select
     */
    protected function _prepareTemporarySelect(Mage_Core_Model_Website $website)
    {
        $select = Mage::getModel(
            'catalogrule/action_index_refresh',
            [
                'connection' => Mage::getSingleton('core/resource')->getConnection('core_write'),
                'factory'    => Mage::getModel('core/factory'),
                'resource'   => Mage::getResourceSingleton('catalogrule/rule'),
                'app'        => Mage::app(),
                'value'      => null
            ]
        )->getPreparedTemporarySelect($website);

        return $select->where('rp.product_id IN (?)', $this->_productId);
    }

    /**
     * Rewrite to avoid running index for demo store
     *
     * {@inheritDoc}
     */
    public function execute()
    {
        $this->_app->dispatchEvent('catalogrule_before_apply', ['resource' => $this->_resource]);

        /** @var $coreDate Mage_Core_Model_Date */
        $coreDate  = $this->_factory->getModel('core/date');
        $timestamp = $coreDate->gmtTimestamp('Today');
        $demoStoreCode = Mage::helper('peppermint_all/store')->getDemoStoreCode();

        foreach ($this->_app->getWebsites(false) as $website) {
            /** @var $website Mage_Core_Model_Website */
            if ($website->getDefaultStore() && $website->getCode() !== $demoStoreCode) {
                $this->_reindex($website, $timestamp);
            }
        }

        $this->_prepareGroupWebsite($timestamp);
        $this->_prepareAffectedProduct();
    }
}
