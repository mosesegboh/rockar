<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Block_Adminhtml_ModelMatrix_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_modelMatrix';
        $this->_blockGroup = 'peppermint_catalog';

        parent::__construct();
        $this->removeButton('delete');
        $this->_addButton(
            'saveandcontinue',
            [
                'label' => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                'class' => 'save'
            ],
            -100
        );
        $this->_formScripts[] = "function saveAndContinueEdit(urlTemplate) {
                    var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/),
                        url = template.evaluate({});
                    editForm.submit(url);
                }";
    }

    /**
     * getHeaderText.
     *
     * @return string
     */
    public function getHeaderText()
    {
        $model = Mage::registry('peppermint_model_matrix_mapping');

        if ($model && $model->getModelCarousel()) {
            return $this->__('Edit model: %s', $model->getModelCarousel());
        }

        return $this->__('Edit');
    }

    /**
     * Getter of url for "Save and Continue" button.
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            [
                '_current' => true,
                'back' => 'edit'
            ]
        );
    }
}
