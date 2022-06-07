<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Soap_ReferenceCodesService extends Peppermint_Dfe_Helper_Soap_Abstract_Service
{
    /**
     * @var [] The defined classes
     */
    protected static $_classmap = [
        'GetReferenceCodes' => 'Peppermint_Dfe_Soap_ReferenceCodes_GetReferenceCodes',
        'GetReferenceCodesResponse' => 'Peppermint_Dfe_Soap_ReferenceCodes_GetReferenceCodesResponse',
        'ArrayOfReferenceCode' => 'Peppermint_Dfe_Soap_ReferenceCodes_ArrayOfReferenceCode',
        'ReferenceCode' => 'Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode'
    ];

    /**
     * @param Peppermint_Dfe_Soap_ReferenceCodes_GetReferenceCodes $parameters
     * @return Peppermint_Dfe_Soap_ReferenceCodes_GetReferenceCodesResponse
     */
    public function getReferenceCodes(Peppermint_Dfe_Soap_ReferenceCodes_GetReferenceCodes $parameters)
    {
        return $this->__soapCall('GetReferenceCodes', [$parameters]);
    }
}
