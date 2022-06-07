<?php

/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Catalog_Model_Product extends Rockar_Catalog_Model_Product
{

    /**
     * Retrive media gallery images
     *
     * @return Varien_Data_Collection
     */
    public function getMediaGalleryImages()
    {
        if (!$this->hasData('media_gallery_images') && is_array($this->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            foreach ($this->getMediaGallery('images') as $image) {
                if ($image['disabled']) {
                    continue;
                }
                $image['url'] = $image['file'];
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $image['file'];
                $images->addItem(new Varien_Object($image));
            }
            $this->setData('media_gallery_images', $images);
        }

        return $this->getData('media_gallery_images');
    }

    /**
     * Rewrite of core function to also check for peppermint product import registry
     * {@inheritdoc}
     *
     * @return Peppermint_Catalog_Model_Product
     */
    public function afterCommitCallback()
    {
        Mage_Core_Model_Abstract::afterCommitCallback();

        if (!Mage::registry('pdc_import') && !Mage::registry('peppermint_import')) {
            /** @var Mage_Index_Model_Indexer $indexer */
            Mage::getSingleton('index/indexer')->processEntityAction($this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE);
        }

        return $this;
    }

    /**
     * Adds offer_tags calculated property to product data before return the data
     *
     * @param string $key
     * @param null $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        $data = parent::getData($key, $index);

        if (!empty($data) && $key === '') {
            $data['offer_tags'] = $this->getOfferTags();
        }

        if ($key === 'offer_tags') {
            $data = $this->getOfferTags();
        }

        return $data;
    }

    /**
     * OfferTag Accessor
     *
     * @return mixed|null
     */
    public function getOfferTags()
    {
        if (!$this->hasData('offer_tags')) {
            $offerTags = Mage::helper('peppermint_offertags')->getOfferTagsByProduct($this);
            $this->setOfferTags($offerTags);
        }

        return $this->_data['offer_tags'] ?? null;
    }
}
