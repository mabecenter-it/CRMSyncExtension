$(document).ready(function(){
    // Verifica que el módulo actual sea SalesOrder
    if (app.getModuleName && app.getModuleName() === 'SalesOrder') {
        console.log("CRMSync script loaded in SalesOrder 2.0");
    }
});
