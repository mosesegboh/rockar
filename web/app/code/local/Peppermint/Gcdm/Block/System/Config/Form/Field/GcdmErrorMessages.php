<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Gcdm
 * @author    Alexander Metzgen <alexander.metzgen@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Gcdm_Block_System_Config_Form_Field_GcdmErrorMessages extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $gcdmHelper = Mage::helper('peppermint_gcdm');

        $this->addColumn(
            'error_code',
            [
                'label' => $gcdmHelper->__('Error Code'),
                'style' => 'width:200px'
            ]
        );

        $this->addColumn(
            'error_message',
            [
                'label' => $gcdmHelper->__('Error Message'),
                'style' => 'width:750px'
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = $gcdmHelper->__('Add');
    }
}
