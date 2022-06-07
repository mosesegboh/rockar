<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ExtendedProductGrid
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_Configurables
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders associated configurables
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        if ($row->getData('type_id') !== 'simple') {
            return '';
        }

        $links = [];
        $ids = explode(',', $row->getData('conf_ids'));

        foreach ($ids as $id) {
            if (!$id) {
                continue;
            }

            $url = Mage::helper('adminhtml')->getUrl(
                'adminhtml/catalog_product/edit',
                [
                    'id' => $id
                ]
            );

            $links[] = "<a href='{$url}' title='{$url}'>" . $id . '</a>';
        }

        return implode('<br/>', $links);
    }
}
