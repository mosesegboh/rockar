<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Orderstatus_Block_Adminhtml_Status_Mapping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'peppermint_orderstatus';
        $this->_controller = 'adminhtml_status_mapping';
        $modelTitle = $this->_getModelTitle();
        $this->_headerText = $this->getHeaderText();

        $this->_updateButton('save', 'label', $this->__("Save $modelTitle"));

        $this->_addButton('saveandcontinue', [
            'label' => $this->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save'
        ], -100);

        $this->_formScripts[] = 'function saveAndContinueEdit() {
            editForm.submit($(\'edit_form\').action+\'back/edit/\');
        }';
    }

    /**
     * Getter current model
     *
     * @return mixed
     */
    protected function _getModel()
    {
        return Mage::registry('current_orderstatus');
    }

    /**
     * Getter for model title
     *
     * @return string
     */
    protected function _getModelTitle()
    {
        return $this->__('Peppermint Order Status Mapping');
    }

    /**
     * Getter for header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $model = $this->_getModel();
        $modelTitle = $this->_getModelTitle();

        if ($model && $model->getId()) {
           return $this->__("Edit $modelTitle (ID: {$model->getId()})");
        }

        return $this->__("New $modelTitle");
    }

    /**
     * Getter for save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Getter for back (reset) url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * Getter for delete url
     *
     * @return string
     * @throws Exception
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]);
    }
}
