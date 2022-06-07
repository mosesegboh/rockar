<?php
/**
 * @category Peppermint
 * @package Peppermint_Customer
 * @author Mihai Chezan <mihai.chezan@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Customer_Helper_Data extends Rockar_Customer_Helper_Data
{
    /**
     * Constants for registration status
     */
    public const REGISTERED_ONLINE = 0;
    public const REGISTERED_IN_STORE = 1;

    /**
     * Constant for available order statuses as an array
     */
    public const ORDER_STATES = [
        Mage_Sales_Model_Order::STATE_CANCELED,
        Mage_Sales_Model_Order::STATE_CLOSED,
        Mage_Sales_Model_Order::STATE_COMPLETE
    ];

    /**
     * Get available south african document types.
     *
     * @return string
     */
    public function getSouthAfricanDocumentTypesJson()
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'south_african_document_type');

        return Mage::helper('rockar_all')->jsonEncode(
            $attribute->getSource()->getAllOptions()
        );
    }

    /**
     * Delete entry in the `peppermint_gcdm_customer_profile` table if exists
     *
     * @param int $customerId
     *
     * @return void
     */
    public function clearCustomerGuidData($customerId)
    {
        $profileModel = Mage::getModel('peppermint_gcdm/customer_profile')->load($customerId);

        if ($profileModel->getId()) {
            Mage::getResourceModel('peppermint_gcdm/customer_profile')->delete($profileModel);
        }
    }

    /**
     * Generates dropdown labels and values
     *
     * @return array
     */
    public function getRegistrationValues()
    {
        return [
            0 => $this->__('Online'),
            1 => $this->__('In-store')
        ];
    }

    /**
     * Passes customer account edit field for registration place
     *
     * @return array
     */
    public function getRegistrationField()
    {
        return [
            'label' => $this->__('Account Created'),
            'name' => 'registered_in',
            'values' => $this->getRegistrationValues(),
            'disabled' => true
        ];
    }

    /**
     * Get available ZA identification document type options
     *
     * @return array
     */
    public function getIdentificationTypes()
    {
        $identificationTypes = [];

        foreach (Mage::getSingleton('eav/config')->getAttribute('customer', 'south_african_document_type')
                     ->getSource()->getAllOptions(false) as $option) {
            $identificationTypes[$option['value']] = $option['label'];
        }

        return $identificationTypes;
    }

    /**
     * @param $lookupPath, path to get the collection from mage get model
     * resulting object should extend from LookupAbstract
     *
     * @returns JSON string representing the data contained within the object.
     */
    public function getLookupForDropDown($lookupPath)
    {
        $collection = Mage::getModel($lookupPath)->getCollection();
        $toReturn = [];

        foreach ($collection as $item) {
            $toReturn[] = [
                'value' => $item->getId(),
                'title' => $item->getName()
            ];
        }

        return Mage::helper('rockar_all')->jsonEncode($toReturn);
    }

    /**
     * all document finance types as required by the front end.
     *
     * @return mixed
     */
    public function getDocumentFinanceType()
    {
        return Mage::helper('rockar_all')->jsonEncode(
            Mage::getModel('peppermint_customer/document_finance_type')->getCollection()
                ->load()
        );
    }

    /**
     * Method to return all required documents given a customer group and a finance group
     *
     * @param $custGroupId Integer|String Customer group as contained in the peppermint_document_customer_group table
     * @param $financeGroupId Integer|String Finance type as contained in the peppermint_document_finance_group table
     * @param $customerId Integer|String Customers identity as contained in the session object.
     *
     * @return object Collection of documents required for the combination of customer and finance group
     */
    public function getDocumentDisplayGrid($custGroupId, $financeGroupId, $customerId)
    {
        return $this->generateFinanceTypeCollection($customerId, $custGroupId, $financeGroupId);
    }

    /**
     * This method returns the default list based on the latest order this customer has placed.
     *
     * @param $customerId String|Integer customers identity
     * @param $documentId String|Integer document id we are trying to associate to an order.
     *
     * @return object Collection containing required data for the frontend.
     */
    public function getFirstOrderByCustomer($customerId, $documentId = null)
    {
        $topOneOrder = Mage::getSingleton('sales/order')->getCollection();
        $topOneOrder->getSelect()
            ->join(
                ['rfo' => $topOneOrder->getTable('rockar_financingoptions/options')],
                'rfo.type = main_table.finance_payment_type'
            )
            ->joinLeft(
                ['roa' => $topOneOrder->getTable('rockar_checkout/order_additional')],
                'roa.order_id = main_table.entity_id'
            );

        $topOneOrder
            ->addFieldToFilter('main_table.customer_id', $customerId)
            ->addFieldToFilter('main_table.state', ['nin' => self::ORDER_STATES]);

        if ($documentId) {
            $topOneOrder->getSelect()
                ->join(
                    ['rcd' => $topOneOrder->getTable('rockar_customer/documents')],
                    'rcd.customer_id = main_table.customer_id'
                );

            $topOneOrder->addFieldToFilter('rcd.entity_id', $documentId);
        }

        $topOneOrder->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns([
                'order_id' => 'main_table.entity_id',
                'order_increment_number' => 'main_table.increment_id',
                'is_company' => 'IF(isnull(is_company) = 1, 0, is_company)',
                'is_pay_in_full' => 'rfo.pay_in_full'
            ])
            ->order('main_table.created_at desc')
            ->limit(1);

        return $topOneOrder;
    }

    /**
     * This method returns the default list based on the latest order this customer has placed.
     * @param $customerId String|Integer customers identity
     * @return object Collection containing required data for the frontend.
     */
    public function getDefaultDocumentList($customerId)
    {
        return $this->generateFinanceTypeCollection($customerId);
    }

    /**
     * Helper method so logic is not duplicated.
     *
     * @param $customerId String|Integer the users id as contained in the session variable.
     * @param $customerGroupId String|Integer the filter used to create the select statement,
     * which customer group am i referring too? (company, private)
     * @param $financeGroupId String|Integer the filter used to create the select statement,
     * Which finance group, (financed or cash)
     * @return object Collection containing required data for the frontend.
     */
    private function generateFinanceTypeCollection($customerId, $customerGroupId = false, $financeGroupId = false)
    {
        $collection = Mage::getModel('peppermint_customer/document_finance_type')->getCollection();
        $collection->getSelect()
            ->join(
                ['fin_group' => $collection->getTable('peppermint_customer/document_finance_group')],
                'fin_group.id = main_table.finance_group_id'
            )
            ->join(
                ['doc_type' => $collection->getTable('peppermint_customer/document_type')],
                'doc_type.id = main_table.document_type_id'
            )
            ->join(
                ['cust' => $collection->getTable('peppermint_customer/document_customer_group')],
                'cust.id = main_table.applicable_to_customer_group_id'
            )
            //this join may seem counter intuitive but take a moment....
            //when a document type is uploaded for a customer it should be applicable to all application types
            //where that document appears.
            //so for cash or other id document is required lets upload once instead of twice.
            ->joinLeft(
                ['document' => $collection->getTable('rockar_customer/documents')],
                'document.document_type_id = main_table.document_type_id and document.customer_id = '.$customerId
            );
        //three scenarios to cater for
        //1. do we have a customergroup and finance group? -- this makes it simple.
        //2. No customer/finance groups, here we need to load the last order for the customer. To identify the groups
        //3. Nothing at all, here we load the last item uploaded by the customer if it exists, or return an empty collection
        if ($customerGroupId && $financeGroupId) {
            $collection->addFilter('fin_group.id', $financeGroupId)
                ->addFilter('cust.id', $customerGroupId);
        } else if (Mage::helper('peppermint_checkout/customer')->getCustomerActiveOrders()) {
            $collection->getSelect()
                ->join(
                    ['tmp' => $this->getFirstOrderByCustomer($customerId)->getSelect()],
                    'tmp.is_pay_in_full = fin_group.is_pay_in_full and tmp.is_company = cust.is_company'
                );
        } else {
            //no active order, lets grab the last document that was uploaded instead
            $rockarDocuments = Mage::getModel('rockar_customer/documents')->getCollection();
            $rockarDocuments->getSelect()
                ->join(
                    ['tmp_fin_type' => $collection->getTable('peppermint_customer/document_finance_type')],
                    'main_table.document_finance_type_id = tmp_fin_type.id'
                )
                ->join(
                    ['tmp_fin_group' => $collection->getTable('peppermint_customer/document_finance_group')],
                    'tmp_fin_group.id = tmp_fin_type.finance_group_id'
                )
                ->join(
                    ['tmp_cust_group' => $collection->getTable('peppermint_customer/document_customer_group')],
                    'tmp_cust_group.id = tmp_fin_type.applicable_to_customer_group_id'
                )
                ->where(
                    'main_table.customer_id = ?',
                    $customerId
                )
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(
                    [
                        'is_pay_in_full' => 'tmp_fin_group.is_pay_in_full',
                        'is_company' => 'tmp_cust_group.is_company'
                    ]
                )
                ->group(['tmp_fin_group.is_pay_in_full', 'tmp_cust_group.is_company'])
                ->order('max(main_table.date) desc')
                ->limit(1);

            $result = $rockarDocuments->getFirstItem()->toArray();

            //if either has a value lets use the filter, otherwise return empty collection.
            if (!is_null($result['is_pay_in_full']) || !is_null($result['is_company'])) {
                $collection->getSelect()
                    ->join(
                        ['tmp' => $rockarDocuments->getSelect()],
                        'tmp.is_pay_in_full = fin_group.is_pay_in_full and tmp.is_company = cust.is_company'
                    );
            } else {
                return $collection->addFieldToFilter('main_table.id', 0);
            }
        }

        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(['groupName' => 'fin_group.name',
                'documentTypeName' => 'doc_type.name',
                'customerName' => 'cust.name',
                'financeType' => 'main_table.id',
                'documentId' => 'document.entity_id',
                'documentTypeId' => 'doc_type.id',
                'allowMultipleUploads' => 'doc_type.allow_multiple_uploads',
                'dateUploaded' => 'document.date',
                'documentName' => 'COALESCE(document.initial_filename, document.filename)',
                'groupId' => 'fin_group.id',
                'customerId' => 'cust.id',
                'financeDocumentId' => 'doc_type.finance_document_type_id'
            ]);

        return $collection;
    }

    /**
     * Gets the maximum file upload size
     *
     * @return integer
     */
    public function getMaxFileSize()
    {
        $maxPostSize = $this->_convertIniToInteger(trim(ini_get('post_max_size')));
        $maxFileSize = $this->_convertIniToInteger(trim(ini_get('upload_max_filesize')));

        return max($maxPostSize, $maxFileSize);
    }


    /**
     * Converts a ini setting to a integer value
     *
     * @param  string $setting
     * @return integer
     */
    protected function _convertIniToInteger($setting)
    {
        if (!is_numeric($setting)) {
            $type = strtoupper(substr($setting, -1));
            $setting = (int)substr($setting, 0, -1);

            switch ($type) {
                case 'K' :
                    $setting *= 1024;
                    break;

                case 'M' :
                    $setting *= 1024 * 1024;
                    break;

                case 'G' :
                    $setting *= 1024 * 1024 * 1024;
                    break;

                default :
                    break;
            }
        }

        return (int)$setting;
    }

    /**
     * Retrieves the required details to be sent to financial services.
     *
     * @param $documentId integer as stored in peppermint_customer_document_order
     *
     * @returns array collection of data as required by financial services.
     */
    public function getDetailsForFinancialServices($documentId)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $resource = Mage::getSingleton('core/resource');
        $docOrders = Mage::getModel('peppermint_customer/document_order')->getCollection();
        $toReturn = [];

        $docOrders->getSelect()
            ->join(
                ['rcd' => $resource->getTableName('rockar_customer/documents')],
                'rcd.entity_id = main_table.document_id'
            )
            ->join(
                ['sfo' => $resource->getTableName('sales_flat_order')],
                'sfo.entity_id = main_table.order_id'
            )
            ->join(
                ['finType' => $resource->getTableName('peppermint_customer/document_finance_type')],
                'finType.id = rcd.document_finance_type_id'
            )
            ->join(
                ['type' => $resource->getTableName('peppermint_customer/document_type')],
                'type.id = finType.document_type_id'
            )
            ->joinLeft(
                ['cev' => $resource->getTableName('customer_entity_varchar')],
                sprintf(
                    'cev.entity_id = sfo.customer_id and cev.attribute_id = %s',
                    $customer->getAttribute('south_african_id_number')->getId()
                )
            )
            ->joinLeft(
                ['cei' => $resource->getTableName('customer_entity_int')],
                sprintf(
                    'cei.entity_id = sfo.customer_id and cei.attribute_id = %s',
                    $customer->getAttribute('south_african_document_type')->getId()
                )
            )
            ->joinLeft(
                ['eaov' => $resource->getTableName('eav_attribute_option_value')],
                'eaov.option_id = cei.value'
            )
            ->joinLeft(
                ['additional' => $resource->getTableName('rockar_checkout/order_additional')],
                'additional.order_id = main_table.order_id'
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(['document_id' => 'main_table.document_id',
                'order_increment' => 'coalesce(sfo.original_increment_id, sfo.increment_id)',
                'customer_id' => 'rcd.customer_id',
                'sf_finance_type' => 'type.finance_document_type_id',
                'customer_email' => 'rcd.customer_email',
                'order_id' => 'main_table.order_id',
                'current_date' => 'now()',
                'identification_number' => 'cev.value',
                'identification_type' => 'eaov.value',
                'company_registration' => 'additional.registration_number',
                'credit_app_id' => 'sfo.credit_app_id',
                'credit_app_status' => 'sfo.credit_app_status'
            ])
            ->where('main_table.document_id = ?', $documentId)
            ->limit(1);

        foreach ($docOrders as $item) {
            $toReturn[] = $item;
        }

        return $toReturn[0] ?? false;
    }
}
