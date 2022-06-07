<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_Experiences_Renderer_Image
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * render
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function render(Varien_Object $row)
    {
        if ($url = $row->getData('image')) {
            $fullUrl = $url;

            if (!preg_match("/^https?\:\/\//", $url)) {
                $fullUrl = Mage::getBaseUrl('media') . $url;
            }

            $output = '<a href="' . $fullUrl . '"'
                . ' onclick="imagePreview(\'row_' . $row->getId() . '_image\'); return false;">'
                . '<img height="100"  src="' . $fullUrl . '" id="row_' . $row->getId() . '_image" title="' . $this->getValue() . '"'
                . ' alt="' . $this->getValue() . '" class="small-image-preview v-middle" />'
                . '</a> ' . $url;
        }

        return $output ?? '';
    }
}
