<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Block_Adminhtml_Customer_Widget_Renderer_Email extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Convert email
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $email = $row->getData('email');

        if (is_null($email)) {
            return '';
        }

        return substr_replace($email, '*****', 1, strpos($email, '@') - 1);
    }
}
