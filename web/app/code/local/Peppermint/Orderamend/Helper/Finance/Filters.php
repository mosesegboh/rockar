<?php
/**
 * @category Peppermint
 * @package Peppermint_Orderamend
 * @author Krisjanis Smits <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Orderamend_Helper_Finance_Filters extends Rockar_Orderamend_Helper_Finance_Filters
{
    /**
     * Get all slider default states
     *
     * @param bool $json
     *
     * @return array|string
     */
    public function getAllSliderDefaultState($json = true)
    {
        $groups = $this->getOptionsGroups();

        $default = [];

        $config = Mage::helper('financing_options/config');
        foreach ($groups as $group) {
            $default[$group->getId()] = [
                'mileage' => (int) $config->getMileageDefaultConfig($group),
                'deposit' => (int) $config->getDepositDefaultConfig($group),
                'term' => (int) $config->getTermDefaultConfig($group),
                'monthlypay' => (int) $config->getMonthlyPayDefaultConfig($group),
                'payinfull' => $config->getPayInFullDefaultConfig($group),
                'depositMultiple' => (int) $config->getDepositMultipleDefaultConfig($group),
                'balloonPercentage' => (int) $config->getBalloonDefaultConfig($group),
                'maintenance' => 0,
                'financeGroupType' => 'personal'
            ];
        }

        return $json ? Mage::helper('rockar_all')->jsonEncode($default) : $default;
    }

    /**
     * Get all slider steps
     *
     * @param bool $json
     *
     * @return string | array
     *
     * @throws Mage_Core_Exception
     */
    public function getAllSliderSteps($json = true)
    {
        $groups = $this->getOptionsGroups();

        $steps = [];

        foreach ($groups as $group) {
            $steps[$group->getId()] = [
                'mileage' => $this->_getSliderSteps($group, 'mileage'),
                'deposit' => $this->_getSliderSteps($group, 'deposit'),
                'depositMultiple' => $this->_getSliderSteps($group, 'deposit_multiple'),
                'term' => $this->_getSliderSteps($group, 'term'),
                'monthlypay' => $this->_getSliderSteps($group, 'monthlypay'),
                'payinfull' => $this->_getPayInFullSliderSteps($group),
                'balloonPercentage' => $this->_getSliderSteps($group, 'balloon')
            ];
        }

        return $json ? Mage::helper('rockar_all')->jsonEncode($steps) : $steps;
    }

    /**
     * Return Finance Sliders Steps
     *
     * @param Rockar_FinancingOptions_Model_Group $group
     *
     * @param string $entity
     *
     * @return array
     *
     * @throws Mage_Core_Exception
     */
    protected function _getSliderSteps($group, $entity)
    {
        $config = Mage::helper('financing_options/config');

        $entityMapping = [
            'mileage' => [
                'finance_group_field' => 'mileage_slider_steps',
                'config_method' => 'getMileageStepsConfig'
            ],
            'deposit' => [
                'finance_group_field' => 'deposit_slider_steps',
                'config_method' => 'getDepositStepsConfig'
            ],
            'deposit_multiple' => [
                'finance_group_field' => 'deposit_multiple_slider_steps',
                'config_method' => 'getDepositMultipleStepsConfig'
            ],
            'term' => [
                'finance_group_field' => 'term_slider_steps',
                'config_method' => 'getTermStepsConfig'
            ],
            'monthlypay' => [
                'finance_group_field' => 'monthlypay_slider_steps',
                'config_method' => 'getMonthlyPayStepsConfig'
            ],
            'balloon' => [
                'finance_group_field' => 'balloon',
                'config_method' => 'getBalloonStepsConfig'
            ]
        ];

        if (!isset($entityMapping[$entity])) {
            Mage::throwException($this->__('Incorrect entity parameter passed.'));
        }

        $groupSteps = $group->getData($entityMapping[$entity]['finance_group_field']);
        $groupSteps = $groupSteps != '' ? $groupSteps : call_user_func([
            $config,
            $entityMapping[$entity]['config_method']
        ]);

        $stepString = str_replace(' ', '', $groupSteps);
        $returnArray = [];
        $returnArray[0]['id'] = 1;
        
        if ($stepString) {
            $stepArray = explode(',', $stepString);
            foreach ($stepArray as $key => $steps) {
                $returnArray[$key]['id'] = (int) $steps;
            }
        }

        return $returnArray;
    }

    /**
     * Add corresponding finance options to finance groups array
     *
     * @return array
     */
    public function getFinanceGroupsWithOptions()
    {
        $financeGroups = $this->getOptionsGroups()->toArray();
        $financeOptions = $this->getFinanceOptions();

        foreach ($financeGroups['items'] as $key => &$group) {
            $group['options'] = array_values(array_filter($financeOptions, function ($option) use ($group) {
                return $option['group_id'] === $group['group_id'];
            }));

            $group['is_instalment_payment'] = strpos(strtolower($group['group_title']), 'instalment') !== false
                ? 1
                : 0;

            if (empty($group['options'])) {
                unset($financeGroups['items'][$key]);
            }
        }

        // Reindex array, if something was removed
        $financeGroups['items'] = array_values($financeGroups['items']);

        return $financeGroups;
    }
}
