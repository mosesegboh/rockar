<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Checkout
 * @author       Robert Ionas <robert.ionas@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Checkout_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Download file
     */
    public function downloadAction()
    {
        $varPath = Mage::getBaseDir('var');
        $file = $this->getRequest()->getParam('file');
        if (isset($file) && file_exists($varPath . DS . 'orders' . DS . $file)) {
            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename=$file");
            readfile($varPath . DS . 'orders' . DS . $file);
        } else {
            echo 'This file does not exist';
        }
    }
}
