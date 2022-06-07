<?php
/**
 * @category  Peppermint
 * @package   Peppermint_All
 * @author    Sergejs Plisko <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_All_Helper_Data extends Rockar_All_Helper_Data
{
    /** @var string Tags allowed after sanitization. All attributes will be removed */
    protected static $allowedTags = '<b><i><em><strong><p><div><ol><ul><li><br>';

    /**
     * Get model attribute
     *
     * @throws Mage_Core_Model_Store_Exception
     */
    public function __construct()
    {
        $model = 'bmw_model_carousel';
        $brand = str_replace('_store_view', '', Mage::app()->getStore()->getCode());

        switch ($brand) {
            case 'admin':
                $model = [
                    'bmw_model_carousel',
                    'min_model_carousel',
                    'mot_model_carousel'
                ];
                break;
            case 'mini':
                $model = 'min_model_carousel';
                break;
            case 'motorrad':
                $model = 'mot_model_carousel';
                break;
        }

        $this->_modelAttribute = $model;
    }

    /**
     * Get model attribute
     *
     * @return string
     */
    public function getModelAttribute()
    {
        if (is_array($this->_modelAttribute)) {
            return $this->_modelAttribute[0];
        }

        return $this->_modelAttribute;
    }

    /**
     * Get all car models attribute names
     *
     * @return array
     */
    public function getAllModelAttributes()
    {
        if (is_array($this->_modelAttribute)) {
            return $this->_modelAttribute;
        }

        return [$this->_modelAttribute];
    }

    /**
     * Returns locale based date format according to php standards
     *
     * @return string
     */
    public function getLocaleDateGlobalFormat()
    {
        return str_replace(
            '%',
            '',
            Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        );
    }

    /**
     * Check if there's already a cron job scheduled for given code and minute
     *
     * @param string $jobCode
     * @param int $minute
     * @return bool
     */
    public function isCronJobAlreadyScheduled(string $jobCode, int $minute): bool
    {
        $collection = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('status', Aoe_Scheduler_Model_Schedule::STATUS_PENDING)
            ->addFieldToFilter('job_code', $jobCode);

        foreach ($collection as $schedule) {
            // Check if scheduled event within $minute pass, otherwise it is too far ahead
            if (strtotime($schedule->getScheduledAt()) < strtotime("+{$minute} minute")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize object's data
     *
     * @param Varien_Object $object
     * @param array|string $data
     * @param bool $partial
     * @return $this
     */
    public function sanitizeData(Varien_Object $object, $data, $partial = false)
    {
        if ($object instanceof Varien_Object) {
            if (is_string($data)) {
                $data = [$data];
            }

            if (is_array($data)) {
                foreach ($data as $key) {
                    $object->setData($key, $this->_sanitizeHtml($object->getData($key), $partial));
                }
            }
        }

        return $this;
    }

    /**
     * Strip tags. If some tags are allowed, remove all their attributes
     *
     * @param $html
     * @param bool $partial
     * @return string
     */
    protected function _sanitizeHtml($html, $partial = false)
    {
        if (!is_string($html)) {
            return $html;
        }

        if ($partial) {
            $result = strip_tags($html, static::$allowedTags);
            $result = preg_replace('/(<\/?\w*)([^>]*)/', '$1', $result);

            return $result;
        }

        return strip_tags(html_entity_decode($html));
    }

    /**
     * Get the store code url param first and if not present,
     * check the cookie
     *
     * @return mixed|string
     */
    public function getInStoreCode()
    {
        $storeCodeParam = Mage::app()->getRequest()->getQuery(self::IN_STORE_COOKIE_NAME, '');
        $storeCode = filter_var($storeCodeParam, FILTER_SANITIZE_STRING);

        return $storeCode
            ?: Mage::getModel('core/cookie')->get(self::IN_STORE_COOKIE_NAME)
            ?: '';
    }

    /**
     * Get the dealer id url param first and if not present,
     * check the cookie
     *
     * @return string
     */
    public function getDealerId(): string
    {
        $dealerIdParam = Mage::app()->getRequest()->getQuery(Peppermint_Customer_Model_Observer::DEALER_ID_COOKIE_NAME, '');
        $dealerId = filter_var($dealerIdParam, FILTER_SANITIZE_STRING);

        return $dealerId
            ?: Mage::getModel('core/cookie')->get(Peppermint_Customer_Model_Observer::DEALER_ID_COOKIE_NAME)
            ?: '';
    }
}
