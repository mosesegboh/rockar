<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar_Orderamend') . DS .
    'Adminhtml' . DS . 'Order' . DS . 'Amend' . DS . 'CouponController.php';

class Peppermint_Orderamend_Adminhtml_Order_Amend_CouponController extends Rockar_Orderamend_Adminhtml_Order_Amend_CouponController
{
    /**
     * Fixes parent apply coupon action when the same coupon could not be readded
     *
     * @TODO When fix will be implemented in Rockar CORE module this action
     * (and whole overwrite) can be removed. See CORE-1540
     */
    public function applyCouponAction()
    {
        $response = [
            'status' => static::STATUS_SUCCESS,
        ];

        $coreHelper = Mage::helper('core');
        try {
            if ($couponCode = $this->getRequest()->getParam('couponCode', false)) {
                $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();

                // Reset outdated information when new coupon code is added
                Mage::getSingleton('adminhtml/session_quote')->updatePriceOverrideData(
                    ['coupon_discount' => null]
                );

                $quote->setCouponCode($couponCode)
                    ->collectTotals()
                    ->save();

                if ($couponCode == $quote->getCouponCode()) {
                    $response['success'] = true;
                    $response['message'] = $this->__(
                        'Coupon code "%s" was applied.', $coreHelper->htmlEscape($couponCode)
                    );
                    $response['code'] = $couponCode;
                    //need to update decorated coupon priceOverride data in session
                    Mage::helper('rockar_orderamend/priceOverride')->updateCouponDataQuoteSession();
                } else {
                    $response['error'] = $this->__('Coupon code "%s" is not valid.', $coreHelper->htmlEscape($couponCode));
                    $response['status'] = static::STATUS_ERROR ;
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $response = [
                'status' => static::STATUS_ERROR,
                'error' => $e->getMessage(),
            ];
        }

        $this->sendJson($response);
    }
}
