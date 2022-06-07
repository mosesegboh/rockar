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

class StringConcatSniff implements Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_STRING_CONCAT];
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

        if ($tokens[$stackPtr]['code'] === T_STRING_CONCAT) {
            if ($tokens[$stackPtr + 1]['code'] !== T_WHITESPACE || $tokens[$stackPtr - 1]['code'] !== T_WHITESPACE) {
                $phpcsFile->addError('Expected space either side of a string concat.', $stackPtr, 'MissingSpace', []);
            }
        }
    }
}
