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
    ],
    "out": [
        { "pos": "Einkaufen", "val": "354" },
        { "pos": "Busfahrt Hin", "val": "35" },
        { "pos": "Busfahrt Rück", "val": "40" },
        { "pos": "Kaution", "val": "100" }
    ]
};
defaultData.other = {
    "bezahlt" : 0, // number of people who payed
    "count"   : 0, // number of valid registrations
    "amount"  : 0, // amount of money to be collected per person
    "back"    : [],// list of people, who received money back (structure: von, bis, antyp, abtyp)
    "remain"  : [],// list of people, who haven't received money yet (structure: von, bis, antyp, abtyp)
    "fahrt"   : [],// dates of the trip (structure: von, bis)
    "arten"   : [],
    "cnt"     : {"all":0, "geman": 0, "gemab":0}
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

    // receive new base
    this.updateBase = function(b){
        table.base = b;
        $rootScope.$broadcast('data::priceUpdated', table.base);
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

/*
 Data Handler for moneyio table
 */
dataapp.service('moneyioData', ["$http", "$rootScope", function ($http, $rootScope) {
    var table = this;

    table.entries = {
        "in": [],
        "out": []
    };

    $http.get('?page=cost&ajax=get-moneyio-json').success(function(data){
        if(data !== ""){
            table.entries = data;
            console.log("got moneyio table from DB");
        } else {
            table.entries = defaultData.moneyio;
            console.error("moneyio table not loaded from DB - took default!");
        }
        $rootScope.$broadcast('data::moneyioUpdated', table.entries);
    });

    // === basic table functions ===

    table.colSum = function(col, ecol){
        var ret = 0;
        for(var i = col.length; i--;){
            if(!col[i].isDeleted)
                ret += 1*col[i].val;
        }
        for(var row in ecol){
            ret += ecol[row].val;
        }
        return ret;
    };

    table.diffR = function(ex){
        var cp = jQuery.extend(true, {}, ex);
        delete cp.out.POUT;
        delete cp.in.PVOR;
        return table.diff(cp);
    };
    table.diffRColor = function(ex){
        var cp = jQuery.extend(true, {}, ex);
        delete cp.out.POUT;
        delete cp.in.PVOR;
        return table.diffColor(cp);
    };

    table.diff = function(ex){
        return table.colSum(table.entries.in, ex.in) - table.colSum(table.entries.out, ex.out);
    };

    table.diffColor = function(ex){
        var tolerance = 50; // specify the tolerance of diff
        var diff = table.diff(ex);
        var color = "";
        if(diff < 0)
            color = "red";
        else if(diff >= 0 && diff <= tolerance)
            color = "green";
        else if(diff > tolerance)
            color = "yellow";

        return color;
    };

}]);

/*
 Data Handler for other mixed data
 */
dataapp.service('otherData', ["$http", "$rootScope", "priceData", function ($http, $rootScope, priceData) {

    /*defaultData.other = {
        "bezahlt" : 0, // number of people who payed
        "count"   : 0, // number of valid registrations
        "amount"  : 0, // amount of money to be collected per person
        "back"    : [],// list of people, who received money back (structure: von, bis, antyp, abtyp)
        "remain"  : [],// list of people, who haven't received money yet (structure: von, bis, antyp, abtyp)
        "fahrt"   : [] // dates of the trip (structure: von, bis)
        "arten"
    };*/

    var data = defaultData.other;
    var base = priceData.base;
    var fdays = [];
    var func = this;

    $http.get('?page=cost&ajax=get-other-json').success(function(dat){
        if(dat !== ""){
            data = dat;
            console.log("got moneyio table from DB");
        } else {
            console.error("other data not loaded from DB - took default!");
        }
        fdays = func.getDays(data.fahrt.von, data.fahrt.bis);
        $rootScope.$broadcast('data::otherUpdated', data);
    });

    $rootScope.$on('data::priceUpdated', function(event, newTab) {
        base = newTab;
        console.log("data in other service received");
    });

    // === Data aggregation functions ===

    // == local functions ==

    // get js Date from a YYYY-mm-dd string (today if not a string or wrong format)
    func.getDate = function(str){
        if(!(typeof str == "string"))
            return new Date();

        var split = str.split("-");

        if(split.length<3 || split.length>3)
            return new Date();

        return new Date(split[0],split[1]-1, split[2]);
    };
    // get YYYY-mm-dd string from js date object (today if not a date)
    func.dateToStr = function(d){
        if(!typeof d == "Date")
            d = new Date();
        return d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate();
    };
    // get next day of js Date object (tomorrow if not a date)
    func.nextDay = function(d){
        if(!typeof d == "Date")
            d = new Date();
        d.setDate(d.getDate()+1);
    };
    // get array of dates between two dates (given as YYYY-mm-dd string) returns datestrings as key and value is daycount
    // maximum number of entries: 10
    func.getDays = function(vonn, biss){
        var von = func.getDate(vonn);
        var tmp = func.getDate(vonn);
        var bis = func.getDate(biss);
        var days = [];
        for(var i = 0; tmp <= bis && i < 10; i++){ // maximum of 10 days (just in case)
            days[func.dateToStr(tmp)] = i;
            func.nextDay(tmp);
        }
        return days;
    };
    // gets the difference of start days (returns 0 on errors)
    func.startOffset = function(f,p){
        for(var k in p){
            if(p[k]==0){
                if(k in f)
                    return f[k];
                else
                    return 0;
            }
        }
        return 0;
    };

    // == public functions ==

    // amount to be paid to person (returns array: [val:<amount to pay>, pos:[{pos:<position>, val:<value>},...])
    this.repayAmount = function(person){
        var pos = [];

        if(base.ALL && base.VAR){
            pos[pos.length] = {"pos": "(Vorkasse)", "val": data.amount};
            pos[pos.length] = {"pos": priceData.bez.REFRA, "val": base.ALL.REFRA};
            pos[pos.length] = {"pos": priceData.bez.B_FIX, "val": (-1)*base.ALL.B_FIX};
            pos[pos.length] = {"pos": priceData.bez.C_BW, "val": (-1)*base.ALL.C_BW};
            pos[pos.length] = {"pos": priceData.bez.E_ESS, "val": (-1)*base.ALL.E_ESS};

            // person: Object {von: "2013-10-18", bis: "2013-10-20", antyp: "gemeinsam mit Bus/Bahn", abtyp: "gemeinsam mit Bus/Bahn"}
            // fahrt: Object {von: "2013-10-25", bis: "2013-10-27"}

            var pdays = func.getDays(person.von, person.bis);
            var firstDay = true, lastDay = false;
            var startOffset = func.startOffset(fdays,pdays);
            var indAn = data.arten.BUSBAHN != person.antyp;
            var indAb = data.arten.BUSBAHN != person.abtyp;
            for(var pday in pdays){
                firstDay = (pdays[pday] == 0               );
                lastDay  = (pdays[pday] == (Object.keys(pdays).length - 1));

                var index = 0;
                for(var tpos in base.VAR){ // for each position of base table
                    index = pdays[pday]+startOffset;
                    if(index in base.VAR[tpos] && base.VAR[tpos][index].val > 0){
                        if(firstDay){
                            if(base.VAR[tpos][index].an){
                                if(!indAn || (indAn &&base.VAR[tpos][index].ind)){
                                    pos[pos.length] = {"pos": priceData.bez[tpos], "val": (-1)*base.VAR[tpos][index].val};
                                }
                            }
                        } else if(lastDay){
                            if(base.VAR[tpos][index].ab){
                                if(!indAb || (indAb &&base.VAR[tpos][index].ind)){
                                    pos[pos.length] = {"pos": priceData.bez[tpos], "val": (-1)*base.VAR[tpos][index].val};
                                }
                            }
                        } else {
                            pos[pos.length] = {"pos": priceData.bez[tpos], "val": (-1)*base.VAR[tpos][index].val};
                        }
                    }
                }
            }
        }

        var val = 0;
        for(var len = pos.length; len--;){
            val += pos[len].val*1;
        }

        return {"val": ((val>data.amount) ? data.amount : val), "rval": val, "pos": pos};
    };

    // get list of persons with the attached amounts + sum
    this.getPaymentList = function(){
        var ret = {
            "repaid": [],
            "toPay":  []
        };

        for(var len = data.back.length; len--;){
            ret.repaid[ret.repaid.length] = {
                "id":   data.back[len].bachelor_id,
                "name": data.back[len].forname + " " + data.back[len].sirname,
                "pay":  this.repayAmount(data.back[len]),
                "person": data.back[len]
            };
        }
        for(var len = data.remain.length; len--;){
            ret.toPay[ret.toPay.length] = {
                "id":   data.remain[len].bachelor_id,
                "name": data.remain[len].forname + " " + data.remain[len].sirname,
                "pay":  this.repayAmount(data.remain[len]),
                "person": data.remain[len]
            };
        }

        return ret;
    };

    // sum of all amounts that have been paid back
    this.repaidSum = function(){
        var ret = 0;
        for(var len = data.back.length; len--;){
            ret += this.repayAmount(data.back[len]).val * 1;
        }
        return ret;
    };

    // sum of all amounts that still have to be paid back
    this.toPaySum = function(){
        var ret = 0;
        for(var len = data.remain.length; len--;){
            ret += this.repayAmount(data.remain[len]).val * 1;
        }
        return ret;
    };

    // sum of all amounts paid back or still to be paid back
    this.paySum = function(){
        return this.repaidSum() * 1 + this.toPaySum() * 1;
    };

    // product of amount to be paid and number of people who paid
    this.intakeSum = function(){
        return data.amount * data.bezahlt;
    };
    this.toIntakeSum = function(){
        return data.amount * (data.count-data.bezahlt);
    };

    // update amount function
    this.updateAmount = function(a){
        data.amount = a;
    }

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
    var app = angular.module('price', ["xeditable", 'mgcrea.ngStrap.tooltip', 'dataapp']);

    app.directive("tablePrice", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-price.html",
            link: function(scope, parent){
                scope.showdetails = false;
                scope.toggleDetails = function(){
                    scope.showdetails = scope.showdetails == false;
                }
            }
        };
    });

    app.directive("tablePriceList", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-price-list.html"
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

    app.directive("tablePriceHelper", function() {
        return {
            restrict: 'E',
            templateUrl: "pages_cost/table-price-helper.html",
            link: function(scope, parent){
                scope.helpermod = 0;
                scope.cnt = 0;
                scope.special = 0;

                scope.summy = function(tab){
                    var ret = 0;
                    var mul = 1;
                    for(var len = tab.in.length; len--;){
                        mul = 1;
                        if(tab.in[len].selected){
                            if(tab.in[len].neg) mul = -1;
                            ret += tab.in[len].val*mul;
                        }
                    }
                    for(var len = tab.out.length; len--;){
                        mul = 1;
                        if(tab.out[len].selected){
                            if(tab.out[len].neg) mul = -1;
                            ret += tab.out[len].val*mul;
                        }
                    }
                    ret += scope.special*1;
                    return ret;
                };
                scope.resetty = function(tab){
                    for(var len = tab.in.length; len--;){
                        delete tab.in[len].selected;
                        delete tab.in[len].neg;
                    }
                    for(var len = tab.out.length; len--;){
                        delete tab.out[len].selected;
                        delete tab.out[len].neg;
                    }
                    scope.special = 0;
                };
                scope.meaner = function(tab){
                    if(scope.cnt<1) scope.cnt = 1;
                    return scope.summy(tab) / scope.cnt;
                }
            }
        };
    });

    app.controller('TablePriceController', ["$http", "$scope", "priceData", "otherData", "moneyioData", function($http, $scope, priceData, otherData, moneyioData){
        var table = this;

        table.editmode = false;
        table.listmode = false;
        table.edit = [];
        table.list = [];

        // === Data Binding stuff ==

        $scope.dataService = priceData;
        $scope.otherDataService = otherData;
        $scope.moneyioDataService = moneyioData;

        table.base = $scope.dataService.base;
        table.calc = $scope.dataService.calc;
        table.bez  = $scope.dataService.bez;
        table.amount = $scope.otherDataService.amount;
        table.io   = $scope.moneyioDataService.entries;
        table.cnt = [];

        $scope.$on('data::priceUpdated', function(event, newTab) {
            table.base = newTab;
            console.log("data in price controller received");
        });
        $scope.$on('data::otherUpdated', function(event, newTab) {
            table.amount = newTab.amount;
            table.cnt    = newTab.cnt;
            console.log("amount in price controller received");
        });
        $scope.$on('data::moneyioUpdated', function(event, newTab) {
            table.io = newTab;
            console.log("amount in price controller received");
        });

        $scope.$watch('table.base', function() {
            if(table.base && $scope.dataService.base){
                $scope.dataService.base = table.base;
            }
        });


        // == table manipulation functions ==

        table.toggleEditmode = function(){
            table.editmode = table.editmode == false;
        };
        table.toggleListmode = function(){
            table.listmode = table.listmode == false;
        };

        table.listPay = function(){
            table.list = $scope.otherDataService.getPaymentList();
            table.toggleListmode();
        };

        table.editTable = function(){
            table.edit = jQuery.extend(true, {}, table.base);
            table.io   = jQuery.extend(true, {}, table.io);
            table.toggleEditmode();
        };
        table.saveTable = function(){
            table.base = jQuery.extend(true, {}, table.edit);
            $http.post('?page=cost&ajax=set-price-json', table.edit).success(function(data, status, headers, config){
                toastr.success('Saved to Database! (price)');
            });
            $http.get('?page=cost&ajax=set-amount&data='+table.amount).success(function(data){
                toastr.success('Saved to Database! (amount)');
            });
            $scope.otherDataService.updateAmount(table.amount);
            $scope.dataService.updateBase(table.base);
            table.toggleEditmode();
        };
        table.cancelTable = function(){
            table.edit = [];
            table.toggleEditmode();
        };
        table.editUpdateAll = function(val, pos){
            table.edit.ALL[pos] = val;
        };

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

        // save copy of table before editing to have something to reset to
        $scope.prepareForm = function(){
            table.orig = jQuery.extend(true, {}, table.entries);
        };

        // cancel all changes
        $scope.cancel = function() {
            table.entries = jQuery.extend(true, {}, table.orig);
            table.orig    = [];
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
            table.orig    = [];

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
    var app = angular.module('receipt', ["xeditable", 'dataapp']);

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

        // save copy of table before editing to have something to reset to
        $scope.prepareForm = function(){
            table.orig = jQuery.extend(true, {}, table.entries);
        };

        // cancel all changes
        $scope.cancel = function() {
            table.entries = jQuery.extend(true, {}, table.orig);
            table.orig    = [];
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
            table.orig    = [];

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
    var app = angular.module('moneyio', [ "xeditable", 'dataapp']);

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
                tform: '=',
                extra: '='
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

    app.controller('TableMoneyioController',
            ["$scope", "$filter", "$q", "$http", "moneyioData", "otherData", "receiptData",
            function($scope, $filter, $q, $http, moneyioData, otherData, receiptData){
        var table = this;

        // === Data Binding stuff ==

        $scope.moneyioDataService = moneyioData;
        $scope.otherDataService   = otherData;
        $scope.receiptDataService = receiptData;

        table.entries = $scope.moneyioDataService.entries;
        table.orig = [];

        table.extra = {
            "in": {
                VOR: {"pos": "Vorkasse", "val": 0},
                PVOR: {"pos": "Vorkasse (ausstehend)", "val": 0}
            },
            "out":{
                REC: {"pos": "Herberge", "val": 0},
                OUT: {"pos": "Rückzahlungen (getätigt)", "val": 0},
                POUT: {"pos": "Rückzahlungen (ausstehend)", "val": 0}
            }
        };

        $scope.$on('data::moneyioUpdated', function(event, newTab) {
            table.entries = newTab;
            console.log("data in moneyio controller received");
        });
        $scope.$on('data::otherUpdated', function(event, newTab) {
            table.extra.in.VOR.val  = $scope.otherDataService.intakeSum();
            table.extra.in.PVOR.val  = $scope.otherDataService.toIntakeSum();
            table.extra.out.OUT.val = $scope.otherDataService.repaidSum();
            table.extra.out.POUT.val = $scope.otherDataService.toPaySum();
            console.log("other in moneyio controller received");
        });
        $scope.$on('data::receiptUpdated', function(event, newTab) {
            table.extra.out.REC.val = $scope.receiptDataService.sum();
            table.extra.in.VOR.val  = $scope.otherDataService.intakeSum();
            table.extra.in.PVOR.val  = $scope.otherDataService.toIntakeSum();
            table.extra.out.OUT.val = $scope.otherDataService.repaidSum();
            table.extra.out.POUT.val = $scope.otherDataService.toPaySum();
            console.log("receipt in moneyio controller received");
        });

        $scope.$watch('table.entries', function() {
            if(table.entries && $scope.moneyioDataService.entries){
                $scope.moneyioDataService.entries = table.entries;
            }
        });



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

        // save copy of table before editing to have something to reset to
        $scope.prepareForm = function(){
            table.orig = jQuery.extend(true, {}, table.entries);
        };

        // cancel all changes
        $scope.cancel = function() {
            table.entries = jQuery.extend(true, {}, table.orig);
            table.orig    = [];
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
            table.orig = [];

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