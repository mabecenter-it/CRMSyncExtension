<?php

include_once 'vendor/autoload.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'modules/Vtiger/CRMEntity.php';

class Expenses extends Vtiger_CRMEntity {

    public function vtlib_handler($moduleName, $eventType) {
        if (in_array($eventType, ['module.postinstall', 'module.postupdate'])) {
            $this->createSchema();
        }
    }

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
        // Schema Setup
        error_log("Initializing tables");
        $moduleInstance->initTables();

        error_log("Creating tables");
    
        // Field Setup
        $block = new Vtiger_Block();
        $block->label = 'LBL_' . strtoupper($moduleInstance->name) . '_INFORMATION';
        $moduleInstance->addBlock($block);

        error_log("Creating block");
    
        $blockcf = new Vtiger_Block();
        $blockcf->label = 'LBL_CUSTOM_INFORMATION';
        $moduleInstance->addBlock($blockcf);

        error_log("Creating custom block");
    
        $field1  = new Vtiger_Field();
        $field1->name = 'summary';
        $field1->label = 'Summary';
        $field1->uitype = 2;
        $field1->column = $field1->name;
        $field1->columntype = 'VARCHAR(255)';
        $field1->typeofdata = 'V~M';
        $block->addField($field1);
    
        $moduleInstance->setEntityIdentifier($field1);
    
        $field2  = new Vtiger_Field();
        $field2->name = 'expenseon';
        $field2->label = 'Expense On';
        $field2->uitype = 5;
        $field2->column = $field2->name;
        $field2->columntype = 'Date';
        $field2->typeofdata = 'D~O';
        $block->addField($field2);
    
        $field3  = new Vtiger_Field();
        $field3->name = 'expenseamount';
        $field3->label = 'Amount';
        $field3->uitype = 71;
        $field3->column = $field3->name;
        $field3->columntype = 'VARCHAR(255)';
        $field3->typeofdata = 'V~M';
        $block->addField($field3);
    
        // Nota: redefinimos $field3 para el campo description
        $field3  = new Vtiger_Field();
        $field3->name = 'description';
        $field3->label = 'Description';
        $field3->uitype = 19;
        $field3->column = 'description';
        $field3->table = 'vtiger_crmentity';
        $blockcf->addField($field3);
    
        // Recommended common fields every Entity module should have (linked to core table)
        $mfield1 = new Vtiger_Field();
        $mfield1->name = 'assigned_user_id';
        $mfield1->label = 'Assigned To';
        $mfield1->table = 'vtiger_crmentity';
        $mfield1->column = 'smownerid';
        $mfield1->uitype = 53;
        $mfield1->typeofdata = 'V~M';
        $block->addField($mfield1);
    
        $mfield2 = new Vtiger_Field();
        $mfield2->name = 'createdtime';
        $mfield2->label = 'Created Time';
        $mfield2->table = 'vtiger_crmentity';
        $mfield2->column = 'createdtime';
        $mfield2->uitype = 70;
        $mfield2->typeofdata = 'DT~O';
        $mfield2->displaytype = 2;
        $block->addField($mfield2);
    
        $mfield3 = new Vtiger_Field();
        $mfield3->name = 'modifiedtime';
        $mfield3->label = 'Modified Time';
        $mfield3->table = 'vtiger_crmentity';
        $mfield3->column = 'modifiedtime';
        $mfield3->uitype = 70;
        $mfield3->typeofdata = 'DT~O';
        $mfield3->displaytype = 2;
        $block->addField($mfield3);
    
        /* NOTE: Vtiger 7.1.0 onwards */
        $mfield4 = new Vtiger_Field();
        $mfield4->name = 'source';
        $mfield4->label = 'Source';
        $mfield4->table = 'vtiger_crmentity';
        $mfield4->displaytype = 2; // to disable field in Edit View
        $mfield4->quickcreate = 3;
        $mfield4->masseditable = 0;
        $block->addField($mfield4);
    
        $mfield5 = new Vtiger_Field();
        $mfield5->name = 'starred';
        $mfield5->label = 'starred';
        $mfield5->table = 'vtiger_crmentity_user_field';
        $mfield5->displaytype = 6;
        $mfield5->uitype = 56;
        $mfield5->typeofdata = 'C~O';
        $mfield5->quickcreate = 3;
        $mfield5->masseditable = 0;
        $block->addField($mfield5);
    
        $mfield6 = new Vtiger_Field();
        $mfield6->name = 'tags';
        $mfield6->label = 'tags';
        $mfield6->displaytype = 6;
        $mfield6->columntype = 'VARCHAR(1)';
        $mfield6->quickcreate = 3;
        $mfield6->masseditable = 0;
        $block->addField($mfield6);
        /* End 7.1.0 */
    
        // Filter Setup
        $filter1 = new Vtiger_Filter();
        $filter1->name = 'All';
        $filter1->isdefault = true;
        $moduleInstance->addFilter($filter1);
        $filter1->addField($field1)->addField($field2, 1)->addField($field3, 2)->addField($mfield1, 3);
    
        // Sharing Access Setup
        $moduleInstance->setDefaultSharing();
    
        // Webservice Setup
        $moduleInstance->initWebservice();
    
        // Aquí agregamos la relación con el módulo Contacts
        $contactsModule = Vtiger_Module::getInstance('Contacts');
        if ($contactsModule) {
            $contactsModule->setRelatedList(
                $moduleInstance,
                $MODULENAME,
                array('ADD', 'SELECT'),
                'get_related_list',
                '1' // Specify 1-N relationship
            );
            error_log("Relationship with Contacts created.");
        }
    
        mkdir('modules/'.$MODULENAME);
        error_log("Module $MODULENAME created successfully.");
    }

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
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Summary' => Array('expenses', 'summary'),
        'Assigned To' => Array('crmentity', 'smownerid')
    );
    var $list_fields_name = Array(
        /* Format: Field Label => fieldname */
        'Summary' => 'summary',
        'Assigned To' => 'assigned_user_id'
    );

    // Make the field link to detail view
    var $list_link_field = 'summary';

    // For Popup listview and UI type support
    var $search_fields = Array(
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Summary' => Array('expenses', 'summary'),
        'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
    );
    var $search_fields_name = Array(
        /* Format: Field Label => fieldname */
        'Summary' => 'summary',
        'Assigned To' => 'assigned_user_id',
    );

    // For Popup window record selection
    var $popup_fields = Array ('summary');

    // For Alphabetical search
    var $def_basicsearch_col = 'summary';

    // Column value to use on detail view record text display
    var $def_detailview_recname = 'summary';

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    var $mandatory_fields = Array('summary','assigned_user_id');

    var $default_order_by = 'summary';
    var $default_sort_order = 'ASC';
}
