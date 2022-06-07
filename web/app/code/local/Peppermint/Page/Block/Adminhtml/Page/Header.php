<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Page
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Page_Block_Adminhtml_Page_Header extends Mage_Adminhtml_Block_Page_Header
{
    /**
     * Peppermint_Page_Block_Adminhtml_Page_Header constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('peppermint/page/header.phtml');
    }
}
