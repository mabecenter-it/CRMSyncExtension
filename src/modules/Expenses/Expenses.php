<?php

include_once 'modules/Vtiger/CRMEntity.php';

class Expenses extends Vtiger_CRMEntity {
    public $table_name = 'vtiger_expenses';
    public $table_index = 'expensesid';

    public $customFieldTable = ['vtiger_expensescf', 'expensesid'];

    public $tab_name = ['vtiger_crmentity', 'vtiger_expenses', 'vtiger_expensescf'];

    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'vtiger_expenses' => 'expensesid',
        'vtiger_expensescf' => 'expensesid',
    ];

    public $list_fields = [
        'Summary' => ['vtiger_expenses', 'summary'],
        'Assigned To' => ['vtiger_crmentity', 'smownerid']
    ];

    public $list_fields_name = [
        'Summary' => 'summary',
        'Assigned To' => 'assigned_user_id'
    ];

    public $list_link_field = 'summary';

    public $search_fields = [
        'Summary' => ['vtiger_expenses', 'summary']
    ];

    public $search_fields_name = [
        'Summary' => 'summary'
    ];

    public $popup_fields = ['summary'];
    public $def_basicsearch_col = 'summary';
    public $def_detailview_recname = 'summary';

    public $mandatory_fields = ['summary', 'assigned_user_id'];
    public $default_order_by = 'summary';
    public $default_sort_order = 'ASC';
}
