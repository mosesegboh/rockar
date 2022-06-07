<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Block_Adminhtml_Customer_Widget_Renderer_Dob extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Convert date of birth
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $dob = $row->getData('dob');

        if (is_null($dob)) {
            return '';
        }

        return Mage::getModel('core/date')->date('d M ****', $dob);
    }
}
