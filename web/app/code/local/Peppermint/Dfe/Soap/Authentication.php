<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Authentication
{
    /**
     * @var string $app_username
     */
    protected $APP_USERNAME = null;

    /**
     * @var string $app_password
     */
    protected $APP_PASSWORD = null;

    /**
     * @return string
     */
    public function getAppUsername()
    {
        return $this->APP_USERNAME;
    }

    /**
     * @param string $appUsername
     * @return Peppermint_Dfe_Soap_Authentication
     */
    public function setAppUsername($appUsername)
    {
        $this->APP_USERNAME = $appUsername;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppPassword()
    {
        return $this->APP_PASSWORD;
    }

    /**
     * @param string $appPassword
     * @return Peppermint_Dfe_Soap_Authentication
     */
    public function setAppPassword($appPassword)
    {
        $this->APP_PASSWORD = $appPassword;

        return $this;
    }
}
