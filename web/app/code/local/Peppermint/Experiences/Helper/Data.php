<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_rules = [];

    /**
     * getImagesBaseDir
     *
     * @return string
     */
    public function getImagesBaseDir()
    {
        return Mage::getBaseDir('media') . DS;
    }

    /**
     * getImagesDir
     *
     * @return string
     */
    public function getImagesDir()
    {
        return 'experiences' . DS;
    }

    /**
     * toOptionArray
     *
     * @return array
     */
    public function toOptionArray($input)
    {
        $output = [];

        foreach ($input as $code => $value) {
            $output[] = [
                'value' => $code,
                'label' => $value
            ];
        }

        return $output;
    }

    /**
     * Get list of the experiences in the [id => name] form
     *
     * @return array
     */
    public function getExperiencesArray()
    {
        $experiences = Mage::getModel('peppermint_experiences/experiences')->getCollection()
            ->addFieldToSelect(['experience_id', 'name']);

        $experiencesArray = ['' => $this->__('Please select Experience Item...')];

        foreach ($experiences as $item) {
            $experiencesArray[$item->getId()] = $item->getName();
        }

        return $experiencesArray;
    }

    /**
     * Get experience data by product
     *
     * @param $product
     * @return Mage_Core_Model_Abstract|null
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getExperienceDataByProduct($product)
    {
        foreach ($this->_getRules() as $rule) {
            /* @var $rule Peppermint_Experiences_Model_ExperiencesRules */
            if ($rule->getConditions()->validate($product)) {
                $experienceId = $rule->getExperienceId();
                $experience = Mage::getModel('peppermint_experiences/experiences')->load($experienceId);

                if ($experience) {
                    $experience->setImage(Mage::getBaseUrl('media') . $experience->getImage());

                    return $experience;
                }
            }
        }

        return null;
    }

    /**
     * Get Rules
     *
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _getRules()
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $key = $websiteId . '_' . $customerGroupId;

        if (!isset($this->_rules[$key])) {
            $this->_rules[$key] = Mage::getResourceModel('peppermint_experiences/experiencesRules_collection')
                ->setValidationFilter($websiteId, $customerGroupId)
                ->load();
        }

        return $this->_rules[$key];
    }
}
