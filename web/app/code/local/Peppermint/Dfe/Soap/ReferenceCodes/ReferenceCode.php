<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode
{
    /**
     * @var string $System
     */
    protected $System = null;

    /**
     * @var string $Category
     */
    protected $Category = null;

    /**
     * @var string $RefCode
     */
    protected $RefCode = null;

    /**
     * @var string $Description
     */
    protected $Description = null;

    /**
     * @return string
     */
    public function getSystem()
    {
        return $this->System;
    }

    /**
     * @param string $system
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode
     */
    public function setSystem($system)
    {
        $this->System = $system;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->Category;
    }

    /**
     * @param string $category
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode
     */
    public function setCategory($category)
    {
        $this->Category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefCode()
    {
        return $this->RefCode;
    }

    /**
     * @param string $refCode
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode
     */
    public function setRefCode($refCode)
    {
        $this->RefCode = $refCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param string $description
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode
     */
    public function setDescription($description)
    {
        $this->Description = $description;

        return $this;
    }

    /**
     * Prepares the data as model resource expects it
     *
     * @return []
     */
    public function getData()
    {
        return [
            'category' => $this->getCategory(),
            'code' => $this->getRefCode(),
            'description' => $this->getDescription()
        ];
    }
}
