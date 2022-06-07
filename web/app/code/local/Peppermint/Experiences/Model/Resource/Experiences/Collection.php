<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Model_Resource_Experiences_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_experiences/experiences');
    }
}
