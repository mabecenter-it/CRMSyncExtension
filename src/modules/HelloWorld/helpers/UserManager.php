<?php

// UserManager.php
require_once('include/utils/utils.php');
require_once('modules/Users/CreateUserPrivilegeFile.php');
require_once 'modules/HelloWorld/models/UserConfig.php';

class UserManager {

    public static function createUserIfNotExists(array $userData, string $roleId) {
        error_log("Por lo menos entré a la función");

        global $adb;

        error_log("\ud83d\udc64 Creating user if not exists: " . $userData['user_name']);
        $username = $userData['user_name'];
        $result = $adb->pquery("SELECT id FROM vtiger_users WHERE user_name = ?", [$username]);

        if ($adb->num_rows($result) > 0) {
            error_log("\u26a0\ufe0f Usuario '$username' ya existe. Actualizando usuario.");
            $userId = $adb->query_result($result, 0, 'id');
            self::updateUser($userId, array_merge($userData, ['roleid' => $roleId]));
            return;
        }

        try {
            $userId = self::createUser(array_merge($userData, ['roleid' => $roleId]));
            error_log("\u2705 Usuario '{$userData['first_name']} {$userData['last_name']}' creado con ID: $userId");
        } catch (Exception $e) {
            error_log("\u274c Error creating user: " . $e->getMessage());
        }
    }

    private static function updateUser(string $userId, array $fields) {
        $user = CRMEntity::getInstance('Users');
        $user->retrieve_entity_info($userId, "Users");

        $hasChanged = false;
        foreach ($fields as $key => $value) {
            if ($user->column_fields[$key] != $value) {
                $user->column_fields[$key] = $value;
                $hasChanged = true;
            }
        }

        if ($hasChanged) {
            $user->id = $userId;
            $user->mode = 'edit';
            $user->save("Users");

            createUserPrivilegesfile($user->id);
            createUserSharingPrivilegesfile($user->id);

            error_log("\u2705 Usuario con ID: $userId actualizado.");
        } else {
            error_log("\u26a0\ufe0f Usuario con ID: $userId no ha cambiado. Omitiendo actualización.");
        }
    }

    private static function createUser(array $fields): string {
        $user = CRMEntity::getInstance('Users');
        foreach ($fields as $key => $value) {
            $user->column_fields[$key] = $value;
        }

        $user->save("Users");

        createUserPrivilegesfile($user->id);
        createUserSharingPrivilegesfile($user->id);

        return $user->id;
    }
}
