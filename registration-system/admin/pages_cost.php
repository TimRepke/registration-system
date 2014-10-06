<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 10/4/14
 * Time: 9:36 PM
 */

global $text, $headers, $admin_db, $config_current_fahrt_id, $ajax, $config_reisearten, $config_reisearten_o, $config_studitypen_o, $config_admin_verbose_level, $config_verbose_level, $config_essen;


// AJAX requests up here ============================================================
if(isset($_REQUEST['ajax'])){

    $data = "";
    $fid  = $config_current_fahrt_id;
    $task = $_REQUEST['ajax'];

    if(isset($_REQUEST['json-data'])){
        $data = $_REQUEST['json-data'];
    } elseif( strpos($task, "set")!==false && strpos($task, "json")!==false ){
        $data = file_get_contents("php://input");
    }


    switch($task){

        // == GETTER ==
        case "get-price-json":
            header('Content-Type: application/json');
            $ajax = $admin_db->get("cost", "tab1", ["fahrt_id" => $fid]);
            break;

        case "get-shopping-json":
            header('Content-Type: application/json');
            $ajax = $admin_db->get("cost", "tab2", ["fahrt_id" => $fid]);
            break;

        case "get-receipt-json":
            header('Content-Type: application/json');
            $ajax = $admin_db->get("cost", "tab3", ["fahrt_id" => $fid]);
            break;

        case "get-moneyio-json":
            header('Content-Type: application/json');
            $ajax = $admin_db->get("cost", "moneyIO", ["fahrt_id" => $fid]);
            break;


        // == SETTER ==
        case "set-price-json":
            $admin_db->update("cost",["tab1" => $data], ["fahrt_id" => $fid]);
            break;

        case "set-shopping-json":
            $admin_db->update("cost",["tab2" => $data], ["fahrt_id" => $fid]);
            break;

        case "set-receipt-json":
            $admin_db->update("cost",["tab3" => $data], ["fahrt_id" => $fid]);
            break;

        case "set-moneyio-json":
            $admin_db->update("cost",["moneyIO" => $data], ["fahrt_id" => $fid]);
            break;

        // == DEFAULT ==
        default:
            break;
    }

}


// base/static stuff down here ===========================================================
else {
    $headers .= '
             <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
             <script type="text/javascript" src="../view/js/angular.min.js"></script>
             <script type="text/javascript" src="../view/js/xeditable.js"></script>
             <script type="text/javascript" src="../view/js/toastr.min.js"></script>
             <script type="text/javascript" src="pages_cost/pages_cost.js"></script>
             <link   type="text/css" rel="stylesheet" href="pages_cost/pages_cost.css" />
             <link   type="text/css" rel="stylesheet" href="../view/css/toastr.css" />';


    $text .= '
        <div ng-app="pages-cost">
            <h1>Kostenaufstellung</h1>

            <div ng-controller="TablePriceController as table">
                <h2>Kosten pro Person</h2>
                <table-price></table-price>
                <button type="button" ng-click="table.toggleEditmode()" class="button-edit">edit</button>
                <table-price-edit></table-price-edit>
            </div>

            <div ng-controller="TableShoppingController as table">
                <h2>Einkaufen</h2>
                <table-shopping></table-shopping>
            </div>

            <div ng-controller="TableReceiptController as table">
                <h2>Herbergsrechnung</h2>
                <table-receipt></table-receipt>
            </div>

            <div ng-controller="TableMoneyioController as table">
                <h2>Money In/Out</h2>
                <table-moneyio></table-moneyio>
            </div>
        </div>
    ';


}