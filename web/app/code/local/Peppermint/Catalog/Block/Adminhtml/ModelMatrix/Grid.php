<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Block_Adminhtml_ModelMatrix_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Peppermint_Catalog_Block_Adminhtml_ModelMatrix_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('modelMatrix');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Peppermint_Catalog_Block_Adminhtml_ModelMatrix_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('peppermint_catalog/matrixMapping')->getCollection();
        $collection->getSelect()->group('model_carousel');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Populate collection with additional data.
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        parent::_afterLoadCollection();

        $collection = $this->getCollection();
        $mappingCollection = Mage::getModel('peppermint_catalog/matrixMapping')->getCollection();

        foreach ($collection as $item) {
            if (!$item->getBrand()) {
                $mappingCollection->addFieldToFilter('model_carousel', $item->getModelCarousel());

                foreach ($mappingCollection as $mappingItem) {
                    if ($mappingItem->getBrand()) {
                        $item->setBrand($mappingItem->getBrand());
                        break;
                    }
                }

                $mappingCollection->clear()->getSelect()->reset(\Zend_Db_Select::WHERE);
            }
        }

        return $this;
    }

    /**
     * Prepare Grid columns
     *
     * @return Peppermint_Catalog_Block_Adminhtml_ModelMatrix_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'model_carousel',
            [
                'header' => $this->__('Model'),
                'width' => '800px',
                'index' => 'model_carousel'
            ]
        );

        $this->addColumn(
            'brand',
            [
                'header' => $this->__('Brand'),
                'index' => 'brand'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => $this->__('Position'),
                'index' => 'position'
            ]
        );

        $this->addColumn(
            'run_out_date',
            [
                'header' => $this->__('Run Out Date'),
                'index' => 'run_out_date',
                'type' => 'date',
                'format' => 'F'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Return row url.
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getModelCarousel()]);
    }

    /**
     * Return grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
