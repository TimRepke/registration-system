function Game(config) {
    if (!(this instanceof Game)) throw "'Game' needs to be constructed";
    if (Game.instance) throw "'Game' already constructed";
    Game.config = config;
    Game.config.loopSpeed = 10;
    Game.instance = this;

    Game.achievements = new Achievements();
    Game.eventHandler = null;
    Game.char = null;
    Game.cam = null;
    Game.mainLoop = null;
    Game.actionsBlocked = false;
}
Game.eventLayers = ['CLICKABLE', 'WALK', 'NOWALK', 'EVENT'];

Game.prototype.run = function () {
    this.loadMap(Game.config.startMap);
};

Game.prototype.nextMap = function (map, spawn) {
    clearInterval(Game.mainLoop);
    Game.mainLoop = null;
    var self = this;
    setTimeout(function() {
        var gameRoot = document.getElementById("gameRoot");
        while (gameRoot.firstChild) {
            gameRoot.removeChild(gameRoot.firstChild);
        }
        Game.char = null;
        Game.cam = null;
        self.loadMap(map, spawn);
    }, Game.config.loopSpeed+5);
};

Game.prototype.loadMap = function (map, spawn) {
    var gameCanvas = document.getElementById("gameCanvas");
    var gameRoot = document.getElementById("gameRoot");

    var svg = null;

    var initstack = [
        [initMap, map, spawn],
        [initMouse],
        [startMainLoop]
    ];

    function init(done) {
        var next = initstack.shift();
        if (next) next[0].apply(null, (next.slice(1) || []).concat(init));
    }

    init();


    function initMap(mapId, spawn, done) {
        console.log('Init map: ' + mapId + ' spawn: ' + spawn);
        d3.xml(FAPI.resolvePath('maps/' + mapId + '.svg'), 'image/svg+xml', function (xml) {

            gameCanvas.style.width = Game.config.size[0] + 'px';
            gameCanvas.style.height = Game.config.size[1] + 'px';
            gameRoot.appendChild(xml.documentElement);

            svg = d3.select("svg");

            // -------------------------------------
            // init event related stuff
            var displayEvents = Game.config.showEventLayers ? 0.5 : 0;
            svg.selectAll('g').filter(function () {
                return (
                    this.getAttribute('inkscape:groupmode') == 'layer'
                    && Game.eventLayers.indexOf(this.getAttribute('inkscape:label')) >= 0
                );
            })
                .style('display', 'block')
                .style('opacity', displayEvents);

            Game.eventHandler = new EventHandler(svg);

            // hide special elements
            svg.selectAll('[special_elem]').style('display', 'none');

            // -------------------------------------
            // init map specific things
            Environment.mapEvents[mapId].init(svg);

            // -------------------------------------
            // init view stuff
            Game.char = new Char(svg, {spawnid: spawn});
            Game.cam = new Camera(svg, Game.char.translation);

            done();

        });
    }

    function initMouse(done) {
        var mousePointer = svg.append("circle").attr("r", 10).style("opacity", 0.5).style("display", 'none');
        svg.on("click", function (d) {
            if (Game.actionsBlocked) return;
            var xy = getMouseXY();
            if (xy) {
                if (!Game.eventHandler.triggerEventOn('click', xy.x, xy.y)) {
                    Game.char.setMoveTarget(xy.x, xy.y);
                }
            }
        }).on('mouseenter', function () {
            mousePointer.style('display', 'block')
        }).on('mouseleave', function () {
            mousePointer.style('display', 'none')
        }).on('mousemove', function () {
            var xy = getMouseXY();
            mousePointer.attr('cx', xy.x);
            mousePointer.attr('cy', xy.y);
            var colour = Game.char.pathFinder.canWalkOn(xy.x, xy.y) ? 'green' : 'red';
            if (Game.eventHandler.hasEventOn('click', xy.x, xy.y)) colour = 'blue';
            if (Game.actionsBlocked) colour = 'red';
            mousePointer.style("fill", colour);
        });

        done();
    }

    function startMainLoop() {
        Game.mainLoop = setInterval(function () {
            if (Game.char && Game.char.loaded) {
                // move player
                Game.char.physics();
                Game.char.animate();
                // cam movement
                Game.cam.movement();
            }
        }, Game.config.loopSpeed);

    }

    function getMouseXY() {
        try {
            var rawCoords = d3.mouse(gameCanvas);
            var cleanCoords = {
                x: (rawCoords[0] < 0) ? 0 : ((rawCoords[0] > Game.config.size[0]) ? Game.config.size[0] : rawCoords[0]),
                y: (rawCoords[1] < 0) ? 0 : ((rawCoords[1] > Game.config.size[1]) ? Game.config.size[1] : rawCoords[1])
            };

            // calculation of top/left offset taken from prototypeJS
            // https://github.com/sstephenson/prototype/blob/8d968bf957f0c41e5fcc665860d63a98a3fd26a0/src/prototype/dom/layout.js#L1156
            var offsetTop = 0, offsetLeft = 0, docBody = document.body;
            var element = gameCanvas;
            /*do {
             offsetTop += element.offsetTop  || 0;
             offsetLeft += element.offsetLeft || 0;
             // Safari fix
             if (element.offsetParent == docBody &&
             element.style.position == 'absolute') break;
             } while (element = element.offsetParent);*/
            var offset = $(element).offset();
            var offsetTop = offset.top;
            var offsetLeft = offset.left;

            element = gameCanvas;
            do {
                // Opera < 9.5 sets scrollTop/Left on both HTML and BODY elements.
                // Other browsers set it only on the HTML element. The BODY element
                // can be skipped since its scrollTop/Left should always be 0.
                if (element != docBody) {
                    offsetTop -= element.scrollTop || 0;
                    offsetLeft -= element.scrollLeft || 0;
                }
            } while (element = element.parentNode);
            // ^--------- from prototypeJS

            var matrix = svg[0][0].getScreenCTM();
            cleanCoords.x = cleanCoords.x - matrix.e + offsetLeft;
            cleanCoords.y = cleanCoords.y - matrix.f + offsetTop;

            return cleanCoords;
        } catch (e) {
            console.error(e);
            return undefined;
        }

    }

};

Game.log = function (message) {
    if (Environment.sound.log) new Audio(FAPI.resolvePath('sounds/plop.ogg')).play();
    var list = document.getElementById('game-log');

    var newElem = document.createElement('li');
    var newElemText = document.createTextNode(message);
    newElem.appendChild(newElemText);

    newElem.style.backgroundColor = '#474c46';
    setTimeout(function(){
        newElem.style.background = 'transparent';
    }, 1000);

    list.insertBefore(newElem, list.childNodes[0]);
};

