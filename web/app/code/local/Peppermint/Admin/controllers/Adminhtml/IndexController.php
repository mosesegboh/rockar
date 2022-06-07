<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Tiberiu Barkoczi <tiberiu.barkoczi@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'IndexController.php';

class Peppermint_Admin_Adminhtml_IndexController extends Mage_Adminhtml_IndexController
{
    /**
     * Rewrite original Magento Authentification with S-Gate OpenId Authentification.
     *
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     * @return void|Zend_Controller_Response_Abstract
     */
    public function loginAction()
    {
        /** @var Peppermint_Admin_Model_Sgate $sGate */
        $sGate = Mage::getModel('peppermint_admin/sgate');
        $adminSession = Mage::getSingleton('admin/session');

        // Redirect to dashboard if is loggedIn
        if ($adminSession->isLoggedIn()) {
            $this->_redirect('*');

            return;
        }

        // Coming back from S-Gate
        $code = $this->getRequest()->getParam('code');

        if ($code) {
            $role = $adminSession->getData(Peppermint_Admin_Model_Sgate::SESSION_KEY_ROLE);
            $accessTokenUri = $sGate->getAccessTokenUri($code, $role);

            try {
                $result = $sGate->retryAccessToken($accessTokenUri, $role);
                $email = $sGate->validateUsersEmail($result['access_token'], $role);
                // Validate Nonce
                if (!isset($result['nonce']) || !$sGate->isValidNonce($result['nonce'])) {
                    // Redirect to Front base url
                    $this->_redirectToFrontBaseUrl();
                } else {
                    $adminSession->setData(Peppermint_Admin_Model_Sgate::OIDC_TOKEN, $result['id_token']);
                    $adminSession->setData(Peppermint_Admin_Model_Sgate::ACCESS_TOKEN, $result['access_token']);
                    $adminSession->setData(Peppermint_Admin_Model_Sgate::REFRESH_TOKEN, $result['refresh_token']);
                    $this->_login($email);
                    $this->_redirect('*');
                }
            } catch (\Exception $e) {
                $this->_redirectToFrontBaseUrl();
            }

            return;
        }

        // Going to S-Gate
        $role = $this->getRequest()->getParam('role');

        if (!Mage::getModel('peppermint_admin/role')->validateRole($role)) {
            $this->_redirectToFrontBaseUrl();
        }

        // Redirect to S-Gate to be Authorized
        return Mage::app()->getFrontController()
            ->getResponse()
            ->setRedirect($sGate->getAuthorizeUri($role));
    }

    /**
     * disable original login action.
     *
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     * @return void
     */
    public function forgotpasswordAction()
    {
        $this->_redirectToFrontBaseUrl();
    }

    /**
     * disable original login action.
     *
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     * @return void
     */
    public function resetPasswordAction()
    {
        $this->_redirectToFrontBaseUrl();
    }

    /**
     * disable original login action.
     *
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     * @return void
     */
    public function resetPasswordPostAction()
    {
        $this->_redirectToFrontBaseUrl();
    }

    /**
     * redirect to frontend base url.
     *
     * @throws Mage_Core_Model_Store_Exception
     * @throws Mage_Core_Exception
     * @return void
     */
    protected function _redirectToFrontBaseUrl()
    {
        $app = Mage::app();
        $app->getFrontController()
            ->getResponse()
            ->setRedirect($app->getStore(Mage::app()->getStore())->getBaseUrl());
        $app->getResponse()
            ->sendResponse();
    }

    /**
     * Login session after use was authorized.
     *
     * @param string $email
     * @return $this
     */
    private function _login(string $email)
    {
        // supply username
        $user = Mage::getModel('admin/user')->load($email, 'email');

        if (!$user->getId()) {
            $this->_redirectToFrontBaseUrl();
        }

        $sessionAdminUrl = Mage::getSingleton('adminhtml/url');

        if ($sessionAdminUrl->useSecretKey()) {
            $sessionAdminUrl->renewSecretUrls();
        }

        Mage::getSingleton('admin/session')->setIsFirstVisit(true)
            ->setUser($user)
            ->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
        Mage::dispatchEvent('admin_session_user_login_success', ['user' => $user]);

        return $this;
    }

    /**
     * Administrator logout action
     *
     * @return $this
     * @throws Mage_Core_Model_Store_Exception
     * @throws Exception
     */
    public function logoutAction()
    {
        /** @var $adminSession Mage_Admin_Model_Session */
        $adminSession = Mage::getSingleton('admin/session');
        $role = $adminSession->getData(Peppermint_Admin_Model_Sgate::SESSION_KEY_ROLE);
        $oidcToken = $adminSession->getData(Peppermint_Admin_Model_Sgate::OIDC_TOKEN);
        $accessToken = $adminSession->getData(Peppermint_Admin_Model_Sgate::ACCESS_TOKEN);
        $refreshToken = $adminSession->getData(Peppermint_Admin_Model_Sgate::REFRESH_TOKEN);
        $adminSession->unsetAll();
        $adminSession->getCookie()->delete($adminSession->getSessionName());
        $adminSession->addSuccess(Mage::helper('adminhtml')->__('You have logged out.'));

        /** @var Peppermint_Admin_Model_Sgate $sGate */
        $sGate = Mage::getModel('peppermint_admin/sgate');
        $sGate->revokeAccess($sGate->getRevokeTokenUri($role, $refreshToken));
        $sGate->revokeAccess($sGate->getRevokeTokenUri($role, $accessToken));
        $endSessionUri = $sGate->getEndSessionUri($role, $oidcToken);

        $this->getResponse()->setRedirect($endSessionUri);

        return $this;
    }
}
