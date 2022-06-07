<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Adminhtml_Admin_RoleController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Mage_Core_Block_Abstract
     */
    private $_createBlock;

    /**
     * @var Mage_Core_Helper_Abstract
     */
    private $_adminHelper;

    /**
     * Peppermint_Admin_Adminhtml_Admin_RoleController constructor.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = []
    ) {
        $this->_adminHelper = Mage::helper('peppermint_admin');
        $this->_createBlock = $this->getLayout()->createBlock('peppermint_admin/adminhtml_role_grid');
        parent::__construct(
            $request,
            $response,
            $invokeArgs
        );
    }

    /**
     * @throws Mage_Core_Exception
     * @return false|Mage_Core_Model_Abstract
     */
    protected function _initRole()
    {
        $role = Mage::getModel('peppermint_admin/role');

        if ($roleId = (int) $this->getRequest()->getParam('id')) {
            $role->load($roleId);
        }
        Mage::register('current_role', $role);

        return $role;
    }

    /**
     * S-Gate access action.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->_adminHelper->__('S-Gate Access'))
            ->_title($this->_adminHelper->__('Roles'));
        $this->renderLayout();
    }

    /**
     * S-Gate access grid.
     *
     * @return void
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * Edit role action.
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function editAction()
    {
        $role = $this->_initRole();

        if ($roleId = $this->getRequest()->getParam('id') && !$role->getId()) {
            $this->_getSession()->addError(
                $this->_adminHelper->__('This role no longer exists.')
            );
            $this->_redirect('*/*/');

            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getRoleData(true);

        if (!empty($data)) {
            $role->setData($data);
        }

        $this->loadLayout();
        $this->_title($this->_adminHelper->__('S-Gate Access'))
            ->_title($this->_adminHelper->__('Roles'));
        $this->_title($this->_adminHelper->__('Add role'));
        $this->renderLayout();
    }

    /**
     * S-Gate access new action.
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save role action.
     *
     * @return void
     */
    public function saveAction()
    {
        $adminSession = Mage::getSingleton('adminhtml/session');
        $request = $this->getRequest();

        if ($data = $request->getPost('role')) {
            $rowId = $request->getParam('id');

            try {
                $role = $this->_initRole();
                $role->addData($data);
                $role->save();
                $adminSession->addSuccess(
                    $this->_adminHelper->__('Role was successfully saved')
                );
                $adminSession->setFormData(false);

                if ($request->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $role->getId()]);
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $adminSession->addError($e->getMessage());
                $adminSession->setRoleData($data);
                $this->_redirect('*/*/edit', ['id' => $rowId]);
            } catch (Exception $e) {
                Mage::logException($e);
                $adminSession->addError(
                    $this->_adminHelper->__('Something went wrong. Please try again.')
                );
                $adminSession->setRoleData($data);
                $this->_redirect('*/*/edit', ['id' => $rowId]);
            }

            return;
        }

        $adminSession->addError(
            $this->_adminHelper->__('Unable to find role to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * Delete role action.
     *
     * @return void
     */
    public function deleteAction()
    {
        $roleId = $this->getRequest()->getParam('id');
        $adminSession = Mage::getSingleton('adminhtml/session');

        if ($roleId > 0) {
            try {
                Mage::getModel('peppermint_admin/role')->setId($roleId)->delete();
                $adminSession->addSuccess(
                    $this->_adminHelper->__('Role was successfully deleted.')
                );
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $adminSession->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $roleId]);
            } catch (Exception $e) {
                Mage::logException($e);
                $adminSession->addError(
                    $this->_adminHelper->__('There was an error deleting role.')
                );
                $this->_redirect('*/*/edit', ['id' => $roleId]);
            }

            return;
        }

        $adminSession->addError(
            $this->_adminHelper->__('Could not find role to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * Adding mass delete action to the grid.
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $roleIds = $this->getRequest()->getParam('role');
        $adminSession = Mage::getSingleton('adminhtml/session');

        if (!is_array($roleIds)) {
            $adminSession->addError(
                $this->_adminHelper->__('Please select roles to delete.')
            );
        } else {
            try {
                foreach ($roleIds as $roleId) {
                    Mage::getModel('peppermint_admin/role')->setId($roleId)->delete();
                }
                $adminSession->addSuccess(
                    $this->_adminHelper->__(
                        'Total of %d roles were successfully deleted.',
                        count($roleIds)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $adminSession->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $adminSession->addError(
                    $this->_adminHelper->__('There was an error deleting roles.')
                );
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Adding mass status action to the grid.
     *
     * @return void
     */
    public function massStatusAction()
    {
        $request = $this->getRequest();
        $roleIds = $request->getParam('role');
        $adminSession = Mage::getSingleton('adminhtml/session');

        if (!is_array($roleIds)) {
            $adminSession->addError(
                $this->_adminHelper->__('Please select roles.')
            );
        } else {
            try {
                foreach ($roleIds as $roleId) {
                    Mage::getSingleton('peppermint_admin/role')->load($roleId)
                        ->setStatus($request->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d roles were successfully updated.', count($roleIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $adminSession->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $adminSession->addError(
                    $this->_adminHelper->__('There was an error updating roles.')
                );
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Export csv file.
     *
     * @return void
     */
    public function exportCsvAction()
    {
        $fileName = 'role.csv';
        $content = $this->_createBlock->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export excel file.
     *
     * @return void
     */
    public function exportExcelAction()
    {
        $fileName = 'role.xls';
        $content = $this->_createBlock->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export xml file.
     *
     * @return void
     */
    public function exportXmlAction()
    {
        $fileName = 'role.xml';
        $content = $this->_createBlock->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/peppermint_admin/role');
    }
}
