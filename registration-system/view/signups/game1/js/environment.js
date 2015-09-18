function Environment () {
  // something?
}

Environment.progress = {
    fs_firstApproach: false,
    fs_georgeScreamed: false,
    fs_filledBoard: false,
    killedGoat: false
};

Environment.inventory = {
    money: false,
    goatDrops: false
};

Environment.mapEvents = {
    'map_landing': {
        init: function(svg) {
            if (Environment.progress.fs_filledBoard) {
                // TODO: remove baustelle (that doesn't exist yet)
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