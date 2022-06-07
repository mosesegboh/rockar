<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Block_Adminhtml_Report_RulesLog_Renderer_Serialized2Format
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    private const VALUES = ['attribute', 'operator', 'value'];

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $value = parent::_getValue($row);
        $value = unserialize($value);

        return empty($value['conditions']) ? '' : $this->format($value['conditions']);
    }

    /**
     * Format Conditions like ATTR === VALUE
     * @param $conditions
     * @return string
     */
    private function format($conditions)
    {
        $tmpResult = [];
        $result = '';

        for ($i = 0; $i < count($conditions); $i++) {
            foreach ($conditions[$i] as $condIdx => $condVal) {
                if (in_array($condIdx, self::VALUES)) {
                    $tmpResult[$i][] = $condVal;
                }
            }
        }

        foreach ($tmpResult as $value) {
            $result .= implode(' ', $value);
            $result .= ', ';
        }

        return rtrim($result, ', ');
    }
}
