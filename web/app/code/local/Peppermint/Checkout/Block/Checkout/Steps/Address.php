<?php
/**
 * @category  Peppermint
 * @package   Peppermint/Checkout
 * @author Mihai Chezan <mihai.chezan@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_Checkout_Block_Checkout_Steps_Address
 */
class Peppermint_Checkout_Block_Checkout_Steps_Address extends Rockar_Checkout_Block_Checkout_Steps_Address
{
    /**
     * Get customer address
     *
     * @return string
     */
    public function getYourAddressDataJson()
    {
        $billingAddress = $this->getQuote()->getBillingAddress();
        $shippingAddress = $this->getQuote()->getShippingAddress();
        $customer = $this->getCustomer();
        $defaultCountry = Mage::helper('rockar_checkout')->getDefaultSelectedCountry();

        $streetLines = [
            'billing' => $billingAddress,
            'shipping' => $shippingAddress
        ];
        $streets = $this->_getStreets($streetLines);

        return Mage::helper('rockar_all')->jsonEncode([
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'email' => $customer->getEmail(),
            'primary_number' => $customer->getPrimaryNumber(),
            'secondary_number' => $customer->getSecondaryNumber(),
            'driving_license_type' => $customer->getDrivingLicenseType(),
            'south_african_id_number' => $customer->getSouthAfricanIdNumber(),
            'south_african_document_type' => $customer->getSouthAfricanDocumentType(),
            'country_of_citizenship' => $customer->getCountryOfCitizenship(),
            'streets' => array_filter($streets['billing']),
            'city' => $billingAddress->getCity(),
            'region' => $billingAddress->getRegion(),
            'postcode' => $billingAddress->getPostcode(),
            'country_id' => $billingAddress->getCountryId() ?: $defaultCountry,
            'dob' => $this->getDobData(),
            'additional_address' => [
                'streets' => array_filter($streets['shipping']),
                'city' => $shippingAddress->getCity(),
                'region' => $shippingAddress->getRegion(),
                'postcode' => $shippingAddress->getPostcode(),
                'country_id' => $shippingAddress->getCountryId()
            ]
        ]);
    }

    /**
     * Rewrites parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getAddressAction()
    {
        return Mage::getUrl('checkout/onepage/saveAddress', [
            '_secure' => true,
            'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);

    }

    /**
     * Get Customer Order Caps block
     *
     * @return string
     */
    public function getCustomerOrderCapBlock()
    {
        return htmlspecialchars(
            $this->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('customer_order_cap')
                ->toHtml()
        );
    }
}
