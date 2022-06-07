<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Adminhtml_Customer_Edit_Tab_Documents extends Rockar_Customer_Block_Adminhtml_Customer_Edit_Tab_Documents
{
    /**
     * Adds new column to the grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid|Rockar_Customer_Block_Adminhtml_Customer_Edit_Tab_Documents|void
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter(
            'initial_filename',
            [
                'header' => $this->__('Initial File Name'),
                'index' => 'initial_filename',
                'type' => 'text',
                'filter' => false,
                'sortable' => false
            ],
            'filename'
        );

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}
