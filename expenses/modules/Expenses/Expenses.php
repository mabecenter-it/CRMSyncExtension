<?php

include_once 'vendor/autoload.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'modules/Vtiger/CRMEntity.php';

class Expenses extends Vtiger_CRMEntity {

    /**
     * Handler que se invoca en eventos de instalación o actualización del módulo.
     */
    public function vtlib_handler($moduleName, $eventType) {
        if (in_array($eventType, ['module.postinstall', 'module.postupdate'])) {
            $this->createSchema();
        }
    }

    /**
     * Sobrescribe el método de guardado para actualizar el setype en vtiger_crmentity.
     */
    /* public function save_module($module) {
        parent::save_module($module);
        global $adb;
        if (!empty($this->id)) {
            $adb->pquery("UPDATE vtiger_crmentity SET setype = ? WHERE crmid = ?", array('Expenses', $this->id));
            error_log("Updated setype for record ID " . $this->id);
        }
    } */

    /**
     * Crea la estructura del módulo, incluidos bloques, campos y relaciones.
     */
    private function createSchema() {
        error_log("Creating Expenses module schema");
        $MODULENAME = 'Expenses';
        
        error_log("Initializing module instance");
        $moduleInstance = Vtiger_Module::getInstance($MODULENAME) ?: new Vtiger_Module();                        
        $moduleInstance->name = $MODULENAME;
        $moduleInstance->parent = 'Tools';
        $moduleInstance->isentitytype = true;
        $moduleInstance->save();
        
        error_log("Module instance created");
        // Inicializa las tablas base y de custom fields
        error_log("Initializing tables");
        $moduleInstance->initTables();
        error_log("Creating tables");
    
        // Bloque de información principal
        $block = new Vtiger_Block();
        $block->label = 'LBL_' . strtoupper($moduleInstance->name) . '_INFORMATION';
        $moduleInstance->addBlock($block);
        error_log("Creating main block");
    
        // Bloque para información custom
        $blockcf = new Vtiger_Block();
        $blockcf->label = 'LBL_CUSTOM_INFORMATION';
        $moduleInstance->addBlock($blockcf);
        error_log("Creating custom block");
    
        // Campo: summary
        error_log("Creating field: summary");
        $field1 = new Vtiger_Field();
        $field1->name = 'summary';
        $field1->label = 'Summary';
        $field1->uitype = 2;
        $field1->column = 'summary';
        $field1->columntype = 'VARCHAR(255)';
        $field1->typeofdata = 'V~M';
        $block->addField($field1);
        $moduleInstance->setEntityIdentifier($field1);
    
        // Campo: expenseon (Fecha)
        error_log("Creating field: expenseon");
        $field2 = new Vtiger_Field();
        $field2->name = 'expenseon';
        $field2->label = 'Expense On';
        $field2->uitype = 5;
        $field2->column = 'expenseon';
        $field2->columntype = 'Date';
        $field2->typeofdata = 'D~O';
        $block->addField($field2);
    
        // Campo: expenseamount (Monto)
        error_log("Creating field: expenseamount");
        $field3 = new Vtiger_Field();
        $field3->name = 'expenseamount';
        $field3->label = 'Amount';
        $field3->uitype = 71;
        $field3->column = 'expenseamount';
        $field3->columntype = 'VARCHAR(255)';
        $field3->typeofdata = 'V~M';
        $block->addField($field3);
    
        // Campo: description (almacenado en vtiger_crmentity)
        error_log("Creating field: description");
        $fieldDesc = new Vtiger_Field();
        $fieldDesc->name = 'description';
        $fieldDesc->label = 'Description';
        $fieldDesc->uitype = 19;
        $fieldDesc->column = 'description';
        $fieldDesc->table = 'vtiger_crmentity';
        $blockcf->addField($fieldDesc);
    
        // Campo: contact_id (Lookup a Contacts, para relación 1:N)
        error_log("Creating lookup field: contact_id");
        $fieldLookup = new Vtiger_Field();
        $fieldLookup->name = 'contact_id';
        $fieldLookup->label = 'Contact';
        $fieldLookup->uitype = 10; // Tipo lookup
        $fieldLookup->column = 'contact_id';
        $fieldLookup->typeofdata = 'N~M'; // Usa 'V~M' si es obligatorio
        $fieldLookup->setRelatedModules(array('Contacts'));
        $block->addField($fieldLookup);
        error_log("Lookup field 'contact_id' added");
   
        // Campos recomendados (vinculados a vtiger_crmentity)
        // Filter Setup: Filtro por defecto "All"
        error_log("Setting up default filter");
        $filter1 = new Vtiger_Filter();
        $filter1->name = 'All';
        $filter1->isdefault = true;
        $moduleInstance->addFilter($filter1);
        $filter1->addField($field1)->addField($field2, 1)->addField($field3, 2);
    
        // Sharing & Webservice Setup
        $moduleInstance->setDefaultSharing();
        $moduleInstance->initWebservice();
    
        // Relación con Contacts (solo metadata; la relación real se almacena en el campo lookup)
        $contactsModule = Vtiger_Module::getInstance('Contacts');
        if ($contactsModule) {
            $contactsModule->setRelatedList(
                $moduleInstance,
                $MODULENAME,
                array('ADD'),
                'get_dependents_list'
            );
            error_log("Relationship with Contacts created.");
        }
    
        if (!is_dir('modules/'.$MODULENAME)) {
            mkdir('modules/'.$MODULENAME);
        }
        error_log("Module $MODULENAME created successfully.");
    }

    // Propiedades del módulo
    var $table_name = 'vtiger_expenses';
    var $table_index = 'expensesid';
    var $customFieldTable = Array('vtiger_expensescf', 'expensesid');
    var $tab_name = Array('vtiger_crmentity', 'vtiger_expenses', 'vtiger_expensescf');
    var $tab_name_index = Array(
        'vtiger_crmentity' => 'crmid',
        'vtiger_expenses' => 'expensesid',
        'vtiger_expensescf' => 'expensesid'
    );

    var $list_fields = Array(
        'Summary' => Array('expenses', 'summary'),
        'Assigned To' => Array('crmentity', 'smownerid')
    );
    var $list_fields_name = Array(
        'Summary' => 'summary',
        'Assigned To' => 'assigned_user_id'
    );

    var $list_link_field = 'summary';
    var $search_fields = Array(
        'Summary' => Array('expenses', 'summary'),
        'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
    );
    var $search_fields_name = Array(
        'Summary' => 'summary',
        'Assigned To' => 'assigned_user_id',
    );

    var $popup_fields = Array('summary');
    var $def_basicsearch_col = 'summary';
    var $def_detailview_recname = 'summary';
    var $mandatory_fields = Array('summary','assigned_user_id');
    var $default_order_by = 'summary';
    var $default_sort_order = 'ASC';
}
