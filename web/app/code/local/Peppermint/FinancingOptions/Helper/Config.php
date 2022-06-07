<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Lucaci Stefan <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Helper_Config extends Rockar_FinancingOptions_Helper_Config
{
    const XML_PATH_FINANCING_OPTIONS_CONFIG_BALLOON_STEPS = 'finance_overlay/balloon/slider_steps';
    const XML_PATH_FINANCING_OPTIONS_CONFIG_BALLOON_DEFAULT = 'finance_overlay/balloon/slider_default';
    const XML_PATH_FINANCING_OPTIONS_CONFIG_MAPPING_OPTION = 'finance_overlay/new_cars_api/mapping_option';

    /**
     * @var []
     */
    protected $_balloonSliderSteps = [];

    /**
     * @var Rockar_All_Helper_Data object
     */
    protected $_rockarHelper;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_rockarHelper = Mage::helper('rockar_all');
    }

   /**
     * Get finance vehicle condition mapping option
     *
     * @return string
     */
    public function getMappingByVehicleConditionConfig()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_FINANCING_OPTIONS_CONFIG_MAPPING_OPTION);
    }
    /**
     * Get balloon slider steps backend config string
     *
     * @return string
     */
    public function getBalloonStepsConfig()
    {
        return Mage::getStoreConfig(self::XML_PATH_FINANCING_OPTIONS_CONFIG_BALLOON_STEPS);
    }

    /**
     * Get balloon slider default step backend config string
     *
     * @param object $group
     * @return integer
     */
    public function getBalloonDefaultConfig($group = null)
    {
        $groupDefaultStep = '';
        if ($group && $group->getId()) {
            $groupDefaultStep = $group->getData('balloon_default_step');
        }

        return (int) ($groupDefaultStep != '' && $groupDefaultStep != null
            ? $groupDefaultStep
            : Mage::getStoreConfig(self::XML_PATH_FINANCING_OPTIONS_CONFIG_BALLOON_DEFAULT));
    }

    /**
     * Get all slider default states
     *
     * @param boolean $json
     * @return array|string
     */
    public function getAllSliderDefaultState($json = true)
    {
        $parentDefault = parent::getAllSliderDefaultState(false);
        foreach (Mage::helper('financing_options')->getOptionsGroups() as $group) {
            if ($balloonDefaultConfig = $this->getBalloonDefaultConfig($group)) {
                $parentDefault[$group->getId()]['balloonPercentage'] = $balloonDefaultConfig;
            }
        }

        return $json ? $this->_rockarHelper->jsonEncode($parentDefault) : $parentDefault;
    }

    /**
     * Get all slider steps
     *
     * @return string
     */
    public function getAllSliderSteps()
    {
        $parentSteps = $this->_rockarHelper->jsonDecode(parent::getAllSliderSteps());
        foreach (Mage::helper('financing_options')->getOptionsGroups() as $group) {
            $parentSteps[$group->getId()]['balloonPercentage'] = $this->_getBalloonSliderSteps($group);
        }

        return $this->_rockarHelper->jsonEncode($parentSteps);
    }

    /**
     * Return Finance Overlay Term steps
     *
     * @param Rockar_FinancingOptions_Model_Group $group
     * @return array
     */
    protected function _getBalloonSliderSteps($group)
    {
        $groupId = $group->getId();
        if (!isset($this->_balloonSliderSteps[$groupId])) {
            $balloonSteps = $group->getData('balloon_slider_steps') ?: $this->getBalloonStepsConfig();

            $stepString = str_replace(' ', '', $balloonSteps);
            $returnArray = [];
            if ($stepString) {
                $stepArray = explode(',', $stepString);
                foreach ($stepArray as $key => $steps) {
                    $returnArray[$key]['id'] = (int) $steps;
                }
            }
            $this->_balloonSliderSteps[$groupId] = $returnArray;
        }

        return $this->_balloonSliderSteps[$groupId];
    }
}
