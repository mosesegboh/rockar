<?php

/**
 * @category  Peppermint
 * @package   Peppermint\Catalog
 * @author    Lucaci Stefan <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */
class Peppermint_Catalog_Block_Product_StandardFeatures extends Rockar_Catalog_Block_Product_StandardFeatures
{
    /**
     * Set template for customized rendering
     *
     * Rockar_Catalog_Block_Product_StandardFeatures constructor.
     */
    public function __construct()
    {
        $this->setTemplate('peppermint/catalog/product/standard_features.phtml');
    }

    /**
     * Apply customized rendering
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->setElement($element)->toHtml();
    }
}
