<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Block_Adminhtml_OfferTags extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    const OFFERTAGS_BLOCK_GROUP = 'peppermint_offertags';

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->_blockGroup = self::OFFERTAGS_BLOCK_GROUP;
        $this->_controller = 'adminhtml_offerTags';
        $this->_headerText = $this->__('Offer Tags');

        parent::__construct();
    }
}