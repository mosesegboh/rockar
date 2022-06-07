<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_Action_Index_Refresh extends Mage_CatalogRule_Model_Action_Index_Refresh
{
    /**
     * Prepare temporary data
     *
     * @param Mage_Core_Model_Website $website
     * @return Varien_Db_Select
     */
    protected function _prepareTemporarySelect(Mage_Core_Model_Website $website)
    {
        /** @var $catalogFlatHelper Mage_Catalog_Helper_Product_Flat */
        $catalogFlatHelper = $this->_factory->getHelper('catalog/product_flat');

        /** @var $eavConfig Mage_Eav_Model_Config */
        $eavConfig = $this->_factory->getSingleton('eav/config');
        $priceAttribute = $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'price');

        $select = $this->_connection->select()
            ->from(
                ['rp' => $this->_resource->getTable('catalogrule/rule_product')],
                []
            )
            ->joinInner(
                ['r' => $this->_resource->getTable('catalogrule/rule')],
                'r.rule_id = rp.rule_id',
                []
            )
            ->where('rp.website_id = ?', $website->getId())
            ->order(
                ['rp.product_id', 'rp.customer_group_id', 'rp.sort_order', 'rp.rule_product_id']
            )
            ->joinLeft(
                [
                    'pg' => $this->_resource->getTable('catalog/product_attribute_group_price')
                ],
                'pg.entity_id = rp.product_id AND pg.customer_group_id = rp.customer_group_id'
                . ' AND pg.website_id = rp.website_id',
                []
            )
            ->joinLeft(
                [
                    'pgd' => $this->_resource->getTable('catalog/product_attribute_group_price')
                ],
                'pgd.entity_id = rp.product_id AND pgd.customer_group_id = rp.customer_group_id'
                . ' AND pgd.website_id = 0',
                []
            );

        $storeId = $website->getDefaultStore()->getId();

        if ($catalogFlatHelper->isEnabled() && $storeId && $catalogFlatHelper->isBuilt($storeId)) {
            $select->joinInner(
                ['p' => $this->_factory->getHelper('peppermint_catalogrule')->getFlatForRuleIdxTablePrefix() . $storeId],
                'p.entity_id = rp.product_id',
                []
            );
            $priceColumn = $this->_connection->getIfNullSql(
                $this->_connection->getIfNullSql(
                    $this->_connection->getCheckSql(
                        'pg.is_percent = 1',
                        'p.price * (100 - pg.value)/100',
                        'pg.value'
                    ),
                    $this->_connection->getCheckSql(
                        'pgd.is_percent = 1',
                        'p.price * (100 - pgd.value)/100',
                        'pgd.value'
                    )
                ),
                'p.price'
            );
        } else {
            $productTable = $this->_resource->getTable(['catalog/product', $priceAttribute->getBackendType()]);

            $select->joinInner(
                ['pd' => $productTable],
                'pd.entity_id = rp.product_id AND pd.store_id = 0 AND pd.attribute_id = '
                . $priceAttribute->getId(),
                []
            )
                ->joinLeft(
                    ['p' => $productTable],
                    'p.entity_id = rp.product_id AND p.store_id = ' . $storeId
                    . ' AND p.attribute_id = pd.attribute_id',
                    []
                );

            $priceColumn = $this->_connection->getIfNullSql(
                $this->_connection->getIfNullSql(
                    $this->_connection->getCheckSql(
                        'pg.is_percent = 1',
                        $this->_connection->getIfNullSql(
                            'p.value',
                            'pd.value'
                        ) . ' * (100 - pg.value)/100',
                        'pg.value'
                    ),
                    $this->_connection->getCheckSql(
                        'pgd.is_percent = 1',
                        $this->_connection->getIfNullSql(
                            'p.value',
                            'pd.value'
                        ) . ' * (100 - pgd.value)/100',
                        'pgd.value'
                    )
                ),
                $this->_connection->getIfNullSql(
                    'p.value',
                    'pd.value'
                )
            );
        }

        $select->columns(
            [
                'grouped_id' => $this->_connection->getConcatSql(
                    ['rp.product_id', 'rp.customer_group_id'],
                    '-'
                ),
                'product_id'        => 'rp.product_id',
                'customer_group_id' => 'rp.customer_group_id',
                'from_date'         => 'r.from_date',
                'to_date'           => 'r.to_date',
                'action_amount'     => 'rp.action_amount',
                'action_operator'   => 'rp.action_operator',
                'action_stop'       => 'rp.action_stop',
                'sort_order'        => 'rp.sort_order',
                'price'             => $priceColumn,
                'rule_product_id'   => 'rp.rule_product_id',
                'from_time'         => 'rp.from_time',
                'to_time'           => 'rp.to_time'
            ]
        );

        return $select;
    }

    /**
     * Accessor to temporary select to allow Refresh/Row to get it
     *
     * @param Mage_Core_Model_Website $website
     * @return Varien_Db_Select
     */
    public function getPreparedTemporarySelect(Mage_Core_Model_Website $website)
    {
        return $this->_prepareTemporarySelect($website);
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
