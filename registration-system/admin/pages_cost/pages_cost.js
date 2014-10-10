/**
 * Created by tim on 10/4/14.
 */


var defaultData = [];
defaultData.price   = {
    ALL: { // Stuff all have to pay, no matter when the come or leave
        B_FIX: "4",  // Fixkosten (z.B. Kurzreisezuschlag)
        C_BW: "4.5", // Bettwäsche
        E_ESS:"5",   // Verpflegung gekauft durch uns
        REFRA: "42"  // Förderung durch RefRat
    },
    VAR: { // stuff that has to be paid depending on certain variables
        "F_FR": [ // Frühstück
            { "val": "", "ind": false, "an": false, "ab": false },
            { "val": "3", "ind": true, "an": false, "ab": true },
            { "val": "3", "ind": true, "an": true, "ab": true }
        ],
        "G_MI": [ // Mittag
            { "val": "", "ind": false, "an": false, "ab": false },
            { "val": "", "ind": false, "an": false, "ab": false },
            { "val": "", "ind": false, "an": false, "ab": false }
        ],
        "H_AB": [ // Abendbrot
            { "val": "4", "ind": true, "an": true, "ab": false },
            { "val": "4.5", "ind": true, "an": true, "ab": false },
            { "val": "", "ind": false, "an": false, "ab": false }
        ],
        "D_UE": [ // Übernachtung
            { "val": "12.5", "ind": true, "an": true, "ab": false },
            { "val": "12.5", "ind": true, "an": true, "ab": false },
            { "val": "", "ind": false, "an": false, "ab": false }
        ],
        "A_FAHRT": [ // Fahrtkosten (z.B. Bus, Bahn)
            { "val": "2.5", "ind": false, "an": true, "ab": false },
            { "val": "", "ind": false, "an": false, "ab": false },
            { "val": "2.5", "ind": false, "an": false, "ab": true }
        ]
    }
};
defaultData.shopping= [
    { "pos": "Limonade", "cnt": "76", "price": "1.43" },
    { "pos": "Brause", "cnt": "42", "price": "0.83" },
    { "pos": "Lutscher", "cnt": "1", "price": "0.5" }
];
defaultData.receipt = [
    { "pos": "Bettwäsche", "cnt": "1", "mul": "40", "price": "1.43" },
    { "pos": "Grillnutzung", "cnt": "1", "mul": "40", "price": "0.3" },
    { "pos": "Halbpension", "cnt": "2", "mul": "40", "price": "12.30" },
    { "pos": "Klodeckel", "cnt": 1, "mul": 1, "price": "33" }
];
defaultData.moneyio = {
    "in": [
        { "pos": "Förderung", "val": "1200" },
        { "pos": "Pfand", "val": "31" }
    ], "out": [
        { "pos": "Einkaufen", "val": "354" },
        { "pos": "Busfahrt Hin", "val": "35" },
        { "pos": "Busfahrt Rück", "val": "40" },
        { "pos": "Kaution", "val": "100" }
    ]
};



// module for shared data-management
var dataapp = angular.module("dataapp", []);

/*
    Data Handler for price table
 */
dataapp.service('priceData', ["$http", "$rootScope", function ($http, $rootScope) {
    var table = this;
    table.base = [];

    $http.get('?page=cost&ajax=get-price-json').success(function(data){
        if(data !== ""){
            table.base = data;
            console.log("got price table from DB");
        } else {
            table.base = defaultData.price;
            console.error("Price table not loaded from DB - took default!");
        }
        $rootScope.$broadcast('data::priceUpdated', table.base);
    });

    // == table data functions ==
    table.bez = {
        A_FAHRT: "Fahrt",
        B_FIX: "Fix",
        C_BW: "Bettwäsche",
        D_UE: "Übernachtung",
        E_ESS: "Essen (wir)",
        F_FR: "Frühstück",
        G_MI: "Mittag",
        H_AB: "Abend"
        ,
        FAHRT: "Fahrtkosten",
        ESSEN: "Verpflegung",
        FIX: "Fixkosten",
        WIR: "Zusatzverpflegung",
        UE: "Übernachtung",
        REFRA: "(Förderung)"
    };

    table.calc = {
        FAHRT: {
            "pos": table.bez.FAHRT,
            "sum": function(){
                if(!table.base.VAR || !table.base.VAR.A_FAHRT)
                    return 0;

                var tmp = 0;
                for(var len = table.base.VAR.A_FAHRT.length; len--;)
                    tmp += (table.base.VAR.A_FAHRT[len].val * 1);
                return tmp;
            },
            "cnt": function(){
                if(!table.base.VAR || !table.base.VAR.A_FAHRT)
                    return 0;

                var tmp = 0;
                for(var len = table.base.VAR.A_FAHRT.length; len--;)
                    if(table.base.VAR.A_FAHRT[len].val > 0)
                        tmp++;
                return tmp;
            },
            "val": function(){
                var tmp = table.calc.FAHRT.cnt();
                if(tmp > 0)
                    return table.calc.FAHRT.sum() / tmp;
                return table.calc.FAHRT.sum();
            }
        },
        ESSEN: {
            "pos": table.bez.ESSEN,
            "sum": function(){
                if(!table.base.VAR || !table.base.VAR.H_AB || !table.base.VAR.F_FR || !table.base.VAR.G_MI)
                    return 0;

                var tmp = 0;
                for(var len = table.base.VAR.H_AB.length; len--;)
                    tmp += (table.base.VAR.H_AB[len].val * 1);
                for(var len = table.base.VAR.F_FR.length; len--;)
                    tmp += (table.base.VAR.F_FR[len].val * 1);
                for(var len = table.base.VAR.G_MI.length; len--;)
                    tmp += (table.base.VAR.G_MI[len].val * 1);
                return tmp;
            },
            "cnt": function(){
                if(!table.base.VAR || !table.base.VAR.H_AB || !table.base.VAR.F_FR || !table.base.VAR.G_MI)
                    return 0;

                var tmp = 0;
                for(var len = table.base.VAR.F_FR.length; len--;)
                    if(table.base.VAR.F_FR[len].val > 0)
                        tmp++;
                for(var len = table.base.VAR.G_MI.length; len--;)
                    if(table.base.VAR.G_MI[len].val > 0)
                        tmp++;
                for(var len = table.base.VAR.H_AB.length; len--;)
                    if(table.base.VAR.H_AB[len].val > 0)
                        tmp++;
                return tmp;
            },
            "val": function(){
                var tmp = table.calc.ESSEN.cnt();
                if(tmp > 0)
                    return table.calc.ESSEN.sum() / tmp;
                return table.calc.ESSEN.sum();
            }
        },
        FIX: {
            "pos": table.bez.FIX,
            "sum": function(){
                if(!table.base.ALL || !table.base.ALL.B_FIX || !table.base.ALL.C_BW)
                    return 0;
                return (table.base.ALL.B_FIX * 1) + (table.base.ALL.C_BW * 1);
            },
            "cnt": function(){
                if(!table.base.ALL || !table.base.ALL.B_FIX || !table.base.ALL.C_BW)
                    return 0;
                return (table.base.ALL.B_FIX > 0 ? 1 : 0) + (table.base.ALL.C_BW > 0 ? 1 : 0);
            },
            "val": function(){
                var tmp = table.calc.FIX.cnt();
                if(tmp > 0)
                    return table.calc.FIX.sum() / tmp;
                return table.calc.FIX.sum();
            }
        },
        UE: {
            "pos": table.bez.UE,
            "sum": function(){
                if(!table.base.VAR || !table.base.VAR.D_UE)
                    return 0;

                var tmp = 0;
                for(var len = table.base.VAR.D_UE.length; len--;)
                    tmp += (table.base.VAR.D_UE[len].val * 1);
                return tmp;
            },
            "cnt": function(){
                if(!table.base.VAR || !table.base.VAR.D_UE)
                    return 0;

                var tmp = 0;
                for(var len = table.base.VAR.D_UE.length; len--;)
                    if(table.base.VAR.D_UE[len].val > 0)
                        tmp++;
                return tmp;
            },
            "val": function(){
                var tmp = table.calc.UE.cnt();
                if(tmp > 0)
                    return table.calc.UE.sum() / tmp;
                return table.calc.UE.sum();
            }
        },
        WIR: {
            "pos": table.bez.WIR,
            "sum": function(){
                if(!table.base.ALL || !table.base.ALL.E_ESS)
                    return 0;
                return table.base.ALL.E_ESS * 1;
            },
            "cnt": function(){
                if(!table.base.ALL || !table.base.ALL.E_ESS)
                    return 0;
                return table.base.ALL.E_ESS > 0 ? 1 : 0;
            },
            "val": function(){
                var tmp = table.calc.WIR.cnt();
                if(tmp>0)
                    return table.calc.WIR.sum() / tmp;
                return table.calc.WIR.sum();
            }
        },
        X_REFRA: {
            "pos": table.bez.REFRA,
            "sum": function(){
                if(!table.base.ALL || !table.base.ALL.REFRA)
                    return 0;
                return table.base.ALL.REFRA;
            },
            "cnt": function(){
                return 1;
            },
            "val": function(){
                return table.calc.X_REFRA.sum();
            }
        }
    };

    table.sum = function(){
        return table.calc.ESSEN.sum() + table.calc.WIR.sum() + table.calc.FAHRT.sum() + table.calc.FIX.sum() + table.calc.UE.sum();
    }

}]);


/*
    Data Handler for shopping table
 */
dataapp.service('shoppingData', ["$http", "$rootScope", function ($http, $rootScope) {
    var table = this;
    table.entries = [];

    $http.get('?page=cost&ajax=get-shopping-json').success(function(data){
        if(data !== ""){
            table.entries = data;
            console.log("got shopping table from DB");
        } else {
            table.entries = defaultData.shopping;
            console.error("Shopping table not loaded from DB - took default!");
        }

        $rootScope.$broadcast('data::shoppingUpdated', table.entries);
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

}]);

/*
    Data Handler for receipt table
 */
dataapp.service('receiptData', ["$http", "$rootScope", function ($http, $rootScope) {
    var table = this;
    table.entries = [];

    $http.get('?page=cost&ajax=get-receipt-json').success(function(data){
        if(data !== ""){
            table.entries = data;
            console.log("got receipt table from DB");
        } else {
            table.entries = defaultData.receipt;
            console.error("Receipt table not loaded from DB - took default!");
        }
        $rootScope.$broadcast('data::receiptUpdated', table.entries);
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

}]);




/*  END: Data Module

========================================================================================================================

now the individual controllers and modules for each table....

 */

/* MAIN module */
(function() {
    var app = angular.module('pages-cost', ['price','shopping','receipt','moneyio']);

    app.filter('currency', ["$filter", function($filter) {
        return function(input, curSymbol, decPlaces, thouSep, decSep) {
            curSymbol = curSymbol || " €";
            decPlaces = decPlaces || 2;
            thouSep = thouSep || " ";
            decSep = decSep || ".";

            // Check for invalid inputs
            var out = isNaN(input) || input === '' || input === null ? 0.0 : input;

            //Deal with the minus (negative numbers)
            var minus = input < 0;
            out = Math.abs(out);
            out = $filter('number')(out, decPlaces);

            // Replace the thousand and decimal separators.
            // This is a two step process to avoid overlaps between the two
            if(thouSep != ",") out = out.replace(/\,/g, "T");
            if(decSep != ".") out = out.replace(/\./g, "D");
            out = out.replace(/T/g, thouSep);
            out = out.replace(/D/g, decSep);

            // Add the minus and the symbol
            if(minus){
                return "-"  + out + curSymbol;
            }else{
                return out + curSymbol;
            }
        }
    }]);

})();


/* ****************************************************************
 * pricetable module
 */
(function() {
    var app = angular.module('price', ['mgcrea.ngStrap.tooltip', 'dataapp']);

    app.directive("tablePrice", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-price.html"
        };
    });

    app.directive("tablePriceEdit",  function() {

        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-price-edit.html",
            link: function(scope, parent){
                scope.tooltip = [];
                scope.tooltip.ind = {
                    "title": "Auch für individuell Reisende zu zahlen",
                    "checked": false
                };
                scope.tooltip.an = {
                    "title": "Auch zu zahlen, wenn Anreise an dem Tag",
                    "checked": false
                };
                scope.tooltip.ab = {
                    "title": "Auch zu zahlen, wenn Abreise an dem Tagn",
                    "checked": false
                };
            }
        };
    });

    app.controller('TablePriceController', ["$http", "$scope", "priceData", function($http, $scope, priceData){
        var table = this;

        table.editmode = false;
        table.edit = [];

        // === Data Binding stuff ==

        $scope.dataService = priceData;

        table.base = $scope.dataService.base;
        table.calc = $scope.dataService.calc;
        table.bez  = $scope.dataService.bez;

        $scope.$on('data::priceUpdated', function(event, newTab) {
            table.base = newTab;
            console.log("data in price controller received");
        });

        $scope.$watch('table.base', function() {
            if(table.base && $scope.dataService.base){
                $scope.dataService.base = table.base;
            }
        });


        // == table manipulation functions ==

        table.toggleEditmode = function(){
            table.editmode = table.editmode == false;
        }

        table.editTable = function(){
            table.edit = jQuery.extend(true, {}, table.base);
            table.toggleEditmode();
        }
        table.saveTable = function(){
            table.base = jQuery.extend(true, {}, table.edit);
            $http.post('?page=cost&ajax=set-price-json', table.edit).success(function(data, status, headers, config){
                toastr.success('Saved to Database!')
            });
            table.toggleEditmode();
        }
        table.cancelTable = function(){
            table.edit = [];
            table.toggleEditmode();
        }
        table.editUpdateAll = function(val, pos){
            table.edit.ALL[pos] = val;
        }

    }]);

})();




/* ****************************************************************
 * shoppingtable module
 */
(function() {
    var app = angular.module('shopping', ["xeditable", "dataapp"]);

    app.run(function(editableOptions) {
        editableOptions.theme = 'bs3';
    });

    app.directive("tableShopping", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-shopping.html"
        };
    });

    app.controller('TableShoppingController', ["$scope", "$filter", "$q", "$http", "shoppingData", function($scope, $filter, $q, $http, shoppingData){
        var table = this;


        // === Data Binding stuff ==

        $scope.dataService = shoppingData;

        table.entries = $scope.dataService.entries;

        $scope.$on('data::shoppingUpdated', function(event, newTab) {
            table.entries = newTab;
            console.log("data in shopping controller received");
        });

        $scope.$watch('table.entries', function() {
            if(table.entries && $scope.dataService.entries){
                $scope.dataService.entries = table.entries;
            }
        });



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
    var app = angular.module('receipt', ['dataapp']);

    app.directive("tableReceipt", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-receipt.html"
        };
    });

    app.controller('TableReceiptController', ["$scope", "$filter", "$q", "$http", "receiptData", function($scope, $filter, $q, $http, receiptData){
        var table = this;


        // === Data Binding stuff ==

        $scope.dataService = receiptData;

        table.entries = $scope.dataService.entries;

        $scope.$on('data::receiptUpdated', function(event, newTab) {
            table.entries = newTab;
            console.log("data in receipt controller received");
        });

        $scope.$watch('table.entries', function() {
            if(table.entries && $scope.dataService.entries){
                $scope.dataService.entries = table.entries;
            }
        });




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
    var app = angular.module('moneyio', [ ]);

    app.directive("tableMoneyio", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-moneyio.html"
        };
    });

    app.directive("tableMoneyioCol", ["currencyFilter", function(currency) {

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

                scope.curr = function(e){
                    return currency(e);
                }
            }

        };
    }]);

    app.controller('TableMoneyioController', ["$scope", "$filter", "$q", "$http", "$rootScope", function($scope, $filter, $q, $http, $rootScope){
        var table = this;

        table.entries = {
                "in": [],
                "out": []
            };


        $http.get('?page=cost&ajax=get-moneyio-json').success(function(data){
            if(data !== "")
                table.entries = data;
            else {
                table.entries = defaultData.moneyio;
                console.error("MoneyIO table not loaded from DB - took default!");
            }
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

})();








/* ======================================================================================================
Mülltonne
 */

/*
var dataapp = angular.module("tabdata",[]);
dataapp.factory("DataService", ["$http", "$rootScope", function($http, $rootScope){

    var dataService = {
        tableShoppingData: { entries: [] }
    };

    $http.get('?page=cost&ajax=get-shopping-json').success(function(data){
        if(data !== ""){
            dataService.tableShoppingData.entries = data;
            $rootScope.$broadcast("valuesUpdated");
        }
    });

    return dataService;

}]);
dataapp.controller('dataController', function($scope, dataService) {
    $scope.shared = dataService;
});*/

/*
inject data to shopping
 var tmp =
 [
 {
 "pos": "Limonade",
 "cnt": "76",
 "price": "1.43"
 },
 {
 "pos": "Brause",
 "cnt": "42",
 "price": "0.83"
 },
 {
 "pos": "Lutscher",
 "cnt": "1",
 "price": "0.5"
 },
 ];
 */