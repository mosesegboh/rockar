<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_RulesLog extends Mage_Core_Model_Abstract
{
    /**
     * Log actions types
     */
    const ACTION_CREATE = 'Create';
    const ACTION_UPDATE = 'Update';
    const ACTION_DELETE = 'Delete';
    const ACTION_APPROVE = 'Approve';

    /**
     * Rule types
     */
    const TYPE_CATALOG_RULE = 'Catalog Price Rule';
    const TYPE_CART_RULE = 'Shopping Cart Price Rule';
    const TYPE_SHORTFALL_SUPPORT = 'Shortfall Support';
    const TYPE_TRADE_IN = 'Trade-in';

    /**
     * @var null|array
     */
    private $addressOptions;

    /**
     * @var null|Mage_SalesRule_Model_Rule_Condition_Address
     */
    private $addressModel;

    /**
     * @var null|Mage_SalesRule_Model_Rule_Condition_Product
     */
    private $salesRuleModel;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('peppermint_catalogrule/rulesLog');
    }

    /**
     * Prepare data and save
     *
     * @param object $model
     * @param string $actionType
     * @param string $ruleType
     * @throws Exception
     */
    public function processPriceRuleLogSave($model, $actionType, $ruleType)
    {
        $ruleData = $model->getData();

        if (is_array($ruleData['website_ids'])) {
            $ruleData['website_ids'] = implode(',', $ruleData['website_ids']);
        }

        if (is_array($ruleData['customer_group_ids'])) {
            $ruleData['customer_group_ids'] = implode(',', $ruleData['customer_group_ids']);
        }

        $user = Mage::getSingleton('admin/session')->getUser();

        $ruleData = $this->getOptionValues($ruleData);

        $this->addData(array_merge(
            $ruleData,
            [
                'username' => $user->getUsername(),
                'full_name' => $user->getFirstname() . ' ' . $user->getLastname(),
                'action_type' => $actionType,
                'rule_type' => $ruleType,
            ]
        ))->save();
    }

    /**
     * Get more user-friendly option values to display
     *
     * @param $ruleData
     * @return mixed
     */
    private function getOptionValues($ruleData)
    {
        foreach ($ruleData as $ruleIdx => $ruleVal) {
            if ($ruleIdx === 'conditions_serialized' || $ruleIdx === 'actions_serialized') {
                $result = unserialize($ruleVal);

                if (!isset($result['conditions'])) {
                    continue;
                }

                foreach ($result['conditions'] as $condIdx => $condVal) {
                    $modified = false;

                    switch ($condVal['type']) {
                        case 'catalogrule/rule_condition_product':
                        case 'salesrule/rule_condition_product':
                        case 'rockar_partexchange/promotions_rule_action_product':
                        case 'peppermint_catalogrule/rule_condition_dateDifference':
                            $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(
                                Mage_Catalog_Model_Product::ENTITY,
                                $condVal['attribute']
                            );

                            if ($attributeModel->usesSource() && $attributeModel->getData('source_model')) {
                                $result['conditions'][$condIdx]['value'] = $attributeModel->getSource()
                                    ->getOptionText($condVal['value']);
                            }

                            $result['conditions'][$condIdx]['attribute'] = $this->getLabel(
                                $result['conditions'][$condIdx]['attribute']
                            ) ?? $result['conditions'][$condIdx]['attribute'];
                            $modified = true;

                            break;
                        case 'salesrule/rule_condition_address':
                            if (!isset($this->addressOptions[$condVal['attribute']])) {
                                $this->getAddressOptions($condVal['attribute']);
                            }

                            if (isset($this->addressOptions[$condVal['attribute']])) {
                                foreach ($this->addressOptions[$condVal['attribute']] as $addressOptionVal) {
                                    if ($condVal['attribute'] === 'region_id') {
                                        if (!is_array($addressOptionVal['value'])) {
                                            continue;
                                        }

                                        foreach ($addressOptionVal['value'] as $regionVal) {
                                            if ($regionVal['value'] == $condVal['value']) {
                                                $result['conditions'][$condIdx]['value'] = $regionVal['label'];
                                                break;
                                            }
                                        }
                                    } elseif ($addressOptionVal['value'] === $condVal['value']) {
                                        $result['conditions'][$condIdx]['value'] = $addressOptionVal['label'];
                                        break;
                                    }
                                }
                            }

                            $result['conditions'][$condIdx]['attribute'] = $this->addressModel->getAttributeName(
                                $result['conditions'][$condIdx]['attribute']
                            );
                            $modified = true;

                            break;
                        case 'salesrule/rule_condition_product_attribute_assigned':
                            $result['conditions'][$condIdx]['attribute'] = $this->getLabel(
                                $result['conditions'][$condIdx]['attribute']
                            ) ?? $result['conditions'][$condIdx]['attribute'];
                            $modified = true;

                            break;
                        default:
                            break;
                    }

                    if ($modified) {
                        $ruleData[$ruleIdx] = serialize($result);
                    }
                }
            }
        }

        return $ruleData;
    }

    /**
     * Get address options for Shopping Cart Price Rule
     *
     * @param $attribute
     */
    private function getAddressOptions($attribute)
    {
        if (!$this->addressModel) {
            $this->addressModel = Mage::getModel('salesrule/rule_condition_address');
        }

        $this->addressModel->setAttribute($attribute);
        $this->addressModel->unsetData('value_select_options');

        if ($options = $this->addressModel->getValueSelectOptions()) {
            $this->addressOptions[$attribute] = $options;
        }
    }

    /**
     * @return Mage_SalesRule_Model_Rule_Condition_Product
     */
    private function getSalesRuleModel()
    {
        if ($this->salesRuleModel === null) {
            $this->salesRuleModel = Mage::getModel('salesrule/rule_condition_product');
        }

        return $this->salesRuleModel;
    }

    /**
     * Get Frontend label for product attributes
     *
     * @param $code
     * @return mixed|string
     */
    private function getLabel($code)
    {
        $result = '';

        foreach ($this->getSalesRuleModel()->getAttributeSelectOptions() as $option) {
            if ($option['value'] === $code) {
                $result = $option['label'];
                break;
            }
        }

        return $result;
    }
}
