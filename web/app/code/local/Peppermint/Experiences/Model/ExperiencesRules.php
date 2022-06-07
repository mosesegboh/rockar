<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Model_ExperiencesRules extends Mage_Rule_Model_Abstract
{
    /**
     * Coupon types
     */
    const COUPON_TYPE_NO_COUPON = 1;
    const COUPON_TYPE_SPECIFIC = 2;

    /**
     * Store coupon code generator instance
     *
     * @var Peppermint_Experiences_Model_Coupon_CodegeneratorInterface
     */
    protected static $_couponCodeGenerator;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'peppermint_experiences_rule';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * Rule's primary coupon
     *
     * @var Peppermint_Experiences_Model_Coupon
     */
    protected $_primaryCoupon;

    /**
     * Rule's subordinate coupons
     *
     * @var array of Peppermint_Experiences_Model_Coupon
     */
    protected $_coupons;

    /**
     * Coupon types cache for lazy getter
     *
     * @var array
     */
    protected $_couponTypes;

    /**
     * Set resource model and Id field name
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_experiences/experiencesRules');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Returns code mass generator instance for auto generated specific coupons
     *
     * @return Peppermint_Experiences_Model_Coupon_CodegeneratorInterface
     */
    public static function getCouponMassGenerator()
    {
        return Mage::getSingleton('peppermint_experiences/coupon_massgenerator');
    }

    /**
     * Set coupon code and uses per coupon
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->setCouponCode($this->getPrimaryCoupon()->getCode());

        if ($this->getUsesPerCoupon() !== null && !$this->getUseAutoGeneration()) {
            $this->setUsesPerCoupon($this->getPrimaryCoupon()->getUsageLimit());
        }

        return parent::_afterLoad();
    }

    /**
     * Save/delete coupon
     *
     * @return $this
     */
    protected function _afterSave()
    {
        $couponCode = trim($this->getCouponCode());

        if (strlen($couponCode)
            && $this->getCouponType() == self::COUPON_TYPE_SPECIFIC
            && !$this->getUseAutoGeneration()
        ) {
            $this->getPrimaryCoupon()
                ->setCode($couponCode)
                ->setUsageLimit($this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null)
                ->setUsagePerCustomer($this->getUsesPerCustomer() ? $this->getUsesPerCustomer() : null)
                ->setExpirationDate($this->getToDate())
                ->save();
        } else {
            $this->getPrimaryCoupon()->delete();
        }

        parent::_afterSave();

        return $this;
    }

    /**
     * Get rule condition combine model instance
     *
     * @return Rockar_PartExchange_Model_Promotions_Rule_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('peppermint_experiences/condition_combine');
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return Varien_Object
     */
    public function getActionsInstance()
    {
        return Mage::getModel('peppermint_experiences/condition_combine');
    }

    /**
     * Returns code generator instance for auto generated coupons
     *
     * @return Mage_Core_Model_Abstract
     */
    public static function getCouponCodeGenerator()
    {
        if (!self::$_couponCodeGenerator) {
            return Mage::getSingleton('peppermint_experiences/coupon_codegenerator', ['length' => 16]);
        }

        return self::$_couponCodeGenerator;
    }

    /**
     * Set code generator instance for auto generated coupons
     *
     * @param Peppermint_Experiences_Model_Coupon_CodegeneratorInterface
     */
    public static function setCouponCodeGenerator(Peppermint_Experiences_Model_Coupon_CodegeneratorInterface $codeGenerator)
    {
        self::$_couponCodeGenerator = $codeGenerator;
    }

    /**
     * Retrieve rule's primary coupon
     *
     * @return Peppermint_Experiences_Model_Coupon
     */
    public function getPrimaryCoupon()
    {
        if ($this->_primaryCoupon === null) {
            $this->_primaryCoupon = Mage::getModel('peppermint_experiences/coupon');
            $this->_primaryCoupon->loadPrimaryByRule($this->getId());
            $this->_primaryCoupon->setRule($this)->setIsPrimary(true);
        }

        return $this->_primaryCoupon;
    }

    /**
     * Get experiences rule customer group Ids
     *
     * @return array
     */
    public function getCustomerGroupIds()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array) $customerGroupIds);
        }

        return $this->_getData('customer_group_ids');
    }

    /**
     * Retrieve subordinate coupons
     *
     * @return array of Peppermint_Experiences_Model_Coupon
     */
    public function getCoupons()
    {
        if ($this->_coupons === null) {
            $collection = Mage::getResourceModel('peppermint_experiences/coupon_collection');
            /** @var Peppermint_Experiences_Model_Resource_Coupon_Collection */
            $collection->addRuleToFilter($this);
            $this->_coupons = $collection->getItems();
        }

        return $this->_coupons;
    }

    /**
     * Retrieve coupon types
     *
     * @return array
     */
    public function getCouponTypes()
    {
        if ($this->_couponTypes === null) {
            $this->_couponTypes = [
                self::COUPON_TYPE_NO_COUPON => Mage::helper('peppermint_experiences')->__('No Coupon'),
                self::COUPON_TYPE_SPECIFIC => Mage::helper('peppermint_experiences')->__('Specific Coupon'),
            ];
        }

        return $this->_couponTypes;
    }

    /**
     * Acquire coupon instance
     *
     * @param bool $saveNewlyCreated Whether or not to save newly created coupon
     * @param int $saveAttemptCount Number of attempts to save newly created coupon
     *
     * @return Peppermint_Experiences_Model_Coupon|null
     */
    public function acquireCoupon($saveNewlyCreated = true, $saveAttemptCount = 10)
    {
        if ($this->getCouponType() == self::COUPON_TYPE_NO_COUPON) {
            return null;
        }

        if ($this->getCouponType() == self::COUPON_TYPE_SPECIFIC) {
            return $this->getPrimaryCoupon();
        }

        /** @var Peppermint_Experiences_Model_Coupon $coupon */
        $coupon = Mage::getModel('peppermint_experiences/coupon');
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
            Mage::throwException(Mage::helper('peppermint_experiences')->__('Can\'t acquire coupon.'));
        }

        return $coupon;
    }
}
