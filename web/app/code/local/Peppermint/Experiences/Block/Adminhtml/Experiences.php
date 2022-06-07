<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_Experiences extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    const EXPERIENCES_BLOCK_GROUP = 'peppermint_experiences';

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->_blockGroup = self::EXPERIENCES_BLOCK_GROUP;
        $this->_controller = 'adminhtml_experiences';
        $this->_headerText = $this->__('Manage Experiences');

        parent::__construct();
    }
}