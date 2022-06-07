<?php
/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedProductGrid
 * @author       Krists Dadzitis <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_ExtendedProductGrid_Model_Adminhtml_System_Config_Source_Categories
{
    /**
     * Get product categories as array
     *
     * @return array
     */
    public function toArray()
    {
        $categoriesCollection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc');

        $categories = [];

        foreach ($categoriesCollection as $category) {
            if ($category->getName() && !$category->hasChildren()
                && $category->getLevel() == 2 && $category->getParentId() > 2) {
                $categories[$category->getId()] = $this->buildCategoryName($category);
            }
        }

        return $categories;
    }

    /**
     * Get category name strings from concatenated ids
     *
     * @param string $value
     * @return string
     */
    public function getCategoryNameFromIds($value)
    {
        if (!$value) {
            return '';
        }

        $names = [];
        $ids = explode(',', $value);

        foreach ($ids as $id) {
            $category = Mage::getModel('catalog/category')->load($id);

            if (!$category) {
                continue;
            }

            $names[] = $this->buildCategoryName($category);
        }

        return count($names) ? implode('<br/>', $names) : '';
    }

    /**
     * Create a category name string
     *
     * @param $category
     * @return string
     */
    protected function buildCategoryName($category)
    {
        if ($category && $category->getParentCategory()) {
            return strtok($category->getParentCategory()->getName(), ' ') . ' ' . $category->getName();
        }

        return '';
    }
}