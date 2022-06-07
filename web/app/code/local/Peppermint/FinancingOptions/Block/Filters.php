<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Sykander Gul <Sykander.Gul@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd, (http://rockar.com)
 */

class Peppermint_FinancingOptions_Block_Filters extends Rockar2_FinancingOptions_Block_Filters
{
    /**
     * Check to see if cms changes have been made to the collection
     * null if changes not made yet
     * @var Rockar_FinancingOptions_Model_Resource_Group_Collection|null
     */
    protected $_optionsGroupCollectionWithCmsChanges = null;

    /**
     * List of fields to convert to cms blocks for finance options
     */
    protected $_fieldsToCmsConvert = [
        'header',
        'footer',
        'video'
    ];

    /**
     * Get finance groups data
     *
     * @return Rockar_FinancingOptions_Model_Resource_Group_Collection
     */
    public function getFinanceGroups()
    {
        if (!$this->_optionsGroupCollectionWithCmsChanges) {
            $collection = parent::getFinanceGroups();

            $this->_optionsGroupCollectionWithCmsChanges = $this->_convertFieldsToCmsBlocks($collection);
        }

        return $this->_optionsGroupCollectionWithCmsChanges;
    }

    /**
     * Convert Fields To CMS Blocks
     * ============================
     * Converts array fields listed in _fieldsToCmsConvert in the array of objects
     * to cms blocks
     *
     * @param Rockar_FinancingOptions_Model_Resource_Group_Collection $collection
     *
     * @return Rockar_FinancingOptions_Model_Resource_Group_Collection
     */
    protected function _convertFieldsToCmsBlocks(Rockar_FinancingOptions_Model_Resource_Group_Collection $collection)
    {
        $processor = Mage::helper('cms')->getBlockTemplateProcessor();

        foreach ($collection as $option) {
            foreach ($this->_fieldsToCmsConvert as $field) {
                if ($value = $option->getData($field)) {
                    $option->setData($field, $processor->filter($value));
                }
            }
        }

        return $collection;
    }
}
