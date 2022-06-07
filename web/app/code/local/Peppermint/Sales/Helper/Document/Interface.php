<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

 /**
  * @var interface Peppermint_Sales_Helper_Document_Interface
  * This interface is intended for helpers for sales documents
  * Implement this interface on helpers listed in the Peppermint_Sales_Model_Order_Document file_helper
  * Different classes can get documents from different sources
  */
interface Peppermint_Sales_Helper_Document_Interface
{
    /**
     * Get Document or false if it fails
     * @param string $identifier
     * @return string|false
     */
    public function getDocument(string $identifier);
}
