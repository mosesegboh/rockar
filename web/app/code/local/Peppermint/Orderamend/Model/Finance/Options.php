<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Ivans Zuks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

/**
 * Class Peppermint_Orderamend_Model_Finance_Options
 */
class Peppermint_Orderamend_Model_Finance_Options extends Rockar_Orderamend_Model_Finance_Options
{
    /**
     * Get protected model alias from parent
     *
     * @return string
     */
    public function getFinanceTermsModelAlias()
    {
        return $this->_financeTermsModelAlias;
    }
}
