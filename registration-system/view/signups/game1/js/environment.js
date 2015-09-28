function Environment () {
  // something?
}
Environment.fapi = new FAPI();

// can be used to turn individual sounds on/off
Environment.sound = {
    achievements: false,
    log: true
};

Environment.progress = {
    // -----------------------------
    // MAP RELATED things

    // castle entrace related
    castlee_niveauDropped: false,

    // fs related
    fs_firstApproach: false,
    fs_georgeScreamed: false,
    fs_filledBoard: false,

    // landing map related
    landing_askedNickname: false,
    landing_killedGoat: false,
    landing_dorfEntranceApproach: false,
    landing_ageChosen: false,
    landing_enteredCastle: false,

    // dorf related
    dorf_talkedToWirt: false,
    dorf_pickedFood: false,
    dorf_pickedFootAndLeftPub: false,
    dorf_boughtTicket: false,
    sleep_inn: false,

    // ufer related
    ufer_princessApproach: false,
    ufer_pickedTransport: false,


    // -----------------------------
    // INVENTORY
    inventory_money: false,
    inventory_ruestung: false,
    inventory_goatDroppings: false
};

Environment.mapEvents = {
    'map_landing': {
        init: function(svg) {
            if (Environment.progress.fs_filledBoard) {
                svg.select('#construction').remove();
                svg.select('#construction_nowalk').remove();
            } else {
                var ship = svg.select("#shipGroup");
                ship
                    .attr("transform", function (d, i) {
                        return "translate(200,000)";
                    });
                ship.transition()
                    .duration(3000)
                    .attr("transform", function (d, i) {
                        return "translate(0,0)";
                    });
            }
        }
    },
    'castle_entrance': {
        init: function(svg) {
            if (!Environment.progress.landing_enteredCastle) {
                Game.log("Geh in die Fachschaft");
                Environment.progress.landing_enteredCastle = true;
            }
            if (Environment.progress.inventory_money)
                d3.select('#moneybags').attr('opacity', 0);
        }
    },
    'castle_fs': {
        init: function(svg) {

        }
    },
    'dorf': {
        init: function(svg) {
            if (!Environment.progress.dorf_pickedFood) {
                Game.log("Geh ins Wirtshaus");
            } else if (!Environment.progress.dorf_pickedFootAndLeftPub && !Environment.progress.dorf_boughtTicket) {
                Environment.progress.dorf_pickedFootAndLeftPub = true;
                Game.log("Geh zum Prinzessinenreisebüro");
            }
            if (!Environment.progress.dorf_pickedFood || Environment.progress.dorf_boughtTicket) {
                svg.select('#ticketfrau').style('display', 'none');
            }
        }
    },
    'shop': {
        init: function(svg) {

        }
    },
    'ufer': {
        init: function(svg) {
            Story.dialogueHelper([{message: 'Wow,... war das?!'}, {message: "Da ist ja die Prinzessin, vielleicht weiß sie mehr..."}])
        }
    }
};