<?php

require_once 'modules/HelloWorld/models/FieldConfig.php';
require_once 'vtlib/Vtiger/Module.php';

class FieldManager {
    public static function initializeFields() {
        $db = PearDatabase::getInstance();
        foreach (FieldConfig::getModules() as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);

            foreach (FieldConfig::getBlocks($moduleName) as $blockConfig) {
                $block = Vtiger_Block::getInstance($blockConfig['name'], $module);

                if ($block) {
                    $db->pquery("UPDATE vtiger_blocks SET sequence = ? WHERE blockid = ?",
                        [(int)$blockConfig['sequence'], $block->id]);
                }

                foreach (FieldConfig::getFields($moduleName) as $fieldConfig) {

                    $block = Vtiger_Block::getInstance($fieldConfig['block'], $module);

                    if (!$block) {
                        $block = new Vtiger_Block();
                        $block->label = $fieldConfig['block'];

                        if (!empty($fieldConfig['block_sequence'])) {
                            $block->sequence = $fieldConfig['block_sequence'];
                        }

                        $module->addBlock($block);
                    } else {
                        $db->pquery("UPDATE vtiger_blocks SET sequence = ? WHERE blockid = ?",
                            [$fieldConfig['block_sequence'], $block->id]);
                    }

                    self::createField($module, $block, $fieldConfig);
                }
            }
        }
    }

    private static function createField($module, $block, $fieldConfig) {
        $db = PearDatabase::getInstance();
        $fieldName = $fieldConfig['name'];
        $fieldNameCF = "cf_{$fieldName}";
        
        //error_log("Creando field: " . $fieldName);
        //error_log("Custom block id: " . $block->id);
        //error_log("Field id después de save(): " . $field->id);

        $field = Vtiger_Field::getInstance($fieldName, $module)  ?: Vtiger_Field::getInstance($fieldNameCF, $module);

        if (!$field) {
            $field = new Vtiger_Field();
            
            $field->column = $fieldNameCF;
            $field->table = $fieldConfig['tablename'];

            if (!empty($fieldConfig['label'])) {
                $field->label = ucwords(str_replace('_', ' ', $fieldConfig['label']));
            } else {
                $field->label = ucwords(str_replace('_', ' ', $fieldConfig['name']));
            }

            $field->name = $fieldNameCF;

            if (!empty($fieldConfig['uitype'])) {
                $field->uitype = (int)$fieldConfig['uitype'];
            }

            if (!empty($fieldConfig['defaultvalue'])) {
                $field->defaultvalue = $fieldConfig['defaultvalue'];
            }

            $block->addField($field);
            $field->save();
        } else {
            $db->pquery("UPDATE vtiger_field SET block = ? WHERE fieldid = ?",
            [$block->id, $field->id]);

            if (!empty($fieldConfig['uitype'])) {
                $db->pquery("UPDATE vtiger_field SET uitype = ? WHERE fieldid = ?",
                [(int)$fieldConfig['uitype'], $field->id]);
            }

            if (!empty($fieldConfig['defaultvalue'])) {
                $db->pquery("UPDATE vtiger_field SET defaultvalue = ? WHERE fieldid = ?",
                [$fieldConfig['defaultvalue'], $field->id]);
            }
        }

        if (isset($fieldConfig['picklist'])) {
            //pendiente implementar la validacion que ya existe
            $field->setPicklistValues($fieldConfig['picklist']);
        }

        if (!empty($fieldConfig['typeofdata'])) {
            $field->typeofdata = $fieldConfig['typeofdata'];
        }

        if (!empty($fieldConfig['relatedModules'])) {
            $field->setRelatedModules($fieldConfig['relatedModules']);
        }

        if (!empty($fieldConfig['sequence'])) {
            $db->pquery("UPDATE vtiger_field SET sequence = ? WHERE fieldid = ?",
                [$fieldConfig['sequence'], $field->id]);
        }

    }
}
