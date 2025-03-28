<?php

require_once 'vtlib/Vtiger/Module.php';
require_once 'modules/HelloWorld/helpers/FieldManager.php';
require_once 'modules/HelloWorld/helpers/UserManager.php';
require_once 'modules/HelloWorld/helpers/RoleManager.php';
require_once 'modules/HelloWorld/helpers/ProfileManager.php';
require_once 'modules/HelloWorld/helpers/GroupManager.php';
require_once 'modules/HelloWorld/helpers/SystemManager.php';

class HelloWorld extends Vtiger_Detail_View {

    public function vtlib_handler($moduleName, $eventType) {
        if (in_array($eventType, ['module.postinstall', 'module.postupdate'])) {
            FieldManager::initializeFields();
            //SystemManager::initializeUsers();
            //self::registerCustomScripts();
        }
    }

    private static function registerCustomScripts() {
        $moduleInstance = Vtiger_Module::getInstance('SalesOrder');
        $moduleInstance->addLink('HEADERSCRIPT', 'HelloWorldJS', 'modules/HelloWorld/resources/custom_script.js');
    }
}
