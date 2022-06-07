<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Ketevani Revazishvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_Catalog_Helper_Filter
 */
class Peppermint_Catalog_Helper_Filter extends Rockar2_Catalog_Helper_Filter
{
    /**
     * Switcher type for filters
     */
    const ATTRIBUTE_FRONTEND_DISPLAY_TYPE_SWITCHER = 4; // Switchers

    /**
     * Gets attribute's display type for switchers
     *
     * @return int
     */
    public function getAttributeDisplayTypeSwitcher(): int
    {
        return self::ATTRIBUTE_FRONTEND_DISPLAY_TYPE_SWITCHER;
    }
}
