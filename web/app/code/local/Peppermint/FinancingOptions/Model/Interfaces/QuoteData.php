<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Interfaces_QuoteData extends Rockar_FinancingOptions_Model_Interfaces_QuoteData
{
    /**
     * @return string
     */
    public function getWebsiteCode()
    {
        return strtoupper(Mage::app()->getWebsite()->getCode());
    }

    /**
     * Get the Balloon Percentage
     *
     * @return integer $balloonPercentage
     */
    public function getBalloonPercentage()
    {
        return (int) $this->balloonPercentage;
    }

    /**
     * Set the Balloon Percentage
     *
     * @param integer $balloonPercentage
     *
     * @return void
     */
    public function setBalloonPercentage($balloonPercentage)
    {
        $this->balloonPercentage = (int) $balloonPercentage;
    }
}
