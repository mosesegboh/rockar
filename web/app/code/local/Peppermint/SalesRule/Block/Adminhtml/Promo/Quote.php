<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Block_Adminhtml_Promo_Quote extends Mage_Adminhtml_Block_Promo_Quote
{
    /**
     * Peppermint_SalesRule_Block_Adminhtml_Promo_Quote constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/quote/actions/add_new_rule')) {
            $this->_removeButton('add');
        }
    }
}
