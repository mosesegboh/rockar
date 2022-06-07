<?php

/**
 * @category     Peppermint
 * @package      Peppermint\ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Adminhtml_Extendedrules_MileageController extends Rockar_ExtendedRules_Controller_Extendedrules_Abstract
{
    protected $_ruleModel = 'peppermint_extendedrules/mileage';
    protected $_activeTab = 'mileage_section';
    protected $_gridCheckboxName = 'mileage_rule_id';
}
