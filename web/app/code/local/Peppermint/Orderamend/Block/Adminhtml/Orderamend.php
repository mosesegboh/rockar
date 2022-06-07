<?php
/**
 * @category Peppermint
 * @package Peppermint\Orderamend
 * @author Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (https://rockar.com)
 *
 * Class Peppermint_Orderamend_Block_Adminhtml_Orderamend
 */

class Peppermint_Orderamend_Block_Adminhtml_Orderamend extends Rockar_Orderamend_Block_Adminhtml_Orderamend
{
    /**
     * Must specify key => label.
     * These fields will appear in the same order as specified here.
     *
     * @var array
     */
    protected $_visibleFields = [
        'quote'            => [
            ['key' => 'subtotal', 'label' => 'Subtotal'],
            ['key' => 'cc_charges', 'label' => 'CC Surcharges'],
            ['key' => 'shipping_amount', 'label' => 'Shipping & Handling'],
            ['key' => 'discount', 'label' => 'Discount'],
            ['key' => 'discount_description', 'label' => 'Discount Description'],
            ['key' => 'grand_total_excl_tax', 'label' => 'Grand total (Excl. Tax)'],
            ['key' => 'tax_amount', 'label' => 'Tax'],
            ['key' => 'grand_total', 'label' => 'Grand total (Incl. Tax)'],
            ['key' => 'total_paid', 'label' => 'Total Paid'],
            ['key' => 'total_refunded', 'label' => 'Total Refunded'],
            ['key' => 'order_date', 'label' => 'Order Date'],
            ['key' => 'order_status', 'label' => 'Order status']
        ],
        'car_details'      => [
            ['key' => 'title', 'label' => 'Car Name'],
            ['key' => 'vin_number', 'label' => 'VIN Number'],
            ['key' => 'sku', 'label' => 'SKU'],
            ['key' => 'exterior', 'label' => 'Exterior'],
            ['key' => 'interior', 'label' => 'Interior'],
            ['key' => 'options', 'label' => 'Options'],
            ['key' => 'accessories', 'label' => 'Accessories']
        ],
        'customer'         => [
            ['key' => 'name', 'label' => 'Customer name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'customer_group', 'label' => 'Customer Group'],
            ['key' => 'dob', 'label' => 'Date of Birth'],
            ['key' => 'prefix', 'label' => 'Prefix']
        ],
        'delivery_details' => [
            ['key' => 'type', 'label' => 'Shipping Method'],
            ['key' => 'store', 'label' => 'Collection Store'],
            ['key' => 'date', 'label' => 'Collection Date'],
            ['key' => 'delivery_fee', 'label' => 'Delivery Fee']
        ],
        'part_exchange'    => [
            ['key' => 'car_model', 'label' => 'Car Derivative'],
            ['key' => 'license_plate', 'label' => 'License Plate'],
            ['key' => 'part_exchange_value', 'label' => 'Part Exchange Value'],
            ['key' => 'outstanding_finance', 'label' => 'Outstanding Finance'],
            ['key' => 'car_mileage', 'label' => 'Total mileage'],
            ['key' => 'car_condition', 'label' => 'Car Condition'],
            ['key' => 'checkboxes', 'label' => 'Extra Exchange Info']
        ],
        'finance'          => [
            ['key' => 'payment_type', 'label' => 'Finance Product'],
            ['key' => 'finance_group', 'label' => 'Finance Group'],
            ['key' => 'finance_option_title', 'label' => 'Finance Option'],
            ['key' => 'finance_option_company', 'label' => 'Finance Company'],
            ['key' => 'deposit', 'label' => 'Initial Rental'],
            ['key' => 'term', 'label' => 'Initial Term'],
            ['key' => 'mileage', 'label' => 'Annual Mileage'],
            ['key' => 'deposit_multiple', 'label' => 'Deposit Multiple'],
            ['key' => 'maintenance', 'label' => 'Maintenance'],
            ['key' => 'finance_application_status', 'label' => 'Finance Application Status'],
            ['key' => 'monthly_price', 'label' => 'Monthly Rental'],
            ['key' => 'cashback', 'label' => 'Cashback']
        ],
        'lead_time'        => [
            ['key' => 'available_in', 'label' => 'Available In']
        ]
    ];
}
