<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Sales_Items_Column_Name extends Rockar_Sales_Block_Adminhtml_Sales_Items_Column_Name
{
    /**
     * Get additional attributes like fuel type and transmission
     *
     * @param $item
     * @return array[]
     */
    public function getAdditionalAttributes($item)
    {
        $result = [];
        $additionalAttributes = unserialize($item->getAdditionalAttributes(), ['allowed_classes' => false]);

        if (!empty($additionalAttributes)) {
            foreach ($additionalAttributes as $attributeData) {
                $result[] = [
                    'label' => $this->__($attributeData['label']),
                    'value' => $this->__($attributeData['value']),
                    'custom_view' => 'default'
                ];
            }
        }

        return $result;
    }

    /**
     * Rewrite prent function to show accessory codes correctly
     *
     * {@inheritDoc}
     */
    public function getOptions()
    {
        $configurationData = $this->getConfiguration();

        $result = [];

        foreach ($configurationData as $items) {
            if (isset($items['items'])) {
                $groupTitle = $items['group'];
                $optionGroup = [];
                $optionPrice = 0;

                foreach ($items['items'] as $title => $option) {
                    $optionPrice += (float) $option['price'];
                    $optionCode = isset($option['option_code'])
                        ? ($option['option_code'] . '-' . $option['id'])
                        : $option['id'];

                    if (isset($option['id'])) {
                        $optionData = sprintf(
                            '%s - %s [ %s ]',
                            $option['label'],
                            $this->_formatPrice($option['price']),
                            $optionCode
                        );
                    } else {
                        $optionData = sprintf('%s - %s', $option['label'], $this->_formatPrice($option['price']));
                    }

                    $optionGroup[] = $optionData;
                }

                $result[] = [
                    'label' => sprintf('%s - %s', $groupTitle, $this->_formatPrice($optionPrice)),
                    'value' => implode('<br>', $optionGroup),
                    'custom_view' => 'default'
                ];
            }
        }

        return $result;
    }

    /**
     * Get order item name. It is overwritten with product's value and we need
     * to fallback to the original item value if product was deleted
     *
     * @param $item
     * @return mixed
     */
    public function getItemName($item)
    {
        $name = $item->getName();

        if (empty($name)) {
            $name = $item->getOrigData('name');
        }

        return $name;
    }
}