<?php

// SystemManager.php
require_once('include/utils/utils.php');
require_once('modules/Users/CreateUserPrivilegeFile.php');
require_once 'modules/HelloWorld/models/UserConfig.php';
require_once 'modules/HelloWorld/helpers/RoleManager.php';
require_once 'modules/HelloWorld/helpers/ProfileManager.php';
require_once 'modules/HelloWorld/helpers/GroupManager.php';
require_once 'modules/HelloWorld/helpers/UserManager.php';

class SystemManager {
    
    public static function initializeUsers() {
        global $adb;

        error_log("\ud83d\ude80 Ejecutando SystemManager::initializeUsers()");

        // Check if the default profile exists
        $result = $adb->pquery("SELECT id FROM vtiger_profile WHERE profilename = ?", ['Testing Profile']);
        if ($adb->num_rows($result) > 0) {
            error_log("\u26a0\ufe0f SystemManager already initialized. Skipping initialization.");
            return;
        }

        try {
            if (isset(self::$config['profile'])) {
                if (!is_array(self::$config['profile'])) {
                    $errorMessage = "JSON config 'profile' must be an array.";
                    error_log("\u274c Error: $errorMessage");
                    throw new Exception($errorMessage);
                }
                
                ProfileManager::initializeProfile();
            }

            $roles = UserConfig::getRoles();
            if ($roles) {
                foreach ($roles as $roleData) {
                    RoleManager::createRoleRecursive($roleData, null);
                }
            }

        } catch (Exception $e) {
            error_log("\u274c Error en SystemManager::initializeUsers(): " . $e->getMessage());
        }
    }
}
