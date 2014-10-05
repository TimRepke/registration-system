/**
 * Created by tim on 10/4/14.
 */

/* MAIN module */
(function() {
    var app = angular.module('pages-cost', ['price','shopping','receipt','moneyio']);

})();




/* ****************************************************************
 * pricetable module
 */
(function() {
    var app = angular.module('price', []);

    app.directive("tablePrice", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-price.html"
        };
    });

    app.directive("tablePriceEdit", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-price-edit.html"
        };
    });

    app.controller('TablePriceController', function(){
        var table = this;

        table.editmode = false;

        table.toggleEditmode = function(){
            table.editmode = table.editmode == false;
        }

    });


})();




/* ****************************************************************
 * shoppingtable module
 */
(function() {
    var app = angular.module('shopping', []);

    app.directive("tableShopping", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-shopping.html"
        };
    });

    app.controller('TableShoppingController', function(){
        var table = this;

        table.entries = tmp_shop;

        table.rowSum = function(row){
            return row.cnt*row.price;
        };

        table.sum = function(){
            var ret = 0;
            for(var run = 0; run < table.entries.length; run++)
                ret += table.rowSum(table.entries[run]);
            return ret;
        };
    });


    var tmp_shop = [
        {
            pos: "Mate",
            cnt: 4,
            price: 0.67
        },
        {
            pos: "Limonade",
            cnt: 5,
            price: 1.30
        },
        {
            pos: "Brause",
            cnt: 10,
            price: 0.12
        }
    ];
})();




/* ****************************************************************
 * receipttable module
 */
(function() {
    var app = angular.module('receipt', []);

    app.directive("tableReceipt", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-receipt.html"
        };
    });

    app.controller('TableReceiptController', function(){
        var table = this;

    });


})();




/* ****************************************************************
 * moneyIO module
 */
(function() {
    var app = angular.module('moneyio', []);

    app.directive("tableMoneyio", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-moneyio.html"
        };
    });

    app.controller('TableMoneyioController', function(){
        var table = this;

    });


})();

