<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Model_Adminhtml_Observer
{
    /**
     * Remove delete action from bookings grid
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function removeBookingsGridDeleteAction(Varien_Event_Observer $observer)
    {
        /** @var Rockar_YouDrive_Block_Adminhtml_Booking_Grid $block */
        $block = $observer->getEvent()->getBlock();
        $block->getMassactionBlock()->removeItem('delete');
    }

    /**
     * Add bookings / booking items grid filter by user local stores
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addBookingsFilterByUserLocalStores(Varien_Event_Observer $observer)
    {
        $table = 'main_table';

        if ($observer->getEvent()->getName() === 'rockar_youdrive_booking_item_grid_prepare_collection') {
            $table = 'booking';
        }

        $collection = $observer->getCollection();
        $adminUserLocalStores = $this->_getAdminUserLocalStores();

        if ($adminUserLocalStores) {
            $collection->addFieldToFilter($table . '.assigned_to', ['in' => $adminUserLocalStores]);
        }
    }

    /**
     * Restrict edit booking page by user local stores
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    public function restrictBookingEditByUserLocalStores(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getData('controller_action');
        $bookingId = $action->getRequest()->getParam('id');

        if ($bookingId) {
            $adminUserLocalStores = $this->_getAdminUserLocalStores();

            if ($adminUserLocalStores) {
                $collection = Mage::getModel('rockar_youdrive/booking')->getCollection()
                    ->addFieldToFilter('id', ['eq' => $bookingId])
                    ->addFieldToFilter('assigned_to',  ['in' => $adminUserLocalStores])
                    ->setPageSize(1);

                if ($collection->getSize()) {
                    return true;
                }

                $this->_setDenied($action);

                return false;
            }
        }

        return true;
    }

    /**
     * Remove the model column from the block
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function removeModelColumn(Varien_Event_Observer $observer)
    {
        $observer->getBlock()
            ->removeColumn('model');

        return $this;
    }

    /**
     * Get assigned local stores to current admin user
     *
     * @return array|null
     */
    protected function _getAdminUserLocalStores()
    {
        $adminUserLocalStores = Mage::getSingleton('admin/session')->getUser()->getData('local_stores');

        return $adminUserLocalStores ? explode(',', $adminUserLocalStores) : null;
    }

    /**
     * Redirect response to denied page
     *
     * @param $action
     * @return void
     */
    protected function _setDenied($action)
    {
        $action->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl('*/*/denied'));
        $action->setFlag('', $action::FLAG_NO_DISPATCH, true);
    }

    /**
     * Edit date fields in booking form
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function editBookingFields(Varien_Event_Observer $observer)
    {
        /** @var Rockar_YouDrive_Block_Adminhtml_Booking_Edit_Form $block */
        $block = $observer->getEvent();
        $form = $block->getForm();

        $bookedOn = $form->getElement('booked_on');
        $bookedOn->setData('required', false);
        $bookedOn->setData('value', null);

        $bookedTo = $form->getElement('booked_to');
        $bookedTo->setData('required', false);
        $bookedTo->setData('value', null);
    }

    /**
     * Add cutomer primary phone number to booking fields
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addCustomerPhoneField(Varien_Event_Observer $observer)
    {
        /** @var Rockar_YouDrive_Block_Adminhtml_Booking_Edit_Form $block */
        $block = $observer->getEvent();
        $form = $block->getForm();

        $model = $observer->getModel();

        if ($model->getId()) {
            $customer = Mage::getModel('customer/customer')->load($model->getCustomerId());

            $helper = Mage::helper('rockar_youdrive');

            $fieldset = $form->getElement('customer_fieldset');
            $fieldset->addField('description',
                'label',
                [
                    'name' => 'customer_phone_number',
                    'label' => $helper->__('Primary phone number'),
                    'title' => $helper->__('Primary phone number'),
                    'value' => $customer->getPrimaryNumber()
                ],
                'customer_email'
            );
        }
    }
}
