<?php

/**
 * @category     Setup
 * @package      Peppermint\AschroderEmail
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_AschroderEmail_Block_Log_Grid extends Aschroder_Email_Block_Log_Grid
{
    protected function _prepareColumns()
    {
        $this->addColumnAfter(
            'attachment_path',
            [
                'header' => Mage::helper('aschroder_email')->__('Attachment'),
                'index' => 'attachment_path',
                'width' => '160px',
                'renderer' =>  'Peppermint_AschroderEmail_Block_Adminhtml_Renderer_Attachment'
            ],
            'email_to'
        );

        return parent::_prepareColumns();
    }
}
