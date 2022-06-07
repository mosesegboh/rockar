<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Block_Adminhtml_Promo_Catalog_Edit extends Mage_Adminhtml_Block_Promo_Catalog_Edit
{
    /**
     * Mage_Adminhtml_Block_Promo_Catalog_Edit constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_removeButton('save_apply');

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/catalog/actions/reset')) {
            $this->_removeButton('reset');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/catalog/actions/delete')) {
            $this->_removeButton('delete');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/catalog/actions/save')) {
            $this->_removeButton('save');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/catalog/actions/save_and_continue')) {
            $this->_removeButton('save_and_continue_edit');
        }

        $model = Mage::registry('current_promo_catalog_rule');

        if (Mage::getSingleton('admin/session')->isAllowed('promo/catalog/actions/approve')
            && $model->getId()
            && !$model->getIsApproved()
        ) {
            $approveUrl = $this->getUrl('*/*/approve', [
                $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                Mage_Core_Model_Url::FORM_KEY => $this->getFormKey()
            ]);

            $buttonAction = 'setLocation(\'' . $approveUrl . '\')';
            $confirmationMessage = Mage::helper('core')->jsQuoteEscape(
                $this->__('Are you sure you want to approve Rule deletion?')
            );

            if ($model->getData('pending_action') === Peppermint_CatalogRule_Model_RulePending::ACTION_DELETE) {
                $buttonAction = 'confirmSetLocation(\'' . $confirmationMessage . '\', \'' . $approveUrl . '\')';
            }

            $this->_addButton(
                'approve',
                [
                    'label' => $this->__('Approve'),
                    'onclick' => $buttonAction
                ]
            );
        }
    }
}
