<?php

require_once 'vtlib/Vtiger/Module.php';
require_once 'modules/CRMSync/helpers/FieldManager.php';
require_once 'modules/CRMSync/helpers/UserManager.php';
require_once 'modules/CRMSync/helpers/RoleManager.php';
require_once 'modules/CRMSync/helpers/ProfileManager.php';
require_once 'modules/CRMSync/helpers/GroupManager.php';
require_once 'modules/CRMSync/helpers/SystemManager.php';

//include_once 'vendor/autoload.php';

class CRMSync extends Vtiger_Detail_View {

    public function vtlib_handler($moduleName, $eventType) {
        error_log("Por acá ingresé a vtlib_handler");
        if (in_array($eventType, ['module.postinstall', 'module.postupdate'])) {
            //FieldManager::initializeFields();
            //SystemManager::initializeUsers();
            self::registerCustomScripts();
            error_log("Por acá ingresé a registerCustomModule");
            self::registerCustomModule();
            error_log("Por acá ingresé a registerCustomModule");
        }
    }

    private static function registerCustomScripts() {
        $moduleInstance = Vtiger_Module::getInstance('SalesOrder');
        $moduleInstance->addLink('HEADERSCRIPT', 'CRMSyncJS', 'modules/CRMSync/resources/custom_script.js');
    }
    private static function registerCustomModule() {
        error_log("Iniciando creación del módulo Expenses");
    
        $MODULENAME = 'Expenses';
    
        // Verifica si ya existe
        $moduleInstance = Vtiger_Module::getInstance($MODULENAME);
        if ($moduleInstance) {
            error_log("El módulo ya existe: $MODULENAME");
            return;
        }
    
        // Crear módulo
        $moduleInstance = new Vtiger_Module();
        $moduleInstance->name = $MODULENAME;
        $moduleInstance->isentitytype = true;
        $moduleInstance->save();
    
        // Asignar tablas
        $moduleInstance->basetable = 'vtiger_expenses';
        $moduleInstance->basetableid = 'expensesid';
        $moduleInstance->customtable = true;
        $moduleInstance->customtableid = 'expensesid';
        $moduleInstance->customtablename = 'vtiger_expensescf';
        $moduleInstance->save();
    
        $adb = PearDatabase::getInstance();
    
        // Crear tabla base
        $schema = "CREATE TABLE IF NOT EXISTS vtiger_expenses (
            expensesid INT(11) NOT NULL AUTO_INCREMENT,
            contact_id INT(11),
            summary VARCHAR(255),
            PRIMARY KEY (expensesid)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $adb->pquery($schema, []);
    
        // Crear tabla custom
        $schema_cf = "CREATE TABLE IF NOT EXISTS vtiger_expensescf (
            expensesid INT(11) NOT NULL,
            PRIMARY KEY (expensesid)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $adb->pquery($schema_cf, []);
    
        // Bloques
        $block = new Vtiger_Block();
        $block->label = 'LBL_EXPENSES_INFORMATION';
        $moduleInstance->addBlock($block);
    
        $blockcf = new Vtiger_Block();
        $blockcf->label = 'LBL_CUSTOM_INFORMATION';
        $moduleInstance->addBlock($blockcf);
    
        // Campo principal (summary)
        $summaryField = new Vtiger_Field();
        $summaryField->name = 'summary';
        $summaryField->label = 'Summary';
        $summaryField->uitype = 2;
        $summaryField->column = 'summary';
        $summaryField->columntype = 'VARCHAR(255)';
        $summaryField->typeofdata = 'V~M';
        $block->addField($summaryField);
        $moduleInstance->setEntityIdentifier($summaryField);
    
        // Campo relacionado con Contacts
        $contactField = new Vtiger_Field();
        $contactField->name = 'contact_id';
        $contactField->label = 'Contact';
        $contactField->table = $moduleInstance->basetable;
        $contactField->column = 'contact_id';
        $contactField->uitype = 10;
        $contactField->typeofdata = 'V~M';
        $contactField->setRelatedModules(['Contacts']);
        $block->addField($contactField);
    
        // Filtro por defecto
        $filter = new Vtiger_Filter();
        $filter->name = 'All';
        $filter->isdefault = true;
        $moduleInstance->addFilter($filter);
        $filter->addField($summaryField, 1)->addField($contactField, 2);
    
        // Permisos por defecto
        $moduleInstance->setDefaultSharing();
    
        // Webservice
        $moduleInstance->initWebservice();
    
        // Relación con Contacts
        $contactsModule = Vtiger_Module::getInstance('Contacts');
        if ($contactsModule) {
            $contactsModule->setRelatedList(
                $moduleInstance,
                $MODULENAME,
                ['ADD', 'SELECT'],
                'get_dependents_list'
            );
        }
    
        // Crear carpeta si no existe
        if (!is_dir("modules/$MODULENAME")) {
            mkdir("modules/$MODULENAME", 0755, true);
        }
    
        error_log("Módulo $MODULENAME creado correctamente.");
    }    
}
