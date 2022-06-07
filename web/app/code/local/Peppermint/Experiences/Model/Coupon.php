<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Model_Coupon extends Mage_Core_Model_Abstract
{
    /**
     * Coupon's owner rule instance
     *
     * @var Peppermint_Experiences_Model_ExperiencesRules
     */
    protected $_rule;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_experiences/coupon');
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if (!$this->getRuleId() && $this->_rule instanceof Peppermint_Experiences_Model_ExperiencesRules) {
            $this->setRuleId($this->_rule->getId());
        }

        return parent::_beforeSave();
    }

    /**
     * Set rule instance
     *
     * @param Peppermint_Experiences_Model_ExperiencesRules
     * @return $this
     */
    public function setRule(Peppermint_Experiences_Model_ExperiencesRules $rule)
    {
        $this->_rule = $rule;

        return $this;
    }

    /**
     * Load primary coupon for specified rule
     *
     * @param Peppermint_Experiences_Model_ExperiencesRules|int $rule
     *
     * @return $this
     */
    public function loadPrimaryByRule($rule)
    {
        $this->getResource()
            ->loadPrimaryByRule($this, $rule);

        return $this;
    }

    /**
     * Load Experiences Rule by coupon code
     *
     * @param string $couponCode
     *
     * @return $this
     */
    public function loadByCode($couponCode)
    {
        $this->load($couponCode, 'code');
        return $this;
    }
}
