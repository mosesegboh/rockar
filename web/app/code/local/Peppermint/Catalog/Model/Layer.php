<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Layer extends Rockar_ApprovedUsed_Model_Layer
{
    /**
     * {@inheritDoc}
     */
    public function getFilterableAttributes()
    {
        $collection = parent::getFilterableAttributes();

        if (!is_array($collection)) {
            $collection->addItem(Mage::getModel('peppermint_offertags/offerTagAttribute'));
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareProductCollection($collection)
    {
        parent::prepareProductCollection($collection);
        $storeId = Mage::app()->getStore()
            ->getId();

        if (Mage::helper('peppermint_all/store')->getDemoStoreId() === $storeId) {
            return $this;
        }

        $attributeFields = array_keys(
            Mage::getSingleton('core/resource')->getConnection('core_read')
                ->describeTable(
                    Mage::helper('peppermint_catalogrule')->getFlatForRuleIdxTablePrefix() . $storeId
                )
        );

        $collection->addAttributeToSelect($attributeFields);
        $financeFilters = Mage::app()->getRequest()->getParam('financeFilters', []);
        $helper = Mage::helper('financing_options');

        if (!empty($financeFilters) && isset($financeFilters['method'])) {
            $savedFinanceData = $helper->getFinanceData();
            $savedFinanceData[$financeFilters['method']] = $financeFilters;
            $savedFinanceData['method'] = (int) $financeFilters['method'];
            $savedFinanceData['group_id'] = (int) $financeFilters['method'];
            $helper->setFinanceData($savedFinanceData);
        }

        $activePayment = $helper->getActivePayment();

        Mage::helper('peppermint_offertags')->appendOfferTagsToProductCollection($collection, $activePayment['group_id']);

        return $this;
    }
}

