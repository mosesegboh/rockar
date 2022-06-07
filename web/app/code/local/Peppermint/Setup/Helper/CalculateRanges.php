<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Stefan Lucaci <lucacistefan.alexandru@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Setup_Helper_CalculateRanges extends Mage_Core_Helper_Abstract
{
    /**
     * Calculate ranges for finance groups
     *
     * @param int $start
     * @param array $rGroup
     * @return string
     */
    public function calculateRange($start, $rGroup)
    {
        // init range
        $range = '';
        // create range
        foreach ($rGroup as $key => $val) {
            while ($start < $key) {
                $range .= $start . ',';
                $start = $start + $val;
            }
        }
        // add last range element
        end($rGroup);
        $range .= key($rGroup);

        return $range;
    }
}
