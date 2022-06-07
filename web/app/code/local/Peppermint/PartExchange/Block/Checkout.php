<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Block_Checkout extends Rockar_PartExchange_Block_Abstract
{
    /**
     * Constructor, sets customer part exchange as protected variable if part exchange is in session
     *
     * Peppermint_PartExchange_Block_Checkout constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $pxModel = Mage::helper('rockar_partexchange/data_quote')->getQuotePartExchanges();

        if ($pxModel->getId()) {
            $varienObject = unserialize($pxModel->getSerializedObject());
            if (!$varienObject->getData('id')) {
                $varienObject->addData(['px_id' => $pxModel->getId()]);
            }

            //send out event to get updated px value based on product leadtime
            $product = Mage::helper('rockar_checkout')->getQuoteItem()
                ->getProduct();

            if ($varienObject->getData('totals') && $product) {
                Mage::dispatchEvent(
                    'rockar_financing_options_get_px_value',
                    [
                        'px' => $varienObject,
                        'product' => $product
                    ]
                );
            }

            $varienObject->addData([
                'is_active' => 1,
                'is_expired' => Mage::helper('rockar_partexchange')->isExpired($pxModel)
            ]);
        } else {
            $varienObject = new Varien_Object();
        }

        $this->_customerPartExchange = $varienObject;

        $template = Mage::helper('rockar_partexchange/serviceFactory')->getVehicleDetailsHelper()
            ->getCheckoutTemplate();
        $this->setTemplate($template);

        parent::__construct($args);
    }
}
