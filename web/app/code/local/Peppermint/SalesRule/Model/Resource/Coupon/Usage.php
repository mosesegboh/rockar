<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Model_Resource_Coupon_Usage extends Mage_SalesRule_Model_Resource_Coupon_Usage
{
    /**
     * {@inheritDoc}
     */
    public function updateCustomerCouponTimesUsed($customerId, $couponId, $decrement = false)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select();
        $select->from($this->getMainTable(), ['times_used'])
            ->where('coupon_id = :coupon_id')
            ->where('customer_id = :customer_id');

        $timesUsed = $read->fetchOne($select, [':coupon_id' => $couponId, ':customer_id' => $customerId]);

        if ($timesUsed !== false) {
            if ($timesUsed == 0 && $decrement) {
                return;
            }

            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                [
                    'times_used' => $timesUsed + ($decrement ? -1 : 1)
                ],
                [
                    'coupon_id = ?' => $couponId,
                    'customer_id = ?' => $customerId,
                ]
            );
        } else {
            $this->_getWriteAdapter()->insert(
                $this->getMainTable(),
                [
                    'coupon_id' => $couponId,
                    'customer_id' => $customerId,
                    'times_used' => 1
                ]
            );
        }
    }
}
