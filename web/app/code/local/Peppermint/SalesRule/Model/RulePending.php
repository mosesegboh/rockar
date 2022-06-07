<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Model_RulePending extends Mage_SalesRule_Model_Rule
{
    /**
     * Separator for store labels array
     */
    const STORE_LABELS_SEPARATOR = '|||';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'peppermint_salesrule_rule_pending';

    /**
     * Set resource model and Id field name
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('peppermint_salesrule/rulePending');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Returns code generator instance for auto generated coupons
     *
     * @return Mage_SalesRule_Model_Coupon_CodegeneratorInterface
     */
    public static function getCouponCodeGenerator()
    {
        if (!self::$_couponCodeGenerator) {
            return Mage::getSingleton('salesrule/coupon_codegenerator', ['length' => 16]);
        }

        return self::$_couponCodeGenerator;
    }

    /**
     * Returns code generator instance for auto generated coupons
     *
     * @return Mage_SalesRule_Model_Coupon_CodegeneratorInterface
     */
    public static function getCouponMassGenerator()
    {
        if (!self::$_couponCodeGenerator) {
            return Mage::getSingleton('peppermint_salesrule/coupon_massgenerator');
        }

        return self::$_couponCodeGenerator;
    }

    /**
     * Retrieve rule's primary coupon
     *
     * @return Mage_SalesRule_Model_Coupon
     */
    public function getPrimaryCoupon()
    {
        if ($this->_primaryCoupon === null) {
            $this->_primaryCoupon = Mage::getModel('peppermint_salesrule/couponPending');
            $this->_primaryCoupon->loadPrimaryByRule($this->getId());
            $this->_primaryCoupon->setRule($this)->setIsPrimary(true);
        }

        return $this->_primaryCoupon;
    }

    /**
     * Get sales rule customer group Ids
     *
     * @return array
     */
    public function getCustomerGroupIds()
    {
        return $this->_getData('customer_group_ids');
    }

    /**
     * Get Rule label by specified store
     *
     * @param Mage_Core_Model_Store|int|bool|null $store
     * @return string|bool
     */
    public function getStoreLabel($store = null)
    {
        $storeId = Mage::app()->getStore($store)->getId();
        $labels = (array) $this->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }

        return false;
    }

    /**
     * Set if not yet and retrieve rule store labels
     *
     * @return array
     */
    public function getStoreLabels()
    {
        $result = [];

        if ($storeLabels = $this->_getData('store_labels')) {
            if (is_array($storeLabels)) {
                return $storeLabels;
            }

            $labels = explode(static::STORE_LABELS_SEPARATOR, $storeLabels);

            foreach ($labels as $label) {
                [$key, $val] = explode('=', $label);
                $result[$key] = $val;
            }
        }

        return $result;
    }

    /**
     * Retrieve subordinate coupons
     *
     * @return array of Mage_SalesRule_Model_Coupon
     */
    public function getCoupons()
    {
        if ($this->_coupons === null) {
            $collection = Mage::getResourceModel('peppermint_salesrule/couponPending_collection');
            /** @var Mage_SalesRule_Model_Resource_Coupon_Collection */
            $collection->addRuleToFilter($this);
            $this->_coupons = $collection->getItems();
        }

        return $this->_coupons;
    }

    /**
     * Acquire coupon instance
     *
     * @param bool $saveNewlyCreated Whether or not to save newly created coupon
     * @param int  $saveAttemptCount Number of attempts to save newly created coupon
     * @return Mage_SalesRule_Model_Coupon|null
     */
    public function acquireCoupon($saveNewlyCreated = true, $saveAttemptCount = 10)
    {
        if ($this->getCouponType() == self::COUPON_TYPE_NO_COUPON) {
            return null;
        }

        if ($this->getCouponType() == self::COUPON_TYPE_SPECIFIC) {
            return $this->getPrimaryCoupon();
        }

        /** @var Mage_SalesRule_Model_Coupon $coupon */
        $coupon = Mage::getModel('peppermint_salesrule/couponPending');
        $coupon->setRule($this)
            ->setIsPrimary(false)
            ->setUsageLimit($this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null)
            ->setUsagePerCustomer($this->getUsesPerCustomer() ? $this->getUsesPerCustomer() : null)
            ->setExpirationDate($this->getToDate());

        $couponCode = self::getCouponCodeGenerator()->generateCode();
        $coupon->setCode($couponCode);

        $ok = false;

        if (!$saveNewlyCreated) {
            $ok = true;
        } else if ($this->getId()) {
            for ($attemptNum = 0; $attemptNum < $saveAttemptCount; $attemptNum++) {
                try {
                    $coupon->save();
                } catch (Exception $e) {
                    if ($e instanceof Mage_Core_Exception || $coupon->getId()) {
                        throw $e;
                    }
                    $coupon->setCode(
                        $couponCode .
                        self::getCouponCodeGenerator()->getDelimiter() .
                        sprintf('%04u', rand(0, 9999))
                    );
                    continue;
                }
                $ok = true;
                break;
            }
        }

        if (!$ok) {
            Mage::throwException(Mage::helper('salesrule')->__('Can\'t acquire coupon.'));
        }

        return $coupon;
    }

    /**
     * Prepare data before saving
     *
     * @return Mage_Rule_Model_Abstract
     */
    protected function _beforeSave()
    {
        // Check if discount amount not negative
        if ($this->hasDiscountAmount()) {
            if ((int) $this->getDiscountAmount() < 0) {
                Mage::throwException(Mage::helper('rule')->__('Invalid discount amount.'));
            }
        }

        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsConditions();
        }

        // Serialize actions
        if ($this->getActions()) {
            $this->setActionsSerialized(serialize($this->getActions()->asArray()));
            $this->unsActions();
        }

        /**
         * Prepare website Ids if applicable and if they were set as array.
         */
        if ($this->hasWebsiteIds()) {
            $websiteIds = $this->getData('website_ids');

            if (is_array($websiteIds) && !empty($websiteIds)) {
                $this->setWebsiteIds(implode(',', $websiteIds));
            }
        }

        /**
         * Prepare customer group Ids if applicable and if they were set as array.
         */
        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();

            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }

        /**
         * Prepare store labels
         */
        if ($this->hasStoreLabels()) {
            $storeLabels = $this->getStoreLabels();

            if (is_array($storeLabels)) {
                $this->setStoreLabels(
                    urldecode(http_build_query($storeLabels, '', static::STORE_LABELS_SEPARATOR))
                );
            }
        }

        Mage_Core_Model_Abstract::_beforeSave();

        return $this;
    }
}
