<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_Experiences_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('experiencesGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('experience_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('peppermint_experiences/experiences')->getCollection();
        $this->setCollection($collection);
        Mage::dispatchEvent(
            'peppermint_experiences_adminhtml_experiences_grid_prepare_collection',
            ['collection' => $collection]
        );

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('experience_id', [
            'header' => $this->__('ID'),
            'index' => 'experience_id'
        ]);

        $this->addColumn('name', [
            'header' => $this->__('Experience Name'),
            'index' => 'name'
        ]);

        $this->addColumn('label', [
            'header' => $this->__('Experience Label'),
            'index' => 'label'
        ]);

        $this->addColumn('image',
            [
                'header' => $this->__('Image'),
                'index' => 'image',
                'renderer' => 'Peppermint_Experiences_Block_Adminhtml_Experiences_Renderer_Image',
                'filter' => false,
                'sortable' => false
            ]
        );

        Mage::dispatchEvent(
            'peppermint_experiences_adminhtml_experiences_grid',
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
        return $this->getUrl('*/*/edit', ['experience_id' => $row->getId()]);
    }
}