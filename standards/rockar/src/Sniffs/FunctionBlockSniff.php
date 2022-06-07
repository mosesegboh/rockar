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

class FunctionBlockSniff implements Sniff
{

    private $_firstFunction = true;

    private $_lastFileName = '';

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_FUNCTION];
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
        if ($this->_lastFileName !== $phpcsFile->getFilename()) {
            $this->_firstFunction = true;
            $this->_lastFileName = $phpcsFile->getFilename();
        }

        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] === T_FUNCTION) {
            // Ensure no blank lines between function block and function
            $line = $tokens[$stackPtr]['line'];
            $closingDoc = $phpcsFile->findPrevious(T_DOC_COMMENT_CLOSE_TAG, $stackPtr);
            $blankLines = $line - $tokens[$closingDoc]['line'] - 1;

            if ($blankLines !== 0) {
                $phpcsFile->addError('Expected no blank lines between function block and function, ' . $blankLines . ' found!', $stackPtr, 'FoundLines', []);
            }

            // Ensure Exactly 1 blank line before opening function block tag, unless first block
            if (!$this->_firstFunction) {
                $openingDoc = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr);
                $tmpPoint = $openingDoc - 1;

                while ($tokens[$tmpPoint]['code'] === T_WHITESPACE) {
                    --$tmpPoint;
                }
                $blankLines = $tokens[$openingDoc]['line'] - $tokens[$tmpPoint]['line'] - 1;

                if ($blankLines !== 1) {
                    $phpcsFile->addError('Expected exactly 1 blank lines between last content and opening function block, ' . $blankLines . ' found!', $stackPtr, 'IncorrectBlankLines', []);
                }
            } else {
                $this->_firstFunction = false;
            }
        }
    }
}
