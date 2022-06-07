<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_ExperiencesRules extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->_blockGroup = 'peppermint_experiences';
        $this->_controller = 'adminhtml_experiencesRules';
        $this->_headerText = $this->__('Manage Experience Rules');

        parent::__construct();
    }
}
