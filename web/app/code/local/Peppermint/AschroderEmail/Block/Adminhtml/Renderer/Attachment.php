<?php

/**
 * @category     Peppermint
 * @package      Peppermint_AschroderEmail
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_AschroderEmail_Block_Adminhtml_Renderer_Attachment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Convert path file to anchor
     * 
     * @param Varien_Object $row
     * 
     * @return string html 'a' tag with the link to attachment or empty space
     */
    public function render(Varien_Object $row)
    {
        $value = $row->getData('attachment_path');
        if (is_null($value)) {
            return '';
        }

        return '<a href="' . $value . '" target="_blank">' . $value . '</a>';
    }
}
