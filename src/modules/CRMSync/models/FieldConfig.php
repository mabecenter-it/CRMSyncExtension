<?php

class FieldConfig {
    private static $config;

    public static function load() {
        if (!self::$config) {
            $jsonPath = __DIR__ . '/../config/fields.json';
            self::$config = json_decode(file_get_contents($jsonPath), true);
        }
    }

    public static function getFields($moduleName) {
        self::load();
        $filtered = [];
        foreach (self::$config as $module) {
            if ($module['name'] === $moduleName) {
                foreach ($module['block'] as $block) {
                    if (!empty($block['dynamic'])) {
                        $dynamicCount = (int)$block['dynamic'];
                        for ($i = 1; $i <= $dynamicCount; $i++) {
                            $newBlockName = $block['name'] . '_' . $i;
                            $newBlockSequence = (int)$block['sequence'] + ($i - 1);

                            // Duplicate fields and update their block information
                            foreach ($block['field'] as $field) {
                                $field['label'] = $field['name'];
                                $field['name'] = $field['name'] . '_' . $i;
                                $field['block'] = $newBlockName;
                                $field['block_sequence'] = $newBlockSequence;
                                $filtered[] = $field;
                            }
                        }
                        continue;
                    }

                    foreach ($block['field'] as $field) {
                        $field['block'] = $block['name'];
                        $field['block_sequence'] = $block['sequence'];
                        $filtered[] = $field;
                    }
                }
            }
        }
        return $filtered;
    }

    public static function getModules() {
        self::load();
        $modules = [];
        foreach (self::$config as $module) {
            $modules[$module['name']] = true;
        }
        return array_keys($modules);
    }

    public static function getBlocks($moduleName) {
        self::load();
        $filtered = [];
        foreach (self::$config as $module) {
            if ($module['name'] === $moduleName) {
                foreach ($module['block'] as $block) {
                    $block['module'] = $module['name'];
                    $filtered[] = $block;
                }
            }
        }
        return $filtered;
    }
}
