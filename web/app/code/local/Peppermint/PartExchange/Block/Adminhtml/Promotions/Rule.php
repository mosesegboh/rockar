<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Block_Adminhtml_Promotions_Rule extends Rockar_PartExchange_Block_Adminhtml_Promotions_Rule
{
    /**
     * Peppermint_PartExchange_Block_Adminhtml_Promotions_Rule constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/partexchange_promotions/actions/add_new_rule')) {
            $this->_removeButton('add');
        }
    }
}
