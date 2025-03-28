<?php

require_once('include/utils/utils.php');

class GroupManager {

    public static function createGroupIfNotExists(string $groupName, string $groupDescription, array $groupType, array $roleIds = []) {
        global $adb;

        error_log("\ud83d\udc65 Creating group if not exists: $groupName");
        // Check if the group already exists
        $result = $adb->pquery("SELECT groupid FROM vtiger_groups WHERE groupname = ?", [$groupName]);
        if ($adb->num_rows($result) > 0) {
            $groupId = $adb->query_result($result, 0, 'groupid');
            error_log("\u26a0\ufe0f Group '$groupName' already exists with ID $groupId. Updating description and members.");
            $adb->pquery("UPDATE vtiger_groups SET description = ? WHERE groupid = ?", [
                $groupDescription,
                $groupId
            ]);

            // Clear existing members
            $adb->pquery("DELETE FROM vtiger_group2role WHERE groupid = ?", [$groupId]);
            $adb->pquery("DELETE FROM vtiger_group2rs WHERE groupid = ?", [$groupId]);

        } else {
            // Create the group
            $groupId = $adb->getUniqueID("vtiger_groups");

            $adb->pquery("INSERT INTO vtiger_groups (groupid, groupname, description) VALUES (?, ?, ?)", [
                $groupId,
                $groupName,
                $groupDescription
            ]);
        }

       // Add group members
        if (in_array("role", $groupType)) {
            foreach ($roleIds as $roleId) {
                $adb->pquery("INSERT INTO vtiger_group2role (groupid, roleid) VALUES (?, ?)", [
                    $groupId,
                    $roleId
                ]);
                error_log("\u2705 Role '$roleId' added to group '$groupName' in vtiger_group2role");
            }
        }
        if (in_array("role and subordinates", $groupType)) {
            foreach ($roleIds as $roleId) {
                $adb->pquery("INSERT INTO vtiger_group2rs (groupid, roleandsubid) VALUES (?, ?)", [
                    $groupId,
                    $roleId
                ]);
                error_log("\u2705 Role '$roleId' added to group '$groupName' in vtiger_group2rs");
            }
        }

        error_log("\u2705 Group '$groupName' created with ID $groupId");
    }
}
