<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Block_Adminhtml_Promotions_Rule_Edit extends Rockar_PartExchange_Block_Adminhtml_Promotions_Rule_Edit
{
    /**
     * Peppermint_PartExchange_Block_Adminhtml_Promotions_Rule_Edit constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/partexchange_promotions/actions/reset')) {
            $this->_removeButton('reset');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/partexchange_promotions/actions/delete')) {
            $this->_removeButton('delete');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/partexchange_promotions/actions/save')) {
            $this->_removeButton('save');
        }

        if (!Mage::getSingleton('admin/session')->isAllowed('promo/partexchange_promotions/actions/save_and_continue')) {
            $this->_removeButton('save_and_continue_edit');
        }

        $model = Mage::registry('current_rockar_partexchange_promotions_rule');

        if (Mage::getSingleton('admin/session')->isAllowed('promo/partexchange_promotions/actions/approve')
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

            if ($model->getData('pending_action') === Peppermint_PartExchange_Model_PromotionsPending::ACTION_DELETE) {
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
