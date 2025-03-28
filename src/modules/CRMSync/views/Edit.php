<?php
include_once 'modules/SalesOrder/views/Edit.php';

class CRMSync_SalesOrder_Edit_View extends SalesOrder_Edit_View {

    public function process(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);

        // Log para verificar si la vista personalizada se está ejecutando
        error_log("✅ Cargando vista personalizada de CRMSync para SalesOrder");
        die("🚨 La vista personalizada de CRMSync para SalesOrder se está ejecutando.");

        // Usar la plantilla personalizada
        $viewer->view('Edit.tpl', 'CRMSync');
    }
}
