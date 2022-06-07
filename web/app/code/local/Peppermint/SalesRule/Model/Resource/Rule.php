<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Model_Resource_Rule extends Mage_SalesRule_Model_Resource_Rule
{
    /**
     * {@inheritdoc}
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Overridden to return back conditions_serialized and actions_serialized fields
     *
     * @return Mage_SalesRule_Model_Resource_Rule
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $conditionsSerialized = $object->getData('conditions_serialized');
        $actionsSerialized = $object->getData('actions_serialized');
        $result = parent::_afterSave($object);
        $object->addData(['conditions_serialized' => $conditionsSerialized, 'actions_serialized' => $actionsSerialized]);

        return $result;
    }
}
