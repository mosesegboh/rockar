<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Checkout_Helper_Pdf extends Mage_Core_Helper_Abstract
{
    /**
     * car data from order item finance_data_variables to show on OTP
     *
     * @var array
     */
    protected static $_carDataToInclude = [
        'Extra Options' => 'Extra Options',
        'Options' => 'Options',
        'Line/Packages' => 'Line/Packages',
        'Accessories' => 'Accessories'
    ];

    /**
     * Rockar_All helper
     *
     * @var Mage_Core_Helper_Abstract
     */
    protected $allHelper;

    /**
     * Peppermint_Checkout_Helper_Pdf constructor.
     */
    public function __construct()
    {
        $this->allHelper = Mage::helper('rockar_all');
    }

    /**
     * Create pdf from html
     *
     * @param null $file
     * @param null $jsonFile
     * @param null|string $pdfFile
     * @param null|string $incrementId
     * @return string|void path to pdf file used in email or download
     */
    public function generate($file = null, $jsonFile = null, $pdfFile = null, $incrementId = null)
    {
        if (!$file || !$jsonFile || !$pdfFile) {
            return;
        }

        $pathNode = Mage::getBaseDir('skin') . DS . 'frontend' . DS . 'rockar';
        $varPath = Mage::getBaseDir('var');
        $tmpPath = Mage::getBaseDir('tmp');
        $outFile = $incrementId ? $tmpPath . DS . $incrementId . '.pdf' : $tmpPath . DS . 'out.pdf';

        if (!file_exists($varPath . DS . 'orders')) {
            mkdir($varPath . DS . 'orders', 0775, true);
        }
        // copy pdf file to be available for download
        exec(
            'export OPENSSL_CONF=/etc/ssl/; '
            . $pathNode . DS . 'node_modules' . DS . '.bin' . DS . 'phantomjs '
            . $jsonFile . ' ' . $file . ' ' . $outFile . ' 2>&1'
        );
        copy($outFile, $pdfFile);
        unlink($outFile);

        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'pdf/index/download?file=' . basename($pdfFile);
    }

    /**
     * Create html needed for pdf OTP file.
     *
     * @param integer|string $incrementId
     * @param Peppermint_Sales_Model_Order $order
     * @param boolean $display
     *
     * @return string|void
     */
    public function makeOtp($incrementId, Peppermint_Sales_Model_Order $order = null, $display = true)
    {
        try {
            $checkoutHelper = Mage::helper('rockar_checkout/order');

            if (!$order) {
                $order = Mage::getModel('sales/order')->load($incrementId, 'increment_id');
            }

            $orderItem = $checkoutHelper->getFirstVisibleItem($order);
            $simpleProduct = $checkoutHelper->getFirstSimpleProduct($order);
            $product = $simpleProduct ? Mage::getModel('catalog/product')->load($simpleProduct->getId()) : false;

            if ($orderItem && $product) {
                /* prepare order and product data */
                $orderVars = $order->getData();
                $orderId = $orderVars['entity_id'] ?? $order->getId();
                $storeId = $orderVars['store_id'] ?? $order->getStoreId();

                /* prepare customer and address data */
                $customer = Mage::getModel('customer/customer')->load($orderVars['customer_id'] ?? '');
                $billing = $order->getBillingAddress();
                $orderAdditionalData = Mage::getModel('rockar_checkout/order_additional')->getCollection()
                    ->addFieldToFilter('order_id', $orderId)
                    ->addFieldToSelect([
                        'is_company',
                        'vat_number',
                        'registration_number',
                        'company_name'
                    ])
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->getFirstItem();

                $customerDataArray = $this->_getCustomerCompanyData(
                    $orderAdditionalData,
                    $billing,
                    $orderVars
                );

                /* prepare finance and PX data */
                $financeDataVariables = $orderItem->getData('finance_data_variables');
                $financeDataVariables = $financeDataVariables
                    ? $this->allHelper
                        ->jsonDecode($financeDataVariables)
                    : [];

                $partExchangeData = Mage::getModel('rockar_partexchange/order')->load($orderId, 'order_id')
                    ->getData();

                /* prepare PDF template data */
                $baseFile = Mage::getBaseDir('tmp') . DS . $incrementId;
                $file = $baseFile . '.html';
                $confFile = $baseFile . '.js';
                $productResourceModel = Mage::getResourceModel('catalog/product');

                // get admin attribute id and value for condition
                $vehicleConditionId = $productResourceModel->getAttributeRawValue(
                    $product->getId(),
                    'vehicle_condition',
                    Mage_Core_Model_App::ADMIN_STORE_ID
                );

                $vehicleCondition = $productResourceModel->getAttribute('vehicle_condition')
                    ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
                    ->getSource()
                    ->getOptionText($vehicleConditionId);

                $driverRowspan = 3;
                $shortSubtitle = $product->getShortSubtitle();

                if ($vehicleCondition !== Peppermint_Catalog_Helper_Vehicle::CONDITION_NEW) {
                    $driverRowspan = 4;
                    $shortSubtitle = preg_replace('/\([0-9]+ km\)/', '', $shortSubtitle);
                }

                $html = file_get_contents(dirname(__DIR__) . DS . 'template' . DS . 'otp_header.html')
                . '<table>'
                . $this->_createTableRow(
                    [
                        $this->__('Offer made for Client:'),
                        $customerDataArray['offerForClient'],
                        $this->__('Make:'),
                        $product->getShortTitle() . ' ' . $shortSubtitle
                    ]
                )
                . $this->_createTableRow(
                    [
                        $this->__('Customer Group:'),
                        $this->__($this->_getCustomerGroupCode($order->getCustomerGroupId())),
                        '',
                        ''
                    ]
                )
                . $this->_createTableRow(
                    [
                        [
                            'data' => $this->__('Driver:'),
                            'rowspan' => $driverRowspan
                        ],
                        [
                            'data' => $customerDataArray['driver'],
                            'rowspan' => $driverRowspan
                        ],
                        $this->__('Order Type:'),
                        $this->__($this->_getAttributePerStore($product, $storeId, 'vehicle_condition'))
                    ]
                )
                . $this->_createTableRow(
                    [
                        $this->__('Fuel Type:'),
                        $this->__($this->_getAttributePerStore($product, $storeId, 'fuel_type'))
                    ]
                )
                . $this->_createTableRow(
                    [
                        $this->__('Transmission:'),
                        $this->__($this->_getAttributePerStore($product, $storeId, 'transmission'))
                    ]
                );

                if (
                    $vehicleCondition !== Peppermint_Catalog_Helper_Vehicle::CONDITION_NEW
                ) {
                    $html .= $this->_createTableRow(
                        [
                            $this->__('Mileage:'),
                            number_format(
                                $this->_getAttributePerStore($product, $storeId, 'km'),
                                0,
                                '.',
                                ' '
                            ) . ' km'
                        ]
                    );
                }

                $html .= $this->_createTableRow(
                        [
                            $this->__('Physical Address:'),
                            $customerDataArray['street'],
                            $this->__('Colour:'),
                            $this->__($this->_getAttributePerStore($product, $storeId, 'exterior')) .
                            " ({$this->_getAttributePerStore($product, false, 'exterior')})"
                        ]
                    )
                    . $this->_createTableRow(
                        [
                            $this->__('Email Address:'),
                            $orderVars['customer_email'],
                            $this->__('Trim:'),
                            $this->__($this->_getAttributePerStore($product, $storeId, 'interior')) .
                            "({$this->_getAttributePerStore($product, false, 'interior')})"
                        ]
                    )
                    . $this->_createTableRow(
                        [
                            $this->__('Telephone:'),
                            $billing->getTelephone(),
                            $this->__('Engine No:'),
                            $product->getEngineno() ?: $this->_getAttributeNonStore($product, 'engineno')
                        ]
                    )
                    . $this->_createTableRow(
                        [
                            $this->__('ID Type:'),
                            $this->__($this->_getAttributePerStore($customer, $storeId, 'south_african_document_type')),
                            $this->__('VIN:'),
                            $this->_getAttributePerStore($product, false, 'vin_number')
                        ]
                    )
                    . $this->_createTableRow(
                        [
                            [
                                'data' => $this->__('ID / Passport Number:'),
                                'rowspan' => 3
                            ],
                            [
                                'data' => $this->_getAttributeNonStore($customer, 'south_african_id_number'),
                                'rowspan' => 3
                            ],
                            $this->__('Registration No:'),
                            $this->_getAttributeNonStore($product, 'registration_number')
                        ]
                    )
                    . $this->_createTableRow(
                        [
                            $this->__('Date of first Registration:'),
                            $this->_getAttributeNonStore($product, 'registration_date')
                        ]
                    )
                    . $this->_createTableRow(
                        [
                            $this->__('MM-code'),
                            $this->_getAttributeNonStore($product, 'bag_mm_code')
                        ]
                    );

                if ($orderAdditionalData->getIsCompany()) {
                    $html .= $this->_createTableRow(
                        [
                            $this->__('Company Registration Number:'),
                            $orderAdditionalData->getRegistrationNumber(),
                            '',
                            ''
                        ]
                    )
                    . $this->_createTableRow(
                        [
                            $this->__('VAT Number:'),
                            $orderAdditionalData->getVatNumber(),
                            '',
                            ''
                        ]
                    );
                }

                $html .= '</table>';

                if (!empty($financeDataVariables['car_data'])) {
                    foreach ($financeDataVariables['car_data'] as $data) {
                        if (isset($data['group'], $data['items'], self::$_carDataToInclude[$data['group']]) && !empty($data['items'])) {
                            $table = [];

                            foreach ($data['items'] as $item) {
                                $itemCode = isset($item['option_code'])
                                    ? ($item['option_code'] . '-' . $item['id'])
                                    : $item['id'];

                                $table[] = [
                                    $itemCode,
                                    $item['label'],
                                    $this->_formatPrice($item['price'])
                                ];
                            }
                            $html .= '<table><tr class="header"><td colspan="3">' . $data['group'] . '</td></tr>'
                            . array_reduce(
                                $table,
                                function ($label, $value) {
                                    return $label .= '<tr class="three-col"><td>' .
                                        implode('</td><td>', $value) .
                                        '</td></tr>';
                                }
                            ) . '</table>';
                        }
                    }
                }

                if (!empty($financeDataVariables['finance_variables'])) {
                    $table = [];
                    $otpFinanceData = $this->_getOtpFinanceData(
                        $product,
                        $order,
                        $orderItem,
                        $financeDataVariables,
                        $financeDataVariables['finance_variables'],
                        $partExchangeData
                    );

                    foreach ($otpFinanceData as $label => $value) {
                        $table[] = [
                            $label,
                            $value
                        ];
                    }

                    $html .= '<table><tr class="header"><td colspan="2">' . $this->__('Finance Details:') . '</td></tr>'
                    . array_reduce(
                        $table,
                        function ($label, $value) {
                            return $label .= '<tr class="two-col"><td>' . implode('</td><td>', $value) . '</td></tr>';
                        }
                    ) . '</table>';
                }

                if ($partExchangeData) {
                    $table = $this->_buildOtpPxData($partExchangeData);
                    $html .= '<table><tr class="header"><td colspan="2">' . $this->__('Trade in details:') . '</td></tr>'
                    . array_reduce(
                        $table,
                        function ($label, $value) {
                            return $label .= '<tr class="two-col"><td>' . implode('</td><td>', $value) . '</td></tr>';
                        }
                    ) . '</table>';
                }

                $html .= file_get_contents(dirname(__DIR__) . DS . 'template' . DS . 'otp_footer.html');

                file_put_contents($file, $html);
                $pdfFile = $this->getOtpFilePath($incrementId);
                $configuration = file_get_contents(Mage::getBaseDir('skin') . '/frontend/rockar/pdf/vars.js');
                file_put_contents(
                    $confFile,
                    str_replace(
                        [
                            '###Number###',
                            '###DATE###',
                            '###FOOTER_NO###',
                            '###DATETIME###'
                        ],
                        [
                            $incrementId,
                            date('Y/m/d'),
                            $incrementId,
                            date('Y/m/d H:i:s')
                        ],
                        $configuration
                    )
                );

                $result = $this->generate($file, $confFile, $pdfFile, $incrementId);

                // Creating the document record shouldn't interfere with generating the document
                try {
                    Mage::helper('peppermint_sales/document_otp')->createRecord($order->getId(), $incrementId);
                } catch (Exception $e) {
                    Mage::logException($e);
                }

                if ($display) {
                    echo $result;
                } else {
                    return $result;
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Collect customer | company data into array.
     *
     * @param Rockar_Checkout_Model_Order_Additional $orderAdditionalModel
     * @param Mage_Sales_Model_Order_Address $billing
     * @param array $orderVars
     *
     * @return array
     */
    protected function _getCustomerCompanyData($orderAdditionalModel, $billing, $orderVars)
    {
        $result = [];
        $result['driver'] = $orderVars['customer_firstname'] . ' ' . $orderVars['customer_lastname'];
        $result['street'] = $billing->getStreetFull()
            . '<br>' . $billing->getCity()
            . '<br>' . $billing->getCountry()
            . '<br>' . $orderVars['postcode'];

        $result['offerForClient'] = $orderAdditionalModel->getIsCompany()
            ? $orderAdditionalModel->getCompanyName()
            : $result['driver'];

        return $result;
    }

    /**
     * Build Otp Px data.
     * @param array $pxData
     * @return array path to pdf file used in email or download
     */
    protected function _buildOtpPxData($pxData): array
    {
        $equity = $this->_getPxEquityValue($pxData);

        return [
            [
                'Make / Model',
                $pxData['car_model'] ?? ''
            ],
            [
                'Year',
                $pxData['car_year'] ?? ''
            ],
            [
                'Mileage',
                $pxData['car_mileage'] ?? ''
            ],
            [
                'Trade in Offer',
                $this->_formatPrice($pxData['part_exchange_value'] ?? 0)
            ],
            [
                'Equity / Surplus',
                $this->_formatPrice($equity)
            ]
        ];
    }
    /**
     * Calculate Px Equity
     *
     * @param array $pxData
     * @return float
     */
    protected function _getPxEquityValue($pxData)
    {
        return (float) (($pxData['part_exchange_value'] ?? 0) - ($pxData['outstanding_finance'] ?? 0));
    }

    /**
     * Build Otp Finance data.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Sale_Model_Order $order
     * @param Mage_Sale_Model_Order_Item $orderItem
     * @param array $financeData
     * @param array $financeVariablesData
     * @param array $pxData
     *
     * @return array of Finance Variable Data
     */
    protected function _getOtpFinanceData($product, $order, $orderItem, $financeData, $financeVariablesData, $pxData)
    {
        $pxValue = $pxData ? $this->_getPxEquityValue($pxData) : 0;

        $pricingSnapshot = $orderItem->getPricingDetailsSnapshot() ?: [];
        $pricingSnapshot = $this->allHelper
            ->jsonDecode($pricingSnapshot);
        $accessoriesCost = Mage::helper('peppermint_financingoptions/otp')->getAccessoriesTotal($financeData);

        $data = [
            $this->__('Base Price of Vehicle') => $pricingSnapshot['base_price'] ?? ($product->getBasePrice() ?: 0),
            $this->__('Manufacturer Fitted Extra Options') =>
                $pricingSnapshot['options_only_price'] ?? ($product->getOptionsOnlyPrice() ?: 0),
            $this->__('Accessories') => $accessoriesCost,
            $this->__('Manufacturer Support') =>
                $this->_getManufacturerSupport(
                    ($pricingSnapshot['price'] ?? $product->getPrice()) + $accessoriesCost,
                    $orderItem
                ) * -1 ?: 0,
            $this->__('Co2 Emission tax') => $pricingSnapshot['co2_tax'] ?? ($product->getCo2Tax() ?: 0),
            $this->__('Total Price (Incl VAT)') => $order->getBaseGrandTotal() ?: 0,
            $this->__('Deposit') => $this->_getFinanceDataFromVariablesData(
                $financeVariablesData,
                'cash_deposit'
            ) * -1,
            $this->__('Secondary Deposit') => $this->_getFinanceDataFromVariablesData(
                $financeVariablesData,
                'px_settlement_payment'
            ) * -1,
            $this->__('Trade Equity') => ($pxValue * -1),
            $this->__(' - Refunded To Customer') => $financeData['cashback'] ?? 0,
            $this->__('Trade-in Support') => $this->_getFinanceDataFromVariablesData(
                $financeVariablesData,
                'shortfall_applied'
            ) * -1,
            $this->__('Finance Amount (Incl VAT)') => $this->_getFinanceAmount($financeData, $financeVariablesData, $pxValue),
            $this->__('Trade Assist Loan Value included above') => $this->_getFinanceDataFromVariablesData(
                $financeVariablesData,
                'px_settlement_creditamount'
            )
        ];

        return array_map(
            function ($value) {
                return $this->_formatPrice($value);
            },
            $data
        );
    }

    /**
     * Get Finance Amount
     *
     * @param array $financeData
     * @param array $variableData
     * @param float $pxValue
     *
     * @return int|float
     */
    protected function _getFinanceAmount($financeData, $variableData, $pxValue)
    {
        $helper = Mage::helper('peppermint_financingoptions/otp');

        return $helper->isPayInFull($financeData)
            ? $helper->getPayInFullFinanceAmount($financeData, $pxValue)
            : $this->_getFinanceDataFromVariablesData($variableData, 'amount_of_credit');
    }

    /**
     * Get manufacturer support
     *
     * @param int $productPrice
     * @param Mage_Sale_Model_Order_Item $orderItem
     *
     * @return float
     */
    protected function _getManufacturerSupport($productPrice, $orderItem)
    {
        return $productPrice - $orderItem->getPrice() + $orderItem->getDiscountAmount();
    }

    /**
     * Get Finance Variable Data from $order Finance Varibles.
     * @param array $variableData
     * @param string $variableValue
     * @return integer|string
     */
    protected function _getFinanceDataFromVariablesData($variableData, $variableValue)
    {
        foreach ($variableData as $value) {
            if ($value['variable'] === $variableValue) {
                $result = $value['value'] ?? 0;
                break;
            }
        }

        return $result ?? 0;
    }

    /**
     * Get Attribute value base on store.
     * @param object $model
     * @param string $storeId
     * @param string $attribute
     * @return integer|string
     */
    protected function _getAttributePerStore($model, $storeId, $attribute)
    {
        return $model->getResource()
            ->getAttribute($attribute)
            ->setStoreId($storeId ?: 0)
            ->getFrontend()
            ->getValue($model);
    }

    /**
     * Get Attribute value base on store.
     * @param object $model
     * @param string $attribute
     * @return integer|string
     */
    protected function _getAttributeNonStore($model, $attribute)
    {
        return $model->getResource()
            ->getAttribute($attribute)
            ->getFrontend()
            ->getValue($model);
    }

    /**
     * return OTP file path.
     *
     * @param string $incrementId
     *
     * @return string
     */
    public function getOtpFilePath($incrementId, $directory = 'orders')
    {
        return Mage::getBaseDir('var') . DS . $directory . DS . $incrementId . '.pdf';
    }

    /**
     * Format to price and change currency symbol
     *
     * @param string|float $value
     *
     * @return string
     */
    protected function _formatPrice($value)
    {
        return str_replace('ZAR', 'R', Mage::helper('core')->formatPrice($value, false));
    }

    /**
     * Function for generating a table row string from an array of elements.
     * If array element is an array then:
     * 'data' key holds the cell text and other keys can be used as html attributes
     *
     * @param array $rowData
     * @return string
     */
    protected function _createTableRow($rowData)
    {
        $result = '<tr>';

        foreach ($rowData as $cell) {
            if (is_array($cell)) {
                $result .= '<td';

                foreach ($cell as $key => $value) {
                    if ($key !== 'data') {
                        $result .= ' ' . $key . '="' . $value . '"';
                    }
                }

                $result .= '>' . $cell['data'] . '</td>';
            } else {
                $result .= '<td>' . $cell . '</td>';
            }
        }

        $result .= '</tr>';

        return $result;
    }

    /**
     * Function to get customer group code
     *
     * @param int $groupId
     * @return string
     */
    protected function _getCustomerGroupCode($groupId)
    {
        return Mage::getModel('customer/group')->getCollection()
            ->addFieldToSelect('customer_group_code')
            ->addFieldToFilter('customer_group_id', $groupId)
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem()
            ->getCustomerGroupCode() ?: '';
    }
}
