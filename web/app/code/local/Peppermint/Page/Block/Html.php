<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Page
 * @author    Artis Viblo <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Page_Block_Html extends Mage_Page_Block_Html
{
    /**
     * Rewrite footer block to add additional DOM variables.
     *
     * @return mixed
     */
    public function getAbsoluteFooter()
    {
        $html = $_transportObject = new Varien_Object();
        Mage::dispatchEvent('peppermint_before_absolute_footer', ['html' => $html]);

        return $html->getHtml() . Mage::getStoreConfig('design/footer/absolute_footer');
    }
}
