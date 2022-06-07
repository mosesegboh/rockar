<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedProductGrid
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedProductGrid_Model_Observer
{
    /** @var array */
    protected $_customAttributesToSelect;

    /** @var boolean */
    protected $financingOptionsEnabled;

    public function __construct()
    {
        $this->_customAttributesToSelect = [
            'bag_mm_code',
            'asset_id'
        ];

        $this->financingOptionsEnabled = Mage::helper('catalog')->isModuleEnabled('Rockar_FinancingOptions');
    }

    /**
     * Add custom columns
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_ExtendedProductGrid_Model_Observer
     */
    public function addCustomColumns(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Block_Catalog_Product_Grid $productGrid */
        $productGrid = $observer->getData('product_grid');
        $productGrid->addColumnAfter(
            'manufacturer',
            [
                'header' => $productGrid->__('Manufacturer'),
                'index' => 'manufacturer',
                'type' => 'options',
                'options' => Mage::getModel('rockar_extended_product_grid/adminhtml_system_config_source_attribute_options')->toArray('manufacturer'),
                'renderer' => 'Rockar_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_Attribute',
            ],
            'image'
        )->addColumnAfter(
            'bag_mm_code',
            [
                'header' => $productGrid->__('M&M Code'),
                'index' => 'bag_mm_code',
            ],
            'cap_code'
        )->addColumnAfter(
            'image',
            [
                'header' => $productGrid->__('Small Image'),
                'index' => 'image',
                'sortable' => false,
                'column_css_class' => 'max_img_height_80',
                'width' => '70',
                'renderer' => 'Peppermint_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_Image',
                'type' => 'options',
                'options' => [
                    $productGrid::PRODUCT_HAS_NO_GALLERY => $productGrid->__('Without images'),
                    $productGrid::PRODUCT_HAS_GALLERY => $productGrid->__('With images'),
                ],
                'filter_condition_callback' => [$this, '_filterHasImages'],
            ],
            'parent_link_ids'
        )->addColumnAfter(
            'product_category',
            [
                'header' => $productGrid->__('Product Category'),
                'index' => 'category_id',
                'type' => 'options',
                'options' => Mage::getModel('peppermint_extendedproductgrid/adminhtml_system_config_source_categories')
                    ->toArray(),
                'renderer' => 'Peppermint_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_Category',
                'filter_condition_callback' => [$this, 'filterCategories']
            ],
            'websites'
        )->getColumn('cap_code')
            ->setHeader($productGrid->__('Asset ID'));

        $productGrid->getColumn('parent_link_ids')
            ->addData([
                'index' => 'conf_ids',
                'sortable' => true,
                'filter' => '',
                'type' => 'text',
                'is_system' => false,
                'renderer' => 'Peppermint_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_Configurables',
                'filter_condition_callback' => [$this, 'filterConfigurables']
            ]);

        if ($this->financingOptionsEnabled) {
            $productGrid->getColumn('finance_products_amount')
                ->addData([
                    'index' => 'finance_products_amount',
                    'sortable' => true,
                    'filter' => '',
                    'type' => 'number',
                    'renderer' => 'Peppermint_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_FinancingAmount',
                    'filter_condition_callback' => [$this, 'filterFinanceProducts']
                ]);
        }

        return $this;
    }

    /**
     * Add attribute to collection
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_ExtendedProductGrid_Model_Observer
     */
    public function additionalAttributes(Varien_Event_Observer $observer)
    {
        $collection = $observer->getCollection();
        $collection->addAttributeToSelect($this->_customAttributesToSelect);

        $collection->joinTable(
            ['category_product' => new Zend_Db_Expr('(
                SELECT product_id, GROUP_CONCAT(category_id) AS category_id
                FROM catalog_category_product
                GROUP BY product_id)'
            )],
            'product_id=entity_id',
            ['category_id' => 'category_id'],
            null,
            'left'
        )->joinTable(
            ['configurables' => new Zend_Db_Expr('(
                SELECT product_id, GROUP_CONCAT(parent_id) AS conf_ids
                FROM catalog_product_super_link
                GROUP BY product_id)'
            )],
            'product_id=entity_id',
            ['conf_ids' => 'conf_ids'],
            null,
            'left'
        );

        if ($this->financingOptionsEnabled) {
            if (Mage::helper('catalog')->isModuleEnabled('Rockar_ApprovedUsed')) {
                $collection->joinTable(
                    ['finance_products' => new Zend_Db_Expr('(
                        SELECT product_id, SUM(num) AS finance_products_amount
                        FROM (
                            SELECT product_id, COUNT(*) AS num
                            FROM rockar_financing_data_products
                            GROUP BY product_id
                            UNION
                            SELECT product_id, COUNT(*) AS num
                            FROM rockar_financing_approved_used_data_products
                            GROUP BY product_id
                        ) AS finance_products_sub
                        GROUP BY product_id)'
                    )],
                    'product_id=entity_id',
                    ['finance_products_amount' => 'finance_products_amount'],
                    null,
                    'left'
                );
            } else {
                $collection->joinTable(
                    ['finance_products' => new Zend_Db_Expr('(
                        SELECT product_id, COUNT(*) AS finance_products_amount
                        FROM rockar_financing_data_products
                        GROUP BY product_id)'
                    )],
                    'product_id=entity_id',
                    ['finance_products_amount' => 'finance_products_amount'],
                    null,
                    'left'
                );
            }
        }

        return $this;
    }

    /**
     * Filter products by category
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return $this
     */
    public function filterCategories($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection->addAttributeToFilter('category_id', ['finset' => $value]);

        return $this;
    }

    /**
     * Filter products by associated configurable
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return $this
     */
    public function filterConfigurables($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection->addAttributeToFilter('conf_ids', ['finset' => $value]);

        return $this;
    }

    /**
     * Filter products by finance product amount
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return $this
     */
    public function filterFinanceProducts($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $filter = [
            ['attribute' => 'finance_products_amount', [$value]]
        ];

        if (!$value['from']) {
            $filter[] = ['attribute' => 'finance_products_amount', 'null' => true];
        }

        $collection->addAttributeToFilter($filter);

        return $this;
    }
}

