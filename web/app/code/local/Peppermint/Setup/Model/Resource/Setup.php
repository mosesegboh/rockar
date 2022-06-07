<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Setup_Model_Resource_Setup extends Rockar_Setup_Model_Resource_Setup
{
    /**
     * Ensure newly added ACL is disabled for existing roles when added
     * @param string $aclPath
     * @return void
     */
    public function disableNewAcl(string $aclPath): void
    {
        $ruleModel = Mage::getModel('admin/rules');
        $adminRoleName = Mage::helper('peppermint_all/config')->getAdministratorRoleName();
        $groupRole = Mage_Admin_Model_Acl::ROLE_TYPE_GROUP;
        $permissionDenied = Mage_Admin_Model_Rules::RULE_PERMISSION_DENIED;

        $roleCollection = Mage::getModel('admin/role')->getCollection()
            ->addFieldToFilter('role_name', ['neq' => $adminRoleName])
            ->addFieldToFilter('parent_id', 0)
            ->addFieldToSelect('role_id');

        foreach ($roleCollection as $role) {
            $roleId = $role->getId();

            $entry = $ruleModel->getCollection()
                ->addFieldToFilter('role_id', $roleId)
                ->addFieldToFilter('resource_id', $aclPath)
                ->addFieldToFilter('role_type', $groupRole)
                ->getFirstItem();

            $entry->addData([
                    'role_id'     => $roleId,
                    'resource_id' => $aclPath,
                    'priveleges'  => null,
                    'assert_id'   => 0,
                    'role_type'   => $groupRole,
                    'permission'  => $permissionDenied
                ])
                ->save();
        }
    }
}