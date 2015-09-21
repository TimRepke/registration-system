function Environment () {
  // something?
}
Environment.fapi = new FAPI();

Environment.progress = {
    // -----------------------------
    // MAP RELATED things

    // fs related
    fs_firstApproach: false,
    fs_georgeScreamed: false,
    fs_filledBoard: false,

    // landing map related
    landing_askedNickname: false,
    landing_killedGoat: false,
    landing_dorfEntranceApproach: false,
    landing_ageChosen: false,


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

        }
    },
    'castle_fs': {
        init: function(svg) {

        }
    },
    'dorf': {
        init: function(svg) {

        }
    }
};