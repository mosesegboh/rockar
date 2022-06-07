<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Block_Adminhtml_ModelMatrix extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Peppermint_Catalog_Block_Adminhtml_ModelMatrix constructor.
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_modelMatrix';
        $this->_blockGroup = 'peppermint_catalog';
        $this->_headerText = Mage::helper('customer')->__('Manage Model Matrix');
        parent::__construct();
        $this->_removeButton('add');
    }
}
