<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Mihai Chezan <mihai.chezan@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType extends Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType
{
    /**
     * Instalment type.
     */
    const TYPE_INSTALMENT = 4;

    /**
     * Options getter.
     *
     * @param boolean $addEmpty
     *
     * @return array
     */
    public function toOptionArray($addEmpty = false)
    {
        $return = parent::toOptionArray($addEmpty);
        foreach ($return as $value) {
            if ($value['value'] === self::TYPE_PAY_IN_FULL) {
                $value['label'] = 'Cash';
            }
            if ($value['value'] === self::TYPE_LEASING) {
                $value['label'] = 'Select';
            }
            $result[] = $value;
        }
        $result[] = [
            'value' => self::TYPE_INSTALMENT,
            'label' => $this->_helper->__('Instalment')
        ];

        return $result;
    }

    /**
     * Returns financing options names list as array, like financing_option_id => financing_option_name.
     *
     * @return array
     */
    public function toArray()
    {
        $return = parent::toArray();

        return [
            self::TYPE_PAY_IN_FULL => $this->_helper->__('Cash'),
            self::TYPE_LEASING => $this->_helper->__('Select'),
            self::TYPE_INSTALMENT => $this->_helper->__('Instalment')
        ] + $return;
    }
}
