function Game(config) {
	if (!(this instanceof Game)) throw "'Game' needs to be constructed";
	if (Game.instance) throw "'Game' already constructed";
	Game.config = config;
	Game.instance = this;

	Game.achievements = new Achievements();
	Game.eventHandler = null;
	Game.char = null;
	Game.cam  = null;
}
Game.eventLayers = ['CLICKABLE', 'WALK', 'NOWALK', 'EVENT'];
Game.prototype.run = function() {
	d3.xml(FAPI.resolvePath('maps/'+Game.config.startMap), 'image/svg+xml', function(xml) {
		var gameCanvas = document.getElementById("gameCanvas");
		var gameRoot = document.getElementById("gameRoot");
		gameCanvas.style.width = Game.config.size[0]+'px';
		gameCanvas.style.height = Game.config.size[1]+'px';
		gameRoot.appendChild(xml.documentElement);

		var svg = d3.select("svg");

		// -------------------------------------
		// init event related stuff
		var displayEvents = Game.config.showEventLayers ? 'block' : 'none';
		svg.selectAll('g').filter(function() {
			return (
				this.getAttribute('inkscape:groupmode') == 'layer'
				&& Game.eventLayers.indexOf(this.getAttribute('inkscape:label')) >= 0
			);
		}).style('display', displayEvents);

		Game.eventHandler = new EventHandler(svg);

		// -------------------------------------
		// init view stuff
		Game.char = new Char(svg);
		Game.cam = new Camera(svg, Game.char.translation);


		// test animation
		var ship = svg.select("#shipGroup");
		ship
			.attr("transform", function(d,i) { return "translate(200,000)"; });
		ship.transition()
			.duration(3000)
			.attr("transform", function(d,i) { return "translate(0,0)"; });

		// animate
		setInterval(function() {
			if (Game.char.loaded) {
				// move player
				Game.char.physics();
				Game.char.animate();
				// cam movement
				Game.cam.movement();
			}
		}, 10);

		svg.on("click", function(d) {
			var matrix = svg[0][0].getScreenCTM();
			var x = d3.event.pageX-matrix.e;
			var y = d3.event.pageY-matrix.f;
			Game.char.setMoveTarget(x, y);
			Game.eventHandler.triggerEventOn('click', x, y);
		});
	});

};
