<?php
/**
 * @category  Peppermint
 * @package   Peppermint\OrderCap
 * @author    Lika Sikharulia <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OrderCap_Block_Adminhtml_OrderCap_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        $helper = Mage::helper('peppermint_ordercap');
        $colors = [
            'green' => $helper->getOrderCapPercentages('Peppermint_OrderCap/general/green'),
            'amber' => $helper->getOrderCapPercentages('Peppermint_OrderCap/general/amber'),
            'red'   => $helper->getOrderCapPercentages('Peppermint_OrderCap/general/red')
        ];

        $activeOrdersCount = $helper->getActiveOrders($row->getData('code'));
        $percentage = ($activeOrdersCount > 0 && $row->getData('order_cap') > 0) 
            ? round(($activeOrdersCount / $row->getData('order_cap')) * 100, 0) 
            : 0;

        $chosenColor = ($percentage <= $colors['green']) 
            ? 'grid-severity-notice' 
            : (($percentage <= $colors['amber']) ? 'grid-severity-major' : 'grid-severity-critical');

        return '<span class="' . $chosenColor . '"><span>' . $percentage . '%</span></span>';
    }
}