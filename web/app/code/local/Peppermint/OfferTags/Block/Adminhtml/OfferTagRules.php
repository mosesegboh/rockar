<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Block_Adminhtml_OfferTagRules extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->_blockGroup = 'peppermint_offertags';
        $this->_controller = 'adminhtml_offerTagRules';
        $this->_headerText = $this->__('Offer Tag Rules');

        parent::__construct();
    }
}
