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

class DocBlockSniff implements Sniff
{

    private $_longestKey = 0;

    private $_existingTags = [];

    private $_requiredTags = [
        'category',
        'package',
        'author',
        'copyright'
    ];

    private $_prohibitedTags = [
        'license',
        'var'
    ];

    private $_regexTags = [
        'category' => '/(Peppermint|Rockar)/',
        'package' => '/^(Peppermint|Rockar)_[a-zA-Z]+$/',
        'author' => '/^.*? <.*?@rockar\.com>$/',
        'copyright' => '/Copyright \(c\) 20[0-2][0-9] Rockar,? Ltd \(http:\/\/rockar\.com\)/'
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_DOC_COMMENT_OPEN_TAG,
            T_DOC_COMMENT_CLOSE_TAG,
            T_DOC_COMMENT_TAG,
            T_CLASS
        ];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile the current file being checked
     * @param integer                     $stackPtr  the position of the current token in
     *                                               the stack passed in $tokens
     *
     * @return integer|void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $classPtr = $phpcsFile->findNext(T_CLASS, 0);

        // Confirm required DocBlock tags exist
        if ($tokens[$stackPtr]['code'] === T_CLASS) {
            $error = false;

            foreach ($this->_requiredTags as $key) {
                if (!in_array($key, $this->_existingTags)) {
                    $this->_throwError($phpcsFile, $stackPtr, 'Missing_RequiredTags', 'Missing required ' . $key . ' tag!');
                    $error = true;
                }
            }

            return !$error;
        }

        // Confirm DocBlock tags
        if ($tokens[$stackPtr]['code'] === T_DOC_COMMENT_TAG && $stackPtr < $classPtr) {
            $key = substr($tokens[$stackPtr]['content'], 1);
            $value = $tokens[$stackPtr + 2];

            $this->_existingTags[] = $key;

            // Isn't prohibited
            if (in_array($key, $this->_prohibitedTags)) {
                return $this->_throwError($phpcsFile, $stackPtr, ucwords($key) . 'Found', ucwords($key) . ' is a prohibited FileDoc tag!');
            }

            // Has a value
            if ($value['code'] !== T_DOC_COMMENT_STRING) {
                return $this->_throwError($phpcsFile, $stackPtr, 'MissingValue', 'Missing value for ' . ucwords($key) . ' tag!');
            }

            // Has a valid value
            if (isset($this->_regexTags[$key])) {
                $matches = [];
                $match = preg_match($this->_regexTags[$key], $value['content'], $matches);

                if (count($matches) <= 0) {
                    return $this->_throwError($phpcsFile, $stackPtr, 'IncorrectValue', 'Incorrect value for ' . ucwords($key) . ' tag! (Regex: ' . $this->_regexTags[$key] . ')');
                }
            }

            // Has the correct number of spaces
            if ($this->_longestKey === 0) {
                $this->_longestKey = max(array_map('strlen', $this->_requiredTags)) + 1;
            }

            $keyStart = $tokens[$stackPtr]['column'];
            $keyLength = strlen($tokens[$stackPtr]['content']) - 1;
            $valueStart = $value['column'];
            $numberOfSpace = $valueStart - ($keyStart + $keyLength) - 1;
            $eitherSpace = $this->_longestKey - $keyLength;

            if ($numberOfSpace !== 1 && $numberOfSpace !== $eitherSpace) {
                return $this->_throwError($phpcsFile, $stackPtr, 'IncorrectSpacing', 'Incorrect number of spaces for ' . $key . ', expecting either 1 or ' . $eitherSpace . ', got ' . $numberOfSpace . '.');
            }
        }

        // Confirm no space before DocBlock
        if ($tokens[$stackPtr]['code'] === T_DOC_COMMENT_OPEN_TAG && $stackPtr < $classPtr) {
            if ($tokens[$stackPtr]['line'] !== 2) {
                return $this->_throwError($phpcsFile, $stackPtr, 'NoSpace', 'FileDoc should be on the 2nd line of the file, with no space between opening PHP tag');
            }
        }

        // Confirm space after DocBlock
        if ($tokens[$stackPtr]['code'] === T_DOC_COMMENT_CLOSE_TAG && $stackPtr < $classPtr) {
            $line = $tokens[$stackPtr]['line'];
            $tmpPoint = $stackPtr + 1;

            while ($tokens[$tmpPoint]['code'] === T_WHITESPACE) {
                ++$tmpPoint;
            }
            $blankLines = $tokens[$tmpPoint]['line'] - $line - 1;

            if ($blankLines !== 1) {
                return $this->_throwError($phpcsFile, $stackPtr, 'IncorrectLineBreak', 'Expected exactly 1 blank line between closing DocBlock and next line of content, ' . $blankLines . ' lines found!');
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
