<?php
/**
 * @category  Peppermint
 * @package   Peppermint\OrderCap
 * @author    Lika Sikharulia <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OrderCap_Block_Adminhtml_OrderCap_Renderer_ActiveOrderCap
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        return '<p>' . $row->getData('order_cap') . '</p>';
    }
}