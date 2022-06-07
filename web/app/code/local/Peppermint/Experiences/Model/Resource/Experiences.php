<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Model_Resource_Experiences extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table
     */
    protected function _construct()
    {
        $this->_init('peppermint_experiences/experiences', 'experience_id');
    }
}
