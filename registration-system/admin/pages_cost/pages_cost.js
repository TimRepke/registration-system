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

    app.controller('TableShoppingController', ["$scope", "$filter", "$q", "$http", function($scope, $filter, $q, $http){
        var table = this;

        table.entries = [];

        $http.get('?page=cost&ajax=get-shopping-json').success(function(data){
            if(data !== "")
                table.entries = data;
        });

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

            $http.post('?page=cost&ajax=set-shopping-json', table.entries).success(function(data, status, headers, config){
                toastr.success('Saved to Database!')
            });
            return $q.all(results);
        };

    }]);

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

    app.controller('TableReceiptController', ["$scope", "$filter", "$q", "$http", function($scope, $filter, $q, $http){
        var table = this;

        table.entries = [];

        $http.get('?page=cost&ajax=get-receipt-json').success(function(data){
            if(data !== "")
                table.entries = data;
        });

        // === basic table functions ===

        table.rowSum = function(row){
            return row.cnt*row.mul*row.price;
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
                mul: 1,
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

            $http.post('?page=cost&ajax=set-receipt-json', table.entries).success(function(data, status, headers, config){
                toastr.success('Saved to Database!')
            });
            return $q.all(results);
        };

     }]);


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

