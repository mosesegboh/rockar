<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Info
 */
class Peppermint_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Info extends
    Rockar_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Info
{
    const TYPE_FIELD_MAX_LENGTH = 10;

    /**
     * Overwrite to change max length and remove required from footer
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $result = parent::_prepareForm();
        $form = $result->getForm();

        $form->getElement('type')
            ->setData('maxlength', self::TYPE_FIELD_MAX_LENGTH)
            ->setData('after_element_html', '<p class="nm"><small>' . $this->_helper->__('E.G.: pcp [Min length: 2, Max length: %s]',
                        self::TYPE_FIELD_MAX_LENGTH) . '</small></p>')
            ->setData('class', sprintf(
                    'validate-alphanum validate-length minimum-length-2 maximum-length-%s', self::TYPE_FIELD_MAX_LENGTH
                )
            );

        $form->getElement('footer')->setData('required', false);

        return $result;
    }
}
