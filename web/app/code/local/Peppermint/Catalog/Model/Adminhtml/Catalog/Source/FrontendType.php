<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Ketevani Revazishvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_Catalog_Model_Adminhtml_Catalog_Source_FrontendType
 */
class Peppermint_Catalog_Model_Adminhtml_Catalog_Source_FrontendType extends Rockar2_Catalog_Model_Adminhtml_Catalog_Source_FrontendType
{
    /**
     * Peppermint_Catalog_Model_Adminhtml_Catalog_Source_FrontendType constructor.
     */
    public function __construct()
    {
        $this->_types[Peppermint_Catalog_Helper_Filter::ATTRIBUTE_FRONTEND_DISPLAY_TYPE_SWITCHER] = 'Switchers';
    }
}
