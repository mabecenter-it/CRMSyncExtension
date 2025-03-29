<?php

require_once 'vtlib/Vtiger/Module.php';
require_once 'modules/CRMSync/helpers/FieldManager.php';
require_once 'modules/CRMSync/helpers/UserManager.php';
require_once 'modules/CRMSync/helpers/RoleManager.php';
require_once 'modules/CRMSync/helpers/ProfileManager.php';
require_once 'modules/CRMSync/helpers/GroupManager.php';
require_once 'modules/CRMSync/helpers/SystemManager.php';

//include_once 'vendor/autoload.php';

class    extends Vtiger_Detail_View {

    public function vtlib_handler($moduleName, $eventType) {
        error_log("Por acá ingresé a vtlib_handler");
        if (in_array($eventType, ['module.postinstall', 'module.postupdate'])) {
            //FieldManager::initializeFields();
            //SystemManager::initializeUsers();
            self::registerCustomScripts();
            error_log("Por acá ingresé a registerCustomModule");
            //self::registerCustomModule();
            error_log("Por acá ingresé a registerCustomModule");
        }
    }

    private static function registerCustomScripts() {
        $moduleInstance = Vtiger_Module::getInstance('SalesOrder');
        $moduleInstance->addLink('HEADERSCRIPT', 'CRMSyncJS', 'modules/CRMSync/resources/custom_script.js');
    }

    private static function registerCustomModule() {
        error_log("Por acá ingresé a registerCustomModule");

        $MODULENAME = 'Expenses';

        error_log("Por acá ingresé");

        // Verifica si ya existe
        $moduleInstance = Vtiger_Module::getInstance($MODULENAME);
        if ($moduleInstance || file_exists("modules/$MODULENAME")) {
            error_log("Module already present - choose a different name.");
            return;
        }

        // Crear nuevo módulo tipo entidad
        $moduleInstance = new Vtiger_Module();
        $moduleInstance->name = $MODULENAME;
        $moduleInstance->isentitytype = true;
        $moduleInstance->tabsequence = Vtiger_Module::getNextTabSequence();
        $moduleInstance->save();

        // Asignar tabla base
        $moduleInstance->basetable = 'vtiger_expenses';
        $moduleInstance->basetableid = 'expensesid';
        $moduleInstance->customtable = false;
        $moduleInstance->save();

        // Crear tabla base
        $schema = "CREATE TABLE IF NOT EXISTS vtiger_expenses (
            expensesid INT(11) NOT NULL AUTO_INCREMENT,
            contact_id INT(11),
            summary VARCHAR(255),
            PRIMARY KEY (expensesid)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $adb = \PearDatabase::getInstance();
        $adb->pquery($schema, []);

        // Campo principal para mostrar nombre del registro
        $block = new Vtiger_Block();
        $block->label = 'LBL_' . strtoupper($MODULENAME) . '_INFORMATION';
        $moduleInstance->addBlock($block);

        $blockcf = new Vtiger_Block();
        $blockcf->label = 'LBL_CUSTOM_INFORMATION';
        $moduleInstance->addBlock($blockcf);

        // Campo obligatorio de entidad
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
        $filter1 = new Vtiger_Filter();
        $filter1->name = 'All';
        $filter1->isdefault = true;
        $moduleInstance->addFilter($filter1);
        $filter1->addField($summaryField)->addField($contactField, 1);

        // Compartición por defecto
        $moduleInstance->setDefaultSharing();

        // Webservice
        $moduleInstance->initWebservice();

        // Crear relación con Contacts
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
            mkdir("modules/$MODULENAME");
        }

        error_log("Módulo $MODULENAME creado correctamente.\n");
    }
}
