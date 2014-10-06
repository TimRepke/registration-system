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
    var app = angular.module('moneyio', ['currfil']);

    app.directive("tableMoneyio", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-moneyio.html"
        };
    });

    app.directive("tableMoneyioCol", function() {

        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-moneyio-col.html",
            transclude: true,
            scope: {
                col: '=',
                io: '@',
                tform: '='
            },
            link: function(scope, element) {
                scope.filterRow = function(row) {
                    return row.isDeleted !== true;
                };

                scope.chang = function(row, cell, val){
                    row[cell] = val;
                }

            }

        };
    });

    app.controller('TableMoneyioController', ["$scope", "$filter", "$q", "$http", "$rootScope", function($scope, $filter, $q, $http, $rootScope){
        var table = this;

        table.entries = {
                "in": [],
                "out": []
            };


        $http.get('?page=cost&ajax=get-moneyio-json').success(function(data){
            if(data !== "")
                table.entries = data;
        });

        // === basic table functions ===

        table.colSum = function(col){
            var ret = 0;
            for(var i = col.length; i--;){
                if(!col[i].isDeleted)
                    ret += 1*col[i].val;
            }
            return ret;
        };

        table.diff = function(){
            return table.colSum(table.entries.in) - table.colSum(table.entries.out);
        };

        table.diffColor = function(){
            var tolerance = 10; // specify the tolerance of diff
            var diff = table.diff();
            var color = "";
            if(diff < 0)
                color = "red";
            else if(diff >= 0 && diff <= tolerance)
                color = "green";
            else if(diff > tolerance)
                color = "yellow";

            return color;
        };

        // === edit table functions ===

        // mark row as deleted
        $scope.deleteRow = function(col,index) {
            console.log("deleteRow called");
            table.entries[col][index].isDeleted = true;
        };

        // add row
        $scope.addRow = function(col) {
            col.push({
                pos: "",
                val: 0,
                isNew: true
            });
        };

        // cancel all changes
        $scope.cancel = function() {
            var kanzlei = function(col){
                for (var i = col.length; i--;) {
                    var row = col[i];

                    // undelete
                    if (row.isDeleted) {
                        delete row.isDeleted;
                    }
                    // remove new
                    if (row.isNew) {
                        col.splice(i, 1);
                    }
                }
            }
            kanzlei(table.entries.in);
            kanzlei(table.entries.out);
        };

        // save edits
        $scope.saveTable = function() {
            var results = [];

            var salbei = function(col){
                for (var i = col.length; i--;) {
                    var row = col[i];

                    // actually delete row
                    if (row.isDeleted) {
                        col.splice(i, 1);
                    }
                    // mark as not new
                    if (row.isNew) {
                        delete row.isNew;
                    }
                }
            }

            salbei(table.entries.in);
            salbei(table.entries.out);

            $http.post('?page=cost&ajax=set-moneyio-json', table.entries).success(function(data, status, headers, config){
                toastr.success('Saved to Database!')
            });
            return $q.all(results);
        };

    }]);

    angular.module('currfil', []).filter('filterDiff', function() {
        return function(val) {
            //console.log(val);
            if(val.indexOf("(")>-1)
                return "<span style='color:red'>"+val+"</span>";
            else
                return "<span style='color:limegreen'>"+val+"</span>";
        };
    });

})();

