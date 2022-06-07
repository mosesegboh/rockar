<?php

/**
 * @category  Peppermint
 * @package   Peppermint\All
 * @author    Krisjanis <techteam@rockar.com>
* @copyright  Copyright (c) 2016 Rockar Ltd (http://rockar.com)
 */
class Peppermint_All_Model_Email_Template extends Aschroder_SMTPPro_Model_Email_Template
{
    /**
     * {@inheritdoc}
     */
    protected function _applyInlineCss($html)
    {
        try {
            // Check to see if the {{inlinecss file=""}} directive set a CSS file to inline
            $inlineCssFile = $this->getInlineCssFile();
            // Only run Emogrify if HTML exists
            if (strlen($html) && $inlineCssFile) {
                $cssToInline = $this->_getCssFileContent($inlineCssFile);
                $emogrifier = new TijsVerkoyen_CssToInlineStyles($html, $cssToInline);

                $processedHtml = $emogrifier->convert();
            } else {
                $processedHtml = $html;
            }
        } catch (Exception $e) {
            $processedHtml = '{CSS inlining error: ' . $e->getMessage() . '}' . PHP_EOL . $html;
        }

        return $processedHtml;
    }
}
