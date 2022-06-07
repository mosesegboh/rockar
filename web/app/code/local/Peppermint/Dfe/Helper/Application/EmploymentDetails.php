<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_EmploymentDetails extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for employment.
     *
     * @param  integer $orderId
     * @return object
     */
    public function setEmploymentDetailsData($orderId)
    {
        $data = $this->_prepareData($orderId);

        return (new Peppermint_Dfe_Soap_Application_EmploymentDetails(0, 10))->setEmploymentStatusCode($data['employment_status'] ?? '')
            ->setPresentEmployerName($data['current_employer'] ?? '')
            ->setIndustryTypeCode($data['employment_industry'] ?? '')
            ->setTimeAtPresentEmployerInYears($data['duration_year_current_employer'] ?? '')
            ->setTimeAtPresentEmployerInMonths($data['duration_month_current_employer'] ?? '')
            ->setPreviousEmployerName($data['previous_employer'] ?? '')
            ->setTimeAtPreviousEmployerInYears($data['duration_year_previous_employer'] ?? '')
            ->setTimeAtPreviousEmployerInMonths($data['duration_month_previous_employer'] ?? '')
            ->setOccupation($data['occupation'] ?? '')
            ->setPresentEmployerPhoneNumber($data['employers_phone'] ?? '');
    }

    /**
     * Prepare collection data by orderID.
     *
     * @param  integer $orderId
     * @return []
     */
    protected function _prepareData($orderId)
    {
        $helper = Mage::helper('peppermint_dfe');
        $collectionData = Mage::getModel('rockar_checkout/order_additional_employment')->load($orderId, 'order_id')
            ->getData();

        $durationAtCurentEmployer = $collectionData['duration_at_current_employer'] ?? 0;

        if ((int) ($durationAtCurentEmployer) > 0) {
            $dataYearMonth = $helper->transformToYearMonth($durationAtCurentEmployer);
            $collectionData['duration_year_current_employer'] = $dataYearMonth['years'];
            $collectionData['duration_month_current_employer'] = $dataYearMonth['months'];
        }

        $durationAtPreviousEmployer = $collectionData['duration_at_previous_employer'] ?? 0;

        if ((int) ($durationAtPreviousEmployer) > 0) {
            $dataYearMonth = $helper->transformToYearMonth($durationAtPreviousEmployer);
            $collectionData['duration_year_previous_employer'] = $dataYearMonth['years'];
            $collectionData['duration_month_previous_employer'] = $dataYearMonth['months'];
        }

        return $collectionData;
    }
}
