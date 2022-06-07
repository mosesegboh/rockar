<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Orderstatus_Adminhtml_Status_MappingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * {@inheritDoc}
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/peppermint_order_status')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Manage Order Status Mapping'), $this->__('Manage Order Status Mapping'));
    }

    /**
     * Show grid action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))
            ->_title($this->__('Manage Order Status Mapping'))
            ->_initAction();

        $this->_addContent($this->getLayout()->createBlock('peppermint_orderstatus/adminhtml_status_mapping'))
            ->renderLayout();
    }

    /**
     * Mass delete action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('ids');

        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select Statuses.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getSingleton('peppermint_orderstatus/status_mapping')->load($id);
                    $model->delete();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been deleted.', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('An error occurred while mass deleting items. Please review log and try again.')
                );
                Mage::logException($e);

                return;
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Edit action
     *
     * @return void
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('peppermint_orderstatus/status_mapping');

        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->_getSession()->addError(
                    $this->__('This Status no longer exists.')
                );
                $this->_redirect('*/*/');

                return;
            }
        }

        $data = $this->_getSession()->getFormData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('current_orderstatus', $model);

        $this->_title($this->__('Sales'))
            ->_title($this->__('Edit Order Status Mapping'));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * New action
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save action
     *
     * @return void
     */
    public function saveAction()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);

        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('entity_id', false);
            $model = Mage::getModel('peppermint_orderstatus/status_mapping');

            if ($id) {
                $model->load($id);

                if (!$model->getId()) {
                    $this->_getSession()->addError(
                        $this->__('This Status no longer exists.')
                    );
                    $this->_redirect('*/*/index');

                    return;
                }
            }

            try {
                $model->addData($data);

                $this->_getSession()->setFormData($data);
                $model->save();

                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess($this->__('The Status has been saved.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Unable to save the Status.'));
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                return;
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id', false)) {
            try {
                $model = Mage::getModel('peppermint_orderstatus/status_mapping')->load($id);

                if (!$model->getId()) {
                    Mage::throwException($this->__('Unable to find a Status to delete.'));
                }

                $model->delete();
                $this->_getSession()->addSuccess($this->__('The Status has been deleted.'));

                $this->_redirect('*/*/index');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('An error occurred while deleting Status data. Please review log and try again.')
                );
                Mage::logException($e);
            }
            $this->_redirect('*/*/edit', ['id' => $id]);

            return;
        }
        $this->_getSession()->addError(
            $this->__('Unable to find a Status to delete.')
        );
        $this->_redirect('*/*/index');
    }

    /**
     * Acl checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/peppermint_order_status');
    }
}
