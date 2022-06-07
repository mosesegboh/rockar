<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Block_Adminhtml_OfferTags_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('offerTagsGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('offertag_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('peppermint_offertags/offerTags')->getCollection();
        $this->setCollection($collection);
        Mage::dispatchEvent(
            'peppermint_offerTags_adminhtml_offerTags_grid_prepare_collection',
            ['collection' => $collection]
        );

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('offertag_id', [
            'header' => $this->__('ID'),
            'index' => 'offertag_id'
        ]);

        $this->addColumn('name', [
            'header' => $this->__('Item Name'),
            'index' => 'name'
        ]);

        $this->addColumn('action_type', [
            'header' => $this->__('Action type'),
            'index' => 'action_type'
        ]);

        $this->addColumn('label', [
            'header' => $this->__('Offer Tag Label'),
            'index' => 'label'
        ]);

        $this->addColumn('icon',
            [
                'header' => $this->__('Icon'),
                'index' => 'icon',
                'renderer' => 'Peppermint_OfferTags_Block_Adminhtml_OfferTags_Renderer_Icon',
                'filter' => false,
                'sortable' => false,
                'column_css_class' => 'max_img_height_150'
            ]
        );

        $this->addColumn('sort_order',
            [
                'header' => $this->__('Sort Order'),
                'width' => '50px',
                'index' => 'sort_order'
            ]
        );

        Mage::dispatchEvent(
            'peppermint_offertags_adminhtml_offertags_grid',
            ['block' => $this]
        );

        return parent::_prepareColumns();
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getGridUrl()
    {
        $params['_current'] = true;

        return $this->getUrl('*/*/grid', $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['offertag_id' => $row->getId()]);
    }
}