<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar_YouDrive') . DS . 'IndexController.php';

class Peppermint_YouDrive_IndexController extends Rockar_YouDrive_IndexController
{
    /**
     * Redirect to auth service
     *
     * @return void
     */
    public function loginAction()
    {
        $session = Mage::getSingleton('customer/session');

        if (!$session->isLoggedIn()) {
            $session->setData('login_process', true);
            $this->getResponse()
                ->setRedirect(
                    Mage::helper('peppermint_gcdm/redirector')->getRedirectUrl()
                );

            $session->setData(
                Peppermint_Gcdm_Helper_Redirector::NS_BEFORE_GCDM_AUTH_URL,
                Mage::getUrl(
                    'test-drives',
                    [
                        '_query' => ['step' => Rockar_YouDrive_Helper_Data::URL_STEP_CONFIRM]
                    ]
                )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function indexAction()
    {
        /**
         * Disable it for default store
         */
        if (Mage::app()->getStore()->getCode() === 'default') {
            $this->norouteAction();

            return;
        };

        $modelIds = $this->getRequest()->getParam('modelIds');

        if ($modelIds) {
            $this->_preSelectCar($modelIds);
        }

        if ($this->getRequest()->getParam('bookingId')) {
            $this->_loadBooking($this->getRequest()->getParam('bookingId'));
        }

        $this->loadLayout();
        $this->_initLayoutMessages(['customer/session', 'core/session']);

        if ($head = $this->getLayout()->getBlock('head')) {
            if ($customTitle = $this->_getMetaTitle()) {
                $head->setTitle($customTitle);
            }

            if ($customDescription = trim($this->_getMetaDescription())) {
                $head->setDescription($customDescription);
            }

            if ($customKeywords = trim($this->_getMetaKeywords())) {
                $head->setKeywords($customKeywords);
            }
        }

        $this->renderLayout();
    }
}
