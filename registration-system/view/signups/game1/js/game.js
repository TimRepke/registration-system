function Game(config) {
	if (!(this instanceof Game)) throw "'Game' needs to be constructed";
	if (Game.instance) throw "'Game' already constructed";
	Game.config = config;
	Game.instance = this;
}
Game.prototype.run = function() {
	d3.xml('maps/'+Game.config.startMap, 'image/svg+xml', function(xml) {
		var gameCanvas = document.getElementById("gameCanvas");
		var gameRoot = document.getElementById("gameRoot");
		gameCanvas.style.width = Game.config.size[0]+'px';
		gameCanvas.style.height = Game.config.size[1]+'px';
		gameRoot.appendChild(xml.documentElement);
	
		var svg = d3.select("svg");
		var char = new Char(svg);
		var cam = new Camera(svg, char.translation);
	
		// test animation
		var ship = svg.select("#shipGroup");
		ship
			.attr("transform", function(d,i) { return "translate(200,000)"; });
		ship.transition()
			.duration(3000)
			.attr("transform", function(d,i) { return "translate(0,0)"; });
	
		// animate
		setInterval(function() {
			if (char.loaded) {
				// move player
				char.physics();
				char.animate();
				// cam movement
				cam.movement();
			}
		}, 10);
	
		svg.on("click", function(d) {
			char.setMoveTarget(d3.event.pageX, d3.event.pageY);
		});
	});
}

