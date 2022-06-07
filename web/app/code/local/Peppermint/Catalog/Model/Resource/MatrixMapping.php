<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Resource_MatrixMapping extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init('peppermint_catalog/matrix_mapping', 'model_matrix_carousel');
    }
}
