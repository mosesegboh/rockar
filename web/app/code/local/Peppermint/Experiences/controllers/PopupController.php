<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_PopupController extends Mage_Core_Controller_Front_Action
{
    /**
     * Save applied experience
     */
    public function saveAppliedPopupExperienceToSessionAction()
    {
        $data = $this->getRequest()->getPost();
        $response['success'] = false;

        if (isset($data['product_id']) && isset($data['experience_id'])) {
            Mage::helper('peppermint_experiences/experiencePopup')->saveAppliedPopupExperience(
                $data['product_id'],
                $data['experience_id']
            );
            $response['success'] = true;
        }

        return $this->_sendJsonResponse($response);
    }

    /**
     * Prepare and send data in JSON format
     *
     * @param $response
     * @return Mage_Core_Controller_Response_Http|Zend_Controller_Response_Abstract
     */
    protected function _sendJsonResponse($response)
    {
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('rockar_all')->jsonEncode($response));
    }
}
