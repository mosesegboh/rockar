<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Helper_Images extends Rockar2_Catalog_Helper_Images
{
    const MATRIX_PLACEHOLDER = 'rockar_catalog/placeholder/placeholder';
    const EXTERIOR_IMAGES_INDEXES_CONFIG_PATH = 'rockar_catalog/general/exterior_images_indexes';
    const IMAGE_TYPE_COSY_VIEW = 'cosy_view';
    const IMAGE_TYPE_DEFAULT_EXTERIOR_URL = 'exterior_url';
    const IMAGE_TYPE_INTERIOR_360 = 'interior_360';
    const MEDIA_IMAGE_TYPE_COSY_VIEW = 3;
    const MEDIA_IMAGE_TYPE_INTERIOR_360 = 4;
    const MEDIA_IMAGE_TYPE_DEFAULT_EXTERIOR_URL = 5;
    const EXTRA_OPTIONS_LABELS = [
        'Options',
        'Extra Options'
    ];


    /**
     * Get product media images
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $placeholder
     * @return array
     */
    public function getProductMedia($product, $placeholder = true)
    {
        $result = [
            self::IMAGE_TYPE_INTERIOR => [],
            self::IMAGE_TYPE_EXTERIOR => [],
            self::IMAGE_TYPE_COSY_VIEW => [],
            self::IMAGE_TYPE_INTERIOR_360 => [],
            self::IMAGE_TYPE_DEFAULT_EXTERIOR_URL => []
        ];

        $images = $product->getMediaGalleryImages();

        if (!$images) {
            // Load media_gallery from backend instance of product
            try {
                $product->getResource()
                    ->getAttribute('media_gallery')
                    ->getBackend()
                    ->afterLoad($product);
            } catch (Mage_Core_Exception $e) {
                $e->setMessage('Failed to retrieve backend instance of product', true);
                Mage::logException($e);
            }

            $images = $product->getMediaGalleryImages();
        }

        if ($images) {
            $exteriorImagesToShow = $this->getExtImageIndexesConfig();
            $extImageIndex = 0;

            foreach ($images as $image) {
                switch ($image->getImageType()) {
                    case Rockar_Catalog_Model_Catalog_Product_Attribute_Backend_Media::MEDIA_IMAGE_TYPE_INTERIOR:
                        $result[self::IMAGE_TYPE_INTERIOR][] = [
                            'image' => $image->getUrl(),
                            'image_path' => $image->getPath()
                        ];
                        break;
                    case self::MEDIA_IMAGE_TYPE_INTERIOR_360:
                        $result[self::IMAGE_TYPE_INTERIOR_360][] = [
                            'image' => $image->getUrl(),
                            'image_path' => $image->getPath()
                        ];
                        break;
                    case self::MEDIA_IMAGE_TYPE_COSY_VIEW:
                        $result[self::IMAGE_TYPE_COSY_VIEW][] = [
                            'image' => $image->getUrl(),
                            'image_path' => $image->getPath()
                        ];
                        break;
                    case self::MEDIA_IMAGE_TYPE_DEFAULT_EXTERIOR_URL:
                        $result[self::IMAGE_TYPE_DEFAULT_EXTERIOR_URL][] = [
                            'image' => $image->getUrl(),
                            'image_path' => $image->getPath()
                        ];
                        break;
                    case Rockar_Catalog_Model_Catalog_Product_Attribute_Backend_Media::MEDIA_IMAGE_TYPE_EXTERIOR:
                    default:
                        if (!$exteriorImagesToShow || in_array($extImageIndex, $exteriorImagesToShow)) {
                            $result[self::IMAGE_TYPE_EXTERIOR][] = [
                                'image' => $image->getUrl(),
                                'image_path' => $image->getPath()
                            ];
                        }

                        $extImageIndex++;
                        break;
                }
            }
        }

        if (!$placeholder) {
            return $result;
        }

        if (count($result[self::IMAGE_TYPE_INTERIOR]) == 0) {
            $result[self::IMAGE_TYPE_INTERIOR][]['image'] = $this->getPlaceholderImage('default_' . self::IMAGE_TYPE_INTERIOR);
        }

        if (count($result[self::IMAGE_TYPE_EXTERIOR]) == 0) {
            $result[self::IMAGE_TYPE_EXTERIOR][]['image'] = $this->getPlaceholderImage('default_' . self::IMAGE_TYPE_EXTERIOR);
        }

        if (count($result[self::IMAGE_TYPE_DEFAULT_EXTERIOR_URL]) == 0) {
            $result[self::IMAGE_TYPE_DEFAULT_EXTERIOR_URL][]['image'] = $this->getPlaceholderImage('default_' . self::IMAGE_TYPE_EXTERIOR);
        }

        return $result;
    }

    /**
     * Get product type first image
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $type
     * @return string
     */
    public function getFirstImage($product, $type = self::IMAGE_TYPE_EXTERIOR)
    {
        $image = $product->getData("default_{$type}");
        if ($image && $image !== 'no_selection') {
            try {
                return (string) $image;
            } catch (Exception $e) {
                return $this->getPlaceholderImage("default_{$type}");
            }
        }

        $images = $this->getProductMedia($product);

        if (!empty($images) && array_key_exists($type, $images) && !empty($images[$type])) {
            return reset($images[$type])['image'];
        }

        return $this->getPlaceholderImage("default_{$type}");
    }

    /**
     * Get small image for a product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getSmallImage($product)
    {
        return (string) $product->getData('small_image') ?: $this->getPlaceholderImage('default_' . self::IMAGE_TYPE_EXTERIOR);
    }

    /**
     * Return model matrix uploaded placeholders image path
     *
     * @param string|null $store
     *
     * @return string
     */
    public function getModelMatrixPlaceholderImage($store = null)
    {
        $imageConfig = Mage::getStoreConfig(self::MATRIX_PLACEHOLDER, $store);

        return $imageConfig
            ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/model_matrix/placeholder' . DS . $imageConfig
            : $this->getDefaultPlaceholderImagePath();
    }

    /**
     * Get default placeholder image path
     *
     * @return string
     */
    public function getDefaultPlaceholderImagePath()
    {
        return Rockar_Carfinder_Block_Layer_View::DEFAULT_FILTER_IMAGE;
    }

    /**
     * Ids of exterior images to show
     *
     * @return array|null
     */
    public function getExtImageIndexesConfig()
    {
        $config = Mage::getStoreConfig(
                self::EXTERIOR_IMAGES_INDEXES_CONFIG_PATH,
                Mage::app()->getStore()->getId()
        );

        return $config ? explode(',', $config) : null;
    }

    /**
     * Get array of extra option images
     *
     * @param array Finance quote data
     * @return array
     */
    private function getExtraOptionsImages($financeQuoteData)
    {
        if (is_null($financeQuoteData)) {
            return [];
        }

        $carData = json_decode($financeQuoteData['car_data']);
        $result = [];

        foreach ($carData as $value) {
            if (in_array($value->group, self::EXTRA_OPTIONS_LABELS)) {
                foreach ($value->items as $item) {
                    if ($item->highligted === 'Y') {
                        // Create result array structure as result from rockar_catalog/images getProductMedia()
                        $result = array_merge($result, [['image' => $item->cosy_url]]);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get all extra cosy images
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array Finance quote data
     * @return string
     */
    public function getAllCosyImagesAsJson($product, $financeQuoteData)
    {
        $images = $this->getProductMedia($product);
        $cosyViewImages = $images[Peppermint_Catalog_Helper_Images::IMAGE_TYPE_COSY_VIEW] ?? [];
        $extraOptionsImages = $this->getExtraOptionsImages($financeQuoteData);

        return Mage::helper('rockar_all')->jsonEncode(
            array_merge($cosyViewImages, $extraOptionsImages)
        );
    }
}
