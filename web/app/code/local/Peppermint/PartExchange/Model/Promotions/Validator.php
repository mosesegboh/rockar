<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Model_Promotions_Validator extends Rockar_PartExchange_Model_Promotions_Validator
{
    /**
     * {@inheritdoc}
     * Rewrite to re-load the product to get all the product attributes for rules validation
     * @todo This rewrite is to be remove after core fix
     *
     */
    public function process($product, $partExchange, $enabledScope = null)
    {
        if ($partExchange === null || empty($partExchange->getData())) {
            return 0;
        }

        if ($product->getId()) {
            $product = Mage::helper('peppermint_financingoptions/cache')->getProduct($product->getId());
        }

        return parent::process($product, $partExchange, $enabledScope);
    }
}
