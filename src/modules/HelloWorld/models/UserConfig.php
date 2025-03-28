<?php

// UserConfig.php
class UserConfig {
    private static $config;

    public static function load() {
        if (!self::$config) {
            $jsonPath = __DIR__ . '/../config/users.json';
            error_log("\ud83d\udd0d Loading JSON config from: $jsonPath");
            if (!file_exists($jsonPath)) {
                $errorMessage = "Archivo JSON no encontrado en $jsonPath";
                error_log("\u274c Error: $errorMessage");
                throw new Exception($errorMessage);
            }
            $jsonContent = file_get_contents($jsonPath);
            error_log("\u2705 JSON config file found. Content: " . substr($jsonContent, 0, 100) . "..."); // Log first 100 chars
            self::$config = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonError = json_last_error_msg();
                error_log("\u274c JSON decode error: $jsonError");
                throw new Exception("JSON decode error: $jsonError");
            }
            error_log("\u2705 JSON config loaded successfully.");

            // Validate JSON structure
            if (!isset(self::$config['roles'])) {
                $errorMessage = "JSON config must contain 'roles' keys.";
                error_log("\u274c Error: $errorMessage");
                throw new Exception($errorMessage);
            }

            if (!is_array(self::$config['roles'])) {
                $errorMessage = "JSON config 'roles' must be an array.";
                error_log("\u274c Error: $errorMessage");
                throw new Exception($errorMessage);
            }

            foreach (self::$config['roles'] as $role) {
                if (!isset($role['name'])) {
                    $errorMessage = "JSON config 'roles' must contain a 'name' key.";
                    error_log("\u274c Error: $errorMessage");
                    throw new Exception($errorMessage);
                }
            }
        }
    }

   public static function getProfile() {
        self::load();
        error_log("\ud83d\udc64 Loading profile from user config");
        if (!isset(self::$config['profile'])) {
            error_log("\u274c No profile found in user config");
            return null;
        }
        // Check if the profile is an array, if so, return the first element
        if (is_array(self::$config['profile'])) {
            error_log("\ud83d\udd39 Profile is an array, returning the first element");
            $profile = self::$config['profile'][0] ?? null;
        } else {
            $profile = self::$config['profile'];
        }
        error_log("\u2705 Profile loaded: " . json_encode($profile));
        return $profile;
    }

    public static function getRoles() {
        self::load();
        error_log("\ud83d\udc65 Loading roles from user config");
        if (!isset(self::$config['roles'])) {
            error_log("\u274c No roles found in user config");
            return null;
        }
        error_log("\u2705 Roles loaded: " . json_encode(self::$config['roles']));
        return self::$config['roles'] ?? null;
    }

    public static function getUsersByRole($roleName) {
        self::load();
        $users = [];

        $search = function ($roles) use (&$search, $roleName, &$users) {
            foreach ($roles as $role) {
                if (isset($role['name']) && $role['name'] === $roleName && isset($role['user'])) {
                    $users = array_merge($users, $role['user']);
                }
                if (isset($role['subrole'])) {
                    $search($role['subrole']);
                }
            }
        };

        $search(self::$config);

        return $users;
    }
}
