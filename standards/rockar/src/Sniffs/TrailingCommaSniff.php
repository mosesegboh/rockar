<?php
/**
 * @category  Rockar
 * @package   Rockar_Sniffs
 * @author    Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

namespace Rockar\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class TrailingCommaSniff implements Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_OPEN_SHORT_ARRAY];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile the current file being checked
     * @param integer                     $stackPtr  the position of the current token in
     *                                               the stack passed in $tokens
     *
     * @return void|integer
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] === T_OPEN_SHORT_ARRAY) {
            $arrayStart = $stackPtr;
            $arrayEnd = $tokens[$stackPtr]['bracket_closer'];

            if ($tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line']) {
                $lastChar = $tokens[$arrayEnd - 1]['code'];
            } else {
                $tmpPoint = $arrayEnd - 1;

                while ($tokens[$tmpPoint]['code'] === T_WHITESPACE) {
                    --$tmpPoint;
                }
                $lastChar = $tokens[$tmpPoint]['code'];
            }

            if ($lastChar === T_WHITESPACE) {
                return $this->_throwError($phpcsFile, $stackPtr, 'LastCharWhiteSpace', 'Unnecessary whitespace found at the end of array');
            }

            if ($lastChar === T_COMMA) {
                return $this->_throwError($phpcsFile, $stackPtr, 'LastCharComma', 'Trailing comma found at the end of array');
            }
        }
    }

    /**
     * Helper function for logging errors.
     *
     * @param  \PHP_CodeSniffer\Files\File $phpcsFile the current file being checked
     * @param  integer                     $stackPtr  the position of the current token in
     *                                                the stack passed in $tokens
     * @param  string                      $code      the error code
     * @param  string                      $message   the error message
     * @param  array                       $data      additional data
     * @return boolean
     */
    private function _throwError($phpcsFile, $stackPtr, $code, $message, $data = [])
    {
        $phpcsFile->addError($message, $stackPtr, $code, $data);

        return false;
    }
}
