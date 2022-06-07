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

class VariableSniff implements Sniff
{

    private $_prohibitedNames = [
        '$something',
        '$random',
        '$arr',
        '$array',
        '$string',
        '$str',
        '$int',
        '$var',
        '$variable'
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_VARIABLE];
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

        if ($tokens[$stackPtr]['code'] === T_VARIABLE) {
            $variableName = $tokens[$stackPtr]['content'];

            // Ensure variable names are longer than 2 chars, unless $i
            if (strlen($variableName) <= 3 && $variableName != '$i' && $variableName != '$e') {
                $phpcsFile->addError('Expected detailed variable name, ' . $variableName . ' found!', $stackPtr, 'OneLetter', []);
            }

            // Ensure variable names doesn't exist in the prohibited name array
            if (in_array($variableName, $this->_prohibitedNames)) {
                $phpcsFile->addError('Expected detailed variable name, ' . $variableName . ' found!', $stackPtr, 'OneLetter', []);
            }

            // Ensure any variable used only once is flagged
            // @todo
        }
    }
}
