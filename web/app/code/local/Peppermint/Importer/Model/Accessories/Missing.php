<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Accessories_Missing extends Mage_Core_Model_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'peppermint_importer_accessories_missing';

    /**
     * Init model
     */
    protected function _construct()
    {
        $this->_init('peppermint_importer/accessories_missing');
    }
}