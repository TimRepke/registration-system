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
    var app = angular.module('shopping', ["xeditable"]);
    app.run(function(editableOptions) {
        editableOptions.theme = 'bs3';
    });
    app.directive("tableShopping", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-shopping.html"
        };
    });

    app.controller('TableShoppingController', function($scope, $filter, $q){
        var table = this;

        table.entries = tmp_shop;

        // === basic table functions ===

        table.rowSum = function(row){
            return row.cnt*row.price;
        };

        table.sum = function(){
            var ret = 0;
            for(var run = 0; run < table.entries.length; run++)
                if(!table.entries[run].isDeleted)
                    ret += table.rowSum(table.entries[run]);
            return ret;
        };

        // === edit table functions ===

        // filter rows to show
        $scope.filterRow = function(row) {
            return row.isDeleted !== true;
        };

        // mark row as deleted
        $scope.deleteRow = function(index) {
            table.entries[index].isDeleted = true;
        };

        $scope.chang = function(index, prop, dat){
            table.entries[index][prop] = dat;
        };

        // add row
        $scope.addRow = function() {
            table.entries.push({
                pos: "",
                cnt: 1,
                price: 0,
                isNew: true
            });
        };

        // cancel all changes
        $scope.cancel = function() {
            for (var i = table.entries.length; i--;) {
                var row = table.entries[i];

                // undelete
                if (row.isDeleted) {
                    delete row.isDeleted;
                }
                // remove new
                if (row.isNew) {
                    table.entries.splice(i, 1);
                }
            }
        };

        // save edits
        $scope.saveTable = function() {
            var results = [];
            for (var i = table.entries.length; i--;) {
                var row = table.entries[i];

                // actually delete row
                if (row.isDeleted) {
                    table.entries.splice(i, 1);
                }
                // mark as not new
                if (row.isNew) {
                   delete row.isNew;
                }
            }

            return $q.all(results);
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

