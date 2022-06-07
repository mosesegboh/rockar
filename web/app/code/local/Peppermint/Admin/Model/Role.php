<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Model_Role extends Mage_Core_Model_Abstract
{
    /**
     * Peppermint_Admin_Model_Role constructor.
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_admin/role');
    }

    /**
     * Processing object before save data.
     *
     * @return $this|Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();

        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);

        return $this;
    }

    /**
     * Set default status value.
     *
     * @return array
     */
    public function getDefaultValues()
    {
        return ['status' => 1];
    }

    /**
     * Role and Email validations.
     *
     * @param string $role
     * @return boolean
     */
    public function validateRole($role)
    {
        return isset($this->getRoleCollection($role)[$role]);
    }

    /**
     * Collect all active roles.
     *
     * @param null|string $role
     * @return array
     */
    public function getRoleCollection($role = null)
    {
        $roleCollection = $this->getCollection()
            ->addFieldToFilter('status', ['eq' => 1])
            ->getData();

        $roles = [];

        foreach ($roleCollection as $key => $data) {
            $roles[$data['role']] = $data;
        }

        return $roles;
    }
}
