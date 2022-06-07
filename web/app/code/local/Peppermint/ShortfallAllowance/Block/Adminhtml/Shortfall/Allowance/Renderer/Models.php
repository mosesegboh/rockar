<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ShortfallAllowance_Block_Adminhtml_Shortfall_Allowance_Renderer_Models extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * render.
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function render(Varien_Object $row)
    {
        $index = $this->getColumn()->getIndex();
        $shortfallIds = explode(',', $row->getData($index));

        $models = [];
        $modelOptions = Mage::getModel('peppermint_shortfallallowance/adminhtml_system_config_source_attribute_options')
            ->toArray(Mage::helper('peppermint_shortfallallowance')->getModelAttribute());

        foreach ($shortfallIds as $shortfallId) {
            if (isset($modelOptions[$shortfallId])) {
                $models[] = $modelOptions[$shortfallId];
            }
        }

        return implode('<br/>', $models);
    }
}
