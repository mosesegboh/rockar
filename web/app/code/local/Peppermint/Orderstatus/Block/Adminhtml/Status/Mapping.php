<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Orderstatus_Block_Adminhtml_Status_Mapping extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_blockGroup = 'peppermint_orderstatus';
        $this->_controller = 'adminhtml_status_mapping';
        $this->_headerText = $this->__('Order Status Mapping Grid');
        $this->_addButtonLabel = $this->__('Add New Order Status Mapping');

        parent::__construct();
    }

    /**
     * Getter for create url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }
}
