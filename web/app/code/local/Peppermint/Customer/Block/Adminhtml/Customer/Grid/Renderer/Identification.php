<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Adminhtml_Customer_Grid_Renderer_Identification extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Convert document id number
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $idNumber = $row->getData('south_african_id_number');

        if (is_null($idNumber)) {
            return '';
        }

        $optionId = $row->getData('south_african_document_type');

        $identificationTypes = Mage::helper('peppermint_customer')->getIdentificationTypes();

        return $identificationTypes[$optionId] === 'Passport Number'
            ? substr($idNumber, 0, -3) . '***'
            : substr($idNumber, 0, -7) . '*******';
    }
}
