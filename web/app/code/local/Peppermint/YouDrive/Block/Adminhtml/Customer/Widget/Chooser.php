<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Block_Adminhtml_Customer_Widget_Chooser
    extends Rockar_YouDrive_Block_Adminhtml_Customer_Widget_Chooser
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'email',
            [
                'header'    => $this->__('Email'),
                'width'     => '150',
                'index'     => 'email',
                'renderer'  => 'peppermint_youdrive/adminhtml_customer_widget_renderer_email'
            ]
        );

        $this->addColumn(
            'dob',
            [
                'header'    => $this->__('Date Of Birth'),
                'index'     => 'dob',
                'renderer'  => 'peppermint_youdrive/adminhtml_customer_widget_renderer_dob',
            ]
        );
    }
}
