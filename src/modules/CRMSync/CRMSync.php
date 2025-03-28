<?php

require_once 'vtlib/Vtiger/Module.php';
require_once 'modules/CRMSync/helpers/FieldManager.php';
require_once 'modules/CRMSync/helpers/UserManager.php';
require_once 'modules/CRMSync/helpers/RoleManager.php';
require_once 'modules/CRMSync/helpers/ProfileManager.php';
require_once 'modules/CRMSync/helpers/GroupManager.php';
require_once 'modules/CRMSync/helpers/SystemManager.php';

class CRMSync extends Vtiger_Detail_View {

    public function vtlib_handler($moduleName, $eventType) {
        if (in_array($eventType, ['module.postinstall', 'module.postupdate'])) {
            FieldManager::initializeFields();
            SystemManager::initializeUsers();
            self::registerCustomScripts();
        }
    }

    private static function registerCustomScripts() {
        $moduleInstance = Vtiger_Module::getInstance('SalesOrder');
        $moduleInstance->addLink('HEADERSCRIPT', 'CRMSyncJS', 'modules/CRMSync/resources/custom_script.js');
    }
}
