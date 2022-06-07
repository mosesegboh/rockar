<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DISPLAY_OPTION_ICON = 'icon';
    const DISPLAY_OPTION_ICON_TEXT = 'icon_text';
    const DISPLAY_OPTION_TEXT = 'text';

    private $_rules = [];

    /**
     * getIconsBaseDir
     *
     * @return string
     */
    public function getIconsBaseDir()
    {
        return Mage::getBaseDir('media') . DS;
    }

    /**
     * getIconsDir
     *
     * @return string
     */
    public function getIconsDir()
    {
        return 'offer_tags' . DS;
    }

    /**
     * toOptionArray
     *
     * @return array
     */
    public function toOptionArray($input)
    {
        $output = [];

        foreach ($input as $code => $value) {
            $output[] = [
                'value' => $code,
                'label' => $value
            ];
        }

        return $output;
    }

    /**
     * Get list of the offer tags in the [id => name] or [id => label] form
     *
     * @param bool $addEmptyValue
     * @param bool $useLabel
     * @return array
     */
    public function getOfferTagsArray($addEmptyValue = false, $useLabel = false)
    {
        $offerTags = Mage::getModel('peppermint_offertags/offerTags')->getCollection()
            ->addFieldToSelect(['offertag_id', 'name', 'label']);

        if ($addEmptyValue) {
            $offerTagsArray = ['' => $this->__('Please select Offer Tag Item...')];
        }

        foreach ($offerTags as $item) {
            $offerTagsArray[$item->getId()] = $useLabel ? $item->getLabel() : $item->getName();
        }

        return $offerTagsArray ?? [];
    }

    /**
     * Get offer tag based on product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array|null
     */
    public function getOfferTagsByProduct($product)
    {
        $currentPayment = Mage::helper('financing_options')->getActivePayment();
        $currentPaymentId = $currentPayment['group_id'] ?? null;

        foreach ($this->_getRules() as $rule) {
            /* @var $rule Peppermint_OfferTags_Model_OfferTagRules */
            if ($rule->getConditions()->validate($product)) {
                $offerTagId = $rule->getOfferTagId();
                $offerTag = Mage::getModel('peppermint_offertags/offerTags')->load($offerTagId);

                if ($offerTag) {
                    if (!in_array($offerTag->getActionType(),
                        [
                            Peppermint_OfferTags_Helper_Data::DISPLAY_OPTION_ICON,
                            Peppermint_OfferTags_Helper_Data::DISPLAY_OPTION_ICON_TEXT
                        ]
                    )) {
                        $offerTag->unsetData('icon');
                    }

                    if ($offerTag->getIcon()) {
                        $offerTag->setIcon(Mage::getBaseUrl('media') . $offerTag->getIcon());
                    }

                    $offerTagForFinanceGroup = [];
                    $financeGroupIds = $rule->getFinanceGroupIds();

                    if ($currentPaymentId && in_array($currentPaymentId, $financeGroupIds)) {
                        $offerTagForFinanceGroup[$currentPaymentId] = $offerTag;

                        return $offerTagForFinanceGroup;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Loads offertag rules collection once
     *
     * @throws Mage_Core_Model_Store_Exception
     * @return Peppermint_OfferTags_Model_Resource_OfferTagRules_Collection
     */
    protected function _getRules()
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $key = $websiteId . '_' . $customerGroupId;

        if (!isset($this->_rules[$key])) {
            $this->_rules[$key] = Mage::getResourceModel('peppermint_offertags/offerTagRules_collection')
                ->setValidationFilter($websiteId, $customerGroupId)
                ->load();
        }

        return $this->_rules[$key];
    }

    /**
     * Add offer tags attribute to product collection
     *
     * @param $collection
     * @param $financeGroupId
     * @return void
     */
    public function appendOfferTagsToProductCollection($collection, $financeGroupId)
    {
        $_collection = $this->cloneCollection($collection);
        $offerTags = [];
        $allHelper = Mage::helper('rockar_all');

        foreach ($_collection->getItems() as $product) {
            $offerTags[$product->getId()] = $product->getOfferTags() ?? null;
        }

        unset($_collection);

        $offerTags = array_filter($offerTags);
        Mage::unregister('offer_tags_products');
        Mage::register('offer_tags_products', $offerTags);

        // Add into select offertags
        if (!empty($offerTags)) {
            $offerTagsStatement = '(CASE ';
            $offerTagIdsStatement = '(CASE ';

            foreach ($offerTags as $entityId => $offerTagByFinanceProduct) {
                $offerTagId = isset($offerTagByFinanceProduct[$financeGroupId])
                    ? $offerTagByFinanceProduct[$financeGroupId]->getId()
                    : 'NULL';

                $offerTagData = $offerTagId
                    ? $allHelper->jsonEncode($offerTagByFinanceProduct[$financeGroupId])
                    : 'NULL';
                $offerTagsStatement .= " WHEN {{entity_id}} = {$entityId} THEN '{$offerTagData}'";
                $offerTagIdsStatement .= " WHEN {{entity_id}} = {$entityId} THEN '{$offerTagId}'";
            }

            $offerTagsStatement .= ' END)';
            $offerTagIdsStatement .= ' END)';
            $collection->addExpressionAttributeToSelect('offer_tag', $offerTagsStatement, ['entity_id']);
            $collection->addExpressionAttributeToSelect('offer_tag_id', $offerTagIdsStatement, ['entity_id']);
        }
    }

    /**
     * Clone collection
     *
     * @param $collection
     * @return mixed
     */
    public function cloneCollection($collection)
    {
        $newCollection = new $collection;

        $selectParts = [
            Varien_Db_Select::DISTINCT,
            Varien_Db_Select::COLUMNS,
            Varien_Db_Select::UNION,
            Varien_Db_Select::FROM,
            Varien_Db_Select::WHERE,
            Varien_Db_Select::GROUP,
            Varien_Db_Select::HAVING,
            Varien_Db_Select::ORDER,
            Varien_Db_Select::LIMIT_COUNT,
            Varien_Db_Select::LIMIT_OFFSET,
            Varien_Db_Select::FOR_UPDATE,
        ];

        $originalSelect = $collection->getSelect();
        $newSelect = $newCollection->getSelect();

        foreach ($selectParts as $part) {
            $newSelect->setPart(
                $part,
                $originalSelect->getPart($part)
            );
        }

        return $newCollection;
    }
}
