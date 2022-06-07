<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Block_Adminhtml_Promo_Catalog extends Mage_Adminhtml_Block_Promo_Catalog
{
    /**
     * Peppermint_CatalogRule_Block_Adminhtml_Promo_Catalog constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/catalog/actions/apply_rules')) {
            $this->_removeButton('apply_rules');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/catalog/actions/add_new_rule')) {
            $this->_removeButton('add');
        }
    }
}
