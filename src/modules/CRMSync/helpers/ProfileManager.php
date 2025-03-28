<?php

require_once('include/utils/utils.php');
require_once('modules/Users/CreateUserPrivilegeFile.php');
require_once 'modules/CRMSync/models/UserConfig.php';

class ProfileManager {

    public static function initializeProfile() {
        global $adb;

        error_log("\ud83d\udc64 Initializing profile creation");
        $profileData = UserConfig::getProfile();

        if (!$profileData) {
            error_log("\u26a0\ufe0f No profile data found in UserConfig.");
            return;
        }

        $profileName = $profileData['name'];
        error_log("\ud83d\udc64 Profile name: $profileName");

        // Check if the profile already exists
        $result = $adb->pquery("SELECT profileid FROM vtiger_profile WHERE profilename = ?", [$profileName]);
        if ($adb->num_rows($result) > 0) {
            $profileId = $adb->query_result($result, 0, 'profileid');
            error_log("\u26a0\ufe0f Profile '$profileName' already exists with ID $profileId. Updating profile.");

            $profileDescription = $profileData['description'] ?? '';
            $adb->pquery("UPDATE vtiger_profile SET description = ? WHERE profileid = ?", [
                $profileDescription,
                $profileId
            ]);

            error_log("\u2705 Profile '$profileName' updated with ID $profileId");

            // Delete existing permissions
            $adb->pquery("DELETE FROM vtiger_profile2standardpermissions WHERE profileid = ?", [$profileId]);
            error_log("\u2705 Existing permissions deleted for profile '$profileName'");

        } else {
            // Create the profile
            $profileId = $adb->getUniqueID("vtiger_profile");
            $profileDescription = $profileData['description'] ?? '';
            error_log("\ud83d\udc64 Creating profile with ID: $profileId, name: $profileName, description: $profileDescription");

            $adb->pquery("INSERT INTO vtiger_profile (profileid, profilename, description) VALUES (?, ?, ?)", [
                $profileId,
                $profileName,
                $profileDescription
            ]);

            error_log("\u2705 Profile '$profileName' created with ID $profileId");
        }

        // Handle module permissions
        if (!empty($profileData['modules'])) {
            foreach ($profileData['modules'] as $moduleData) {
                $moduleName = $moduleData['name'];
                error_log("\ud83d\udc64 Handling module permissions for module: $moduleName");

                $tabId = getTabid($moduleName);
                if (!$tabId) {
                    error_log("\u274c Tab ID not found for module: $moduleName");
                    continue;
                }

                if (!empty($moduleData['permissions'])) {
                    foreach ($moduleData['permissions'] as $permissionName) {
                        $operation = self::permissionToOperation($permissionName);

                        $adb->pquery("INSERT INTO vtiger_profile2standardpermissions (profileid, tabid, operation, permissions) VALUES (?, ?, ?, ?)", [
                            $profileId,
                            $tabId,
                            $operation,
                            0 // 0 = allowed, 1 = denied
                        ]);

                        error_log("\u2705 Permission '$permissionName' set for module '$moduleName' for profile '$profileName'");
                    }
                }

                // Handle field permissions
                if (!empty($moduleData['fields'])) {
                    foreach ($moduleData['fields'] as $fieldData) {
                        $fieldName = $fieldData['name'];
                        $permission = $fieldData['permissions'] ?? 'Read Write'; // Default permission
                        error_log("\ud83d\udc64 Setting field permission for field: $fieldName, permission: $permission");

                        $fieldInfo = getFieldid($tabId, $fieldName);
                        if (!$fieldInfo) {
                            error_log("\u274c Field ID not found for field: $fieldName in module: $moduleName");
                            continue;
                        }

                        $visibility = match(strtolower($permission)) {
                            'read only' => 1,
                            'invisible' => 2,
                            default => 0 // Read-Write
                        };

                        $adb->pquery("INSERT INTO vtiger_profile2field (profileid, tabid, fieldid, visible, readonly) VALUES (?, ?, ?, ?, ?)", [
                            $profileId,
                            $tabId,
                            $fieldInfo,
                            ($visibility != 2 ? 0 : 1),
                            ($visibility == 1 ? 1 : 0)
                        ]);

                        error_log("\u2705 Field '$fieldName' in module '$moduleName' set to '$permission' for profile '$profileName'");
                    }
                }
            }
        }
        error_log("\u2705 Profile initialization completed");
    }

    private static function permissionToOperation(string $permName): int {
        return match($permName) {
            'EditView' => 1,                // Edit
            'Delete' => 2,                  // Delete
            'DetailView' => 4,             // View (detalle)
            'Import' => 5,                  // Import
            'Export' => 6,                  // Export
            'CreateView' => 7,      // PBX phone actions
            default => 4                   // Fallback: Import (o podrías lanzar una excepción)
        };
    }
}
