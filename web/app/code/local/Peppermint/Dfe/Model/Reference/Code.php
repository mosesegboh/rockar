<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Model_Reference_Code extends Mage_Core_Model_Abstract
{
    /**
     * @var string[] $_allowedSystems a collection of whitelisted foreign systems
     */
    protected $_allowedSystems = ['DFE'];

    protected function _construct()
    {
        $this->_init('peppermint_dfe/reference_code');
    }

    /**
     * Performs database sync based on the provided collection of foreign ref codes
     *
     * @param Peppermint_Dfe_Soap_ReferenceCodes_ArrayOfReferenceCode[] $referenceCodes
     * @return Peppermint_Dfe_Model_Reference_Code
     */
    public function sync(Peppermint_Dfe_Soap_ReferenceCodes_ArrayOfReferenceCode $referenceCodes)
    {
        $referenceCodes->rewind();
        $rows = [];
        while ($referenceCodes->valid()) {
            $referenceCode = $referenceCodes->current();
            if (in_array($referenceCode->getSystem(), $this->_allowedSystems)) {
                $rows[] = array_merge($referenceCode->getData(), ['is_deleted' => 0]);
            }
            $referenceCodes->next();
        }
        if (!empty($rows)) {
            $this->getResource()->sync($rows);
        }

        return $this;
    }
}
