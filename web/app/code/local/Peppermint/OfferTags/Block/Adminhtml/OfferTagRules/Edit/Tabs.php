<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili<techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Block_Adminhtml_OfferTagRules_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('offerTagRules_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Offer Tag Rules'));
    }
}
