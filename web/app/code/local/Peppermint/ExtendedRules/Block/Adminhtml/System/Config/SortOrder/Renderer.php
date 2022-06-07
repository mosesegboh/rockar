<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Block_Adminhtml_System_Config_SortOrder_Renderer extends Rockar_ExtendedRules_Block_Adminhtml_System_Config_SortOrder_Renderer
{
    protected $_fieldsToRender = [
        'brands' => 'Brands',
        'age' => 'Age',
        'exceptionCap' => 'Exception By MM Code',
        'exceptionBrand' => 'Exception By Brand',
        'dayInMonth' => 'Day In Month',
        'priceRanges' => 'Price Ranges',
        'mileage' => 'Mileage'
    ];
}
