<?php
/**
 * @category  Peppermint
 * @package   Peppermint_All
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_All_Helper_Config extends Mage_Core_Helper_Abstract
{
    const ADMIN_USER_ADMINISTRATOR_ROLE = 'Administrators';

    /**
     * Get Administrator Role Name
     * @return string
     */
    public function getAdministratorRoleName(): string
    {
        return self::ADMIN_USER_ADMINISTRATOR_ROLE;
    }
}
