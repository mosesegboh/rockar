<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Adminhtml_ModelMatrixController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Customers list action
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage Model Matrix'));
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('catalog/manage_model_matrix');

        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('peppermint_catalog/adminhtml_modelMatrix')
        );

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
     * Edit action
     *
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function editAction()
    {
        $mappingId = $this->getRequest()->getParam('id', false);
        $model = Mage::getModel('peppermint_catalog/matrixMapping');

        if ($mappingId) {
            $model->load($mappingId, 'model_carousel');

            if (!$model->getModelCarousel()) {
                $this->_getSession()->addError(
                    $this->__('This item no longer exists.')
                );

                $this->_redirect('*/*/index');

                return false;
            }
        }

        $data = $this->_getSession()->getFormData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('peppermint_model_matrix_mapping', $model);

        $this->loadLayout();
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
            $model = Mage::getModel('peppermint_catalog/matrixMapping');

            try {
                $modelCarousel = $request->getParam('model_carousel', false);
                $collection = $model->getCollection()->addFieldToFilter('model_carousel', $modelCarousel);

                if (!$modelCarousel || !count($collection)) {
                    $this->_getSession()->addError(
                        $this->__('This item no longer exists.')
                    );
                    $this->_redirect('*/*/index');

                    return false;
                }
                
                // save
                $this->_getSession()->setFormData($data);

                foreach ($collection as $item) {
                    $item->setPosition($data['position']);
                    $item->save();
                }

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
                $this->_redirect('*/*/edit', ['id' => $request->getParam('model_carousel')]);

                return false;
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Check the permission to run it
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/manage_model_matrix');
    }
}
