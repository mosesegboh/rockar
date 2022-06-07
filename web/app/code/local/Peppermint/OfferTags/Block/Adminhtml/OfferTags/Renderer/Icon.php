<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Block_Adminhtml_OfferTags_Renderer_Icon
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
        $output = '';

        if ((string) $url = $row->getData('icon')) {
            $fullUrl = $url;

            if (!preg_match("/^https?\:\/\//", $url)) {
                $fullUrl = Mage::getBaseUrl('media') . $url;
            }

            $output = '<a href="' . $fullUrl . '"'
                . ' onclick="imagePreview(\'row_' . $row->getId() . '_image\'); return false;">'
                . '<img src="' . $fullUrl . '" id="row_' . $row->getId() . '_image" title="' . $this->getValue() . '"'
                . ' alt="' . $this->getValue() . '" class="small-image-preview v-middle" />'
                . '</a> ' . $url;
        }

        return $output;
    }
}
