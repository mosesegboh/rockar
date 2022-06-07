<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_ExperiencesRules_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('experiencesRules_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Experience Rules'));
    }
}
