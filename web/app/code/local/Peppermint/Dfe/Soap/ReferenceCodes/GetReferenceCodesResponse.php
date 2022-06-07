<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_ReferenceCodes_GetReferenceCodesResponse
{
    /**
     * @var Peppermint_Dfe_Soap_ReferenceCodes_ArrayOfReferenceCode $GetReferenceCodesResult
     */
    protected $GetReferenceCodesResult = null;

    /**
     * @param Peppermint_Dfe_Soap_ReferenceCodes_ArrayOfReferenceCode $getReferenceCodesResult
     */
    public function __construct($getReferenceCodesResult)
    {
        $this->GetReferenceCodesResult = $getReferenceCodesResult;
    }

    /**
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ArrayOfReferenceCode
     */
    public function getGetReferenceCodesResult()
    {
        return $this->GetReferenceCodesResult;
    }

    /**
     * @param Peppermint_Dfe_Soap_ReferenceCodes_ArrayOfReferenceCode $getReferenceCodesResult
     * @return Peppermint_Dfe_Soap_ReferenceCodes_GetReferenceCodesResponse
     */
    public function setGetReferenceCodesResult($getReferenceCodesResult)
    {
        $this->GetReferenceCodesResult = $getReferenceCodesResult;

        return $this;
    }
}
