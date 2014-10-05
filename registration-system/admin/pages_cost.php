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

}


// base/static stuff down here ===========================================================
else {
    $headers .= '
             <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
             <script type="text/javascript" src="../view/js/angular.min.js"></script>
             <script type="text/javascript" src="../view/js/xeditable.js"></script>
             <script type="text/javascript" src="pages_cost/pages_cost.js"></script>
             <link   type="text/css" rel="stylesheet" href="pages_cost/pages_cost.css" />';


    $text .= '
        <div ng-app="pages-cost">
            <h1>Kostenaufstellung</h1>

            <div ng-controller="TablePriceController as table">
                <h2>Kosten pro Person</h2>
                <a href ng-click="table.toggleEditmode()" class="editbutton">edit</a>
                <table-price></table-price>
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