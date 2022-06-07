<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Adminhtml_ExperiencesController extends Mage_Adminhtml_Controller_Action
{
    const ERROR_NO_IMAGE = 99999;

    /**
     * Handles index action, displays the grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage Experiences'));
        $this->loadLayout();
        $this->_setActiveMenu('promo/experiences');
        $this->renderLayout();
    }

    /**
     * Grid Action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Init action.
     *
     * @return void
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo/experiences');

        $this->_title($this->__('Manage Experiences'));
    }

    /**
     * New action.
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit action.
     *
     * @return boolean
     */
    public function editAction()
    {
        $experienceId = $this->getRequest()->getParam('experience_id', false);
        $model = Mage::getModel('peppermint_experiences/experiences');

        if ($experienceId) {
            $model->load($experienceId);

            if (!$model->getId()) {
                $this->_getSession()->addError(
                    $this->__('This item no longer exists.')
                );

                $this->_redirect('*/*/index');

                return false;
            }
        }

        $data = $this->_getSession()->getFormData(true);

        if (!empty($data)) {
            if (isset($data['image'])) {
                $imageValue = is_array($data['image']) ? $data['image']['value'] : $data['image'];
                $data['image'] = $imageValue;
            }

            $model->setData($data);
        }

        Mage::register('peppermint_experiences', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Save action.
     *
     * @return boolean
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        $redirectBack = $request->getParam('back', false);

        if ($data = $request->getPost()) {
            $model = Mage::getModel('peppermint_experiences/experiences');

            try {
                $experienceId = $request->getParam('experience_id', false);

                if ($experienceId) {
                    $model->load($experienceId);

                    if (!$model->getId()) {
                        $this->_getSession()->addError(
                            $this->__('This item no longer exists.')
                        );
                        $this->_redirect('*/*/index');

                        return false;
                    }
                }

                // save
                $this->_getSession()->setFormData($data);

                $model->addData($data);

                $imageAlreadyExists = isset($data['image']['value']) && $data['image']['value'];
                $imageDeleted = isset($data['image']['delete']) && $data['image']['delete'];
                $imageUploaded = isset($_FILES['image']['name']) && $_FILES['image']['name'] != '';
                if (!$imageUploaded && ($imageDeleted || !$imageAlreadyExists)) {
                    throw new Mage_Core_Exception('Image field is Mandatory', self::ERROR_NO_IMAGE);
                }

                $model->save();

                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess(
                    $this->__('Item has been saved.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $e->getCode() === self::ERROR_NO_IMAGE
                        ?  $this->__($e->getMessage())
                        : $this->__('Unable to save item.')
                );
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', ['experience_id' => $model->getId()]);

                return false;
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * deleteAction
     *
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('experience_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('peppermint_experiences/experiences')->load($id);
                if (!$model->getId()) {
                    Mage::throwException($this->__('Unable to find record to delete.'));
                }
                $model->delete();

                $this->_getSession()->addSuccess(
                    $this->__('The record has been deleted.')
                );

                $this->_redirect('*/*/index');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('An error occurred while deleting record.')
                );
                Mage::logException($e);
            }

            $this->_redirect('*/*/edit', ['experience_id' => $id]);

            return;
        }

        $this->_getSession()->addError(
            $this->__('Unable to find record to delete.')
        );

        $this->_redirect('*/*/index');
    }

    /**
     * Check the permission to run it
     *
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/peppermint_experiences/manage_experiences');
    }
}
