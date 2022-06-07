<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Block_Adminhtml_Promo_Quote_Edit extends Mage_Adminhtml_Block_Promo_Quote_Edit
{
    /**
     * Peppermint_SalesRule_Block_Adminhtml_Promo_Quote_Edit constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/quote/actions/reset')) {
            $this->_removeButton('reset');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/quote/actions/delete')) {
            $this->_removeButton('delete');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/quote/actions/save')) {
            $this->_removeButton('save');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/quote/actions/save_and_continue')) {
            $this->_removeButton('save_and_continue_edit');
        }

        $model = Mage::registry('current_promo_quote_rule');

        if (Mage::getSingleton('admin/session')->isAllowed('promo/quote/actions/approve')
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

            if ($model->getData('pending_action') === Peppermint_CatalogRule_Model_RulesLog::ACTION_DELETE) {
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
