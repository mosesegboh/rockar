<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Adminhtml_OfferTagsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Handles index action, displays the grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage Offer Tags'));
        $this->loadLayout();
        $this->_setActiveMenu('promo/offerTags');
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
            ->_setActiveMenu('promo/offerTags');

        $this->_title($this->__('Manage Offer Tags'));
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
        $offertagId = $this->getRequest()->getParam('offertag_id', false);
        $model = Mage::getModel('peppermint_offertags/offerTags');

        if ($offertagId) {
            $model->load($offertagId);

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
            if (isset($data['icon'])) {
                $iconValue = is_array($data['icon']) ? $data['icon']['value'] : $data['icon'];
                $data['icon'] = $iconValue;
            }

            $model->setData($data);
        }

        Mage::register('peppermint_offertags', $model);

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
            $model = Mage::getModel('peppermint_offertags/offerTags');

            try {
                $offerTagId = $request->getParam('offertag_id', false);

                if ($offerTagId) {
                    $model->load($offerTagId);

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
                $data['brand_bg_color'] = isset($data['brand_bg_color']) ? true : false;

                $model->addData($data);

                if (in_array($data['action_type'],
                    [
                        Peppermint_OfferTags_Helper_Data::DISPLAY_OPTION_ICON,
                        Peppermint_OfferTags_Helper_Data::DISPLAY_OPTION_ICON_TEXT
                    ]
                )
                ) {
                    $iconAlreadyExists = isset($data['icon']['value']) && $data['icon']['value'];
                    $iconDeleted = isset($data['icon']['delete']) && $data['icon']['delete'];
                    $iconUploaded = isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != '';
                    if (!$iconUploaded && ($iconDeleted || !$iconAlreadyExists)) {
                        throw new Mage_Core_Exception('Icon image required if you choose to show Offer Tag as "Icon" or as "Icon and text."');
                    }
                }

                $model->save();

                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess(
                    $this->__('Item has been saved.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Unable to save item.'));
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', ['offertag_id' => $model->getId()]);

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
        if ($id = $this->getRequest()->getParam('offertag_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('peppermint_offertags/offerTags')->load($id);
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

            $this->_redirect('*/*/edit', ['offertag_id' => $id]);

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
        return Mage::getSingleton('admin/session')->isAllowed('promo/peppermint_offertags/manage_offertags');
    }
}
