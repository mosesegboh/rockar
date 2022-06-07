<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Helper_ExperiencePopup extends Mage_Core_Helper_Abstract
{
    /**
     * XML paths
     */
    const XML_PATH_EXPERIENCES_POPUP_DELAY = 'experiences/popup_management/popup_delay';

    /**
     * Session keys
     */
    const SESSION_PRODUCT_EXPERIENCES_POPUP_KEY = 'product_experiences_popup';

    /**
     * Get popup delay in seconds
     *
     * @return int
     */
    public function getPopupDelay()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_EXPERIENCES_POPUP_DELAY);
    }

    /**
     * Get experience to show in popup
     *
     * @param $productId
     * @return mixed
     */
    public function getExperiencePopupData($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        $experience = Mage::helper('peppermint_experiences')->getExperienceDataByProduct($product);
        $result = $experience ? $experience->getData() : [];
        $result['show_popup'] = $result && $this->showPopup($productId, $result['experience_id']);
        $result['popup_delay'] = $this->getPopupDelay();
        $result['product_id'] = $productId;
        $result['save_url'] = Mage::getUrl('peppermint_experiences/popup/saveAppliedPopupExperienceToSession');

        if (isset($result['textblock'])) {
            $processor = Mage::helper('cms')->getBlockTemplateProcessor();
            $processor->setVariables([
                'product_title' => $product->getTitle(),
                'product_short_title' => $product->getShortTitle(),
                'product_subtitle' => $product->getSubtitle(),
                'product_short_subtitle' => $product->getShortSubtitle()
            ]);
            $result['textblock'] = $processor->filter($result['textblock']);
        }

        return Mage::helper('rockar_all')->jsonEncode($result);
    }

    /**
     * Check to show experience popup
     *
     * @param $productId
     * @param $experienceId
     * @return bool
     */
    public function showPopup($productId, $experienceId)
    {
        $appliedExperiences = $this->getAppliedPopupExperiences();
        $result = true;

        if (isset($appliedExperiences[$productId]) && $appliedExperiences[$productId] == $experienceId) {
            $result = false;
        }

        return $result;
    }

    /**
     * Save applied experience to session
     *
     * @param $productId
     * @param $experienceId
     *
     * @return void
     */
    public function saveAppliedPopupExperience($productId, $experienceId)
    {
        $productExperiences = $this->getAppliedPopupExperiences();
        $productExperiences[$productId] = $experienceId;

        Mage::getSingleton('customer/session')->setData(
            self::SESSION_PRODUCT_EXPERIENCES_POPUP_KEY,
            $productExperiences
        );
    }

    /**
     * Get applied popup experiences from session
     *
     * @return array|mixed|null
     */
    public function getAppliedPopupExperiences()
    {
        return Mage::getSingleton('customer/session')->getData(
            self::SESSION_PRODUCT_EXPERIENCES_POPUP_KEY
        );
    }
}
