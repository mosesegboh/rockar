<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ProductPods
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_ProductPods_Model_Resource_Item_Collection extends Rockar_ProductPods_Model_Resource_Item_Collection
{
    /**
     * List of attribute to populate in array
     *
     * @var array
     */
    protected $_itemFields = [
        'attribute_code',
        'display',
        'place',
        'icon',
        'hover_icon',
        'big_icon',
        'hover_text',
        'text_template',
        'label',
    ];
}
