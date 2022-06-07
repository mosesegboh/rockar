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

class FunctionVisibilitySniff implements Sniff
{

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
        $visibility = $phpcsFile->getMethodProperties($stackPtr)['scope'];
        $name = $phpcsFile->getDeclarationName($stackPtr);
        $underscore = $visibility != 'public';

        if ($visibility == "public" && strpos($name, "construct") !== false) {
            return true;
        }

        if ($underscore && $name[0] != '_') {
            $phpcsFile->addError('Expected _ before ' . $visibility . ' function name.', $stackPtr, 'MissingUnderscore', []);
        }

        if (!$underscore && $name[0] == '_') {
            $phpcsFile->addError('Unexpected _ before ' . $visibility . ' function name.', $stackPtr, 'FoundUnderscore', []);
        }
    }
}
