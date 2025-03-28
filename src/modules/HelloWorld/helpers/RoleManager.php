<?php

require_once 'modules/HelloWorld/helpers/UserManager.php';
require_once 'modules/HelloWorld/helpers/GroupManager.php';

class RoleManager {

    public static function createRoleRecursive(array $roleData, ?string $parentRoleId) {
        global $adb;

        error_log("\ud83d\udc65 Creando rol recursivamente: " . $roleData['name']);
        $roleName = $roleData['name'];

        error_log("\ud83d\udc65 Creando rol '$roleName'");
        $result = $adb->pquery("SELECT roleid, parentrole FROM vtiger_role WHERE rolename = ?", [$roleName]);
        if ($adb->num_rows($result) > 0) {
            error_log("\u26a0\ufe0f Rol '$roleName' ya existe. Omitiendo creación.");
            $roleId = $adb->query_result($result, 0, 'roleid');
        } else {
            error_log("\u2705 Rol '$roleName' no existe. Creando nuevo rol.");
            $roleId = 'H' . $adb->getUniqueID("vtiger_role");

            if ($parentRoleId) {
                error_log("\ud83d\udc65 Rol padre ID: $parentRoleId");
                $parentResult = $adb->pquery("SELECT parentrole FROM vtiger_role WHERE roleid = ?", [$parentRoleId]);
                $parentPath = $adb->query_result($parentResult, 0, 'parentrole');
                $rolePath = $parentPath . '::' . $roleId;
            } else {
                error_log("\ud83d\udc65 Rol raíz. No hay rol padre.");
                $rolePath = $roleId;
            }

            $depth = substr_count($rolePath, '::');

            $adb->pquery("INSERT INTO vtiger_role (roleid, rolename, parentrole, depth) VALUES (?, ?, ?, ?)", [
                $roleId,
                $roleName,
                $rolePath,
                $depth
            ]);
        }

        error_log("\ud83d\udc65 Rol '$roleName' ID: $roleId");

        error_log("\ud83d\udc65 Crear grupos si existen");

        // Crear grupos si existen
        if (!empty($roleData['group'])) {
            error_log("\ud83d\udc65 Grupos encontrados para crear en este rol" . json_encode($roleData['group']));
            foreach ($roleData['group'] as $groupData) {
                error_log("\ud83d\udc65 Grupo: " . $groupData['name']);
                error_log("\ud83d\udc65 Descripción: " . $groupData['description']);
                GroupManager::createGroupIfNotExists($groupData['name'], $groupData['description'], $groupData['type'], [$roleId]);
            }
        }

        error_log("\ud83d\udc65 Crear usuarios si existen");

        // Crear usuarios si existen
        if (empty($roleData['user'])) {
            error_log("\ud83d\udc65 No hay usuarios para crear en este rol");
        } else {
            foreach ($roleData['user'] as $user) {
                error_log("\ud83d\udc65 Usuario: " . $user['user_name']);
                UserManager::createUserIfNotExists($user, $roleId);
            }
        }

        error_log("\ud83d\udc65 Crear subroles si existen");

        // Crear subroles si existen
        if (!empty($roleData['subrole'])) {
            foreach ($roleData['subrole'] as $subRoleData) {
                self::createRoleRecursive($subRoleData, $roleId);
            }
        }
    }
}
