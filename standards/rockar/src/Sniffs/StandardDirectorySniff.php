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

class StandardDirectorySniff implements Sniff
{

    private $_allowedClassTypes = [
        'Helper',
        'Model',
        'Block',
        'sql',
        'data',
        'controllers',
        'Controller',
        'etc'
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_CLASS];
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
        $error = true;
        $path = $phpcsFile->path;

        foreach ($this->_allowedClassTypes as $type) {
            if (strpos($path, $type)) {
                $error = false;
            }
        }

        if ($error) {
            $phpcsFile->addError('Unexpected class type found, please use only the following class types: [' . implode(', ', $this->_allowedClassTypes) . ']', $stackPtr, 'IncorrectClassType', []);
        }
    }
}
