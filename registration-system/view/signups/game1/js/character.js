function Char(svg, options) {
	if (!options) options = {};
	this.svg = svg;
	this.pathFinder = new PathFinder(svg);

	this.translation = options.spawn ? options.spawn : this.findSpawn(options ? options.spawnid : null);
	this.moveTarget = [];
	this.maxSpeed = 2;
	this.loaded = false;

	var self = this;
	d3.xml(Environment.fapi.resolvePath('chars/bernd.svg'), 'image/svg+xml', function(xml) {
		self.image = self.svg.append('g').attr('id', 'player');
		var layers = d3.select(xml.documentElement).selectAll('g').filter(function() {
			return this.getAttribute('inkscape:groupmode') == 'layer';
		}).each(function() {
			self.image[0][0].appendChild(this);
		});

		self.initializeAnimations();
		self.updatePosition();

		self.loaded = true;
	});
}
Char.prototype.findSpawn = function(spawnId) {
	if (!spawnId) spawnId = 'player_spawn';
	var spawn = this.svg.select('#'+spawnId);
	if (!spawn[0][0]) console.error("Could not find spawn: #" + spawnId);
	var bbox = spawn[0][0].getBBox();
	return Vec.add(getTranslation(spawn[0][0], this.svg[0][0]), [bbox.x, bbox.y]);
};
Char.prototype.initializeAnimations = function() {
	var self = this;

	this.animateStep = 0;
	this.lastPosition = this.translation.slice();
	this.lastDirection = 2;
	this.currentFrame = 0;
	this.frames = null;

	this.animations = {};

	this.image.selectAll('g').filter(function() {
		return this.getAttribute('inkscape:groupmode') == 'layer';
	}).each(function() {
		var label = this.getAttribute('inkscape:label');

		var element = d3.select(this)
			.style('display', 'block');

		var frames = [];
		element.selectAll('image')
			.style('display', 'none')
			.each(function() {
				frames.push(this);
			});

		self.animations[label] = frames;
	});
};
Char.directionToName = ["UP", "RIGHT", "DOWN", "LEFT"];
Char.prototype.animate = function(force) {
	if(force) {
		this.animateStep = 0;
	} else {
		this.animateStep += 1;
		if (this.animateStep <= 8) return;
		while (this.animateStep > 8)
			this.animateStep -= 8;
	}
	var xSpeed = this.translation[0] - this.lastPosition[0];
	var ySpeed = - this.translation[1] + this.lastPosition[1];
	this.lastPosition = this.translation.slice();
	var speed = Math.max(Math.abs(xSpeed), Math.abs(ySpeed)); // estimate

	var postfix = Environment.progress.inventory_ruestung ? "_r" : "";

	var direction = this.lastDirection;
	if (speed > g_smallValue) {
		if (Math.abs(xSpeed) >= Math.abs(ySpeed))
			direction = (xSpeed >= 0) ? 1 : 3;
		else
			direction = (ySpeed >= 0) ? 0 : 2;
	}

	var currentFrame = this.currentFrame;
	if (currentFrame > 0 && Game.config.moonWalk)
		currentFrame = this.frames.length - currentFrame;

	// hide last visible frame
	if (this.frames && this.frames[currentFrame]) {
		var lastFrame = this.frames[currentFrame];
		lastFrame.style.display = 'none';
	}
	// change animation
	if (direction != this.lastDirection || force) {
		this.lastDirection = direction;
		this.currentFrame = 0;
		this.frames = this.animations[Char.directionToName[direction]+postfix];
	}

	// if no current frames available show fallback downwards frame
	if (!this.frames || this.frames.length == 0)
		this.frames = this.animations['DOWN'+postfix];
	// if everything fails..
	if (!this.frames || this.frames.length == 0)
		return;

	// frames depending on speed
	if (speed < g_smallValue)
		this.currentFrame = 0; // stand still
	else
		this.currentFrame += 1; // walk
	this.currentFrame %= this.frames.length;

	var currentFrame = this.currentFrame;
	if (currentFrame > 0 && Game.config.moonWalk)
		currentFrame = this.frames.length - currentFrame;

	// show current frame
	this.frames[currentFrame].style.display = 'block';
};
Char.prototype.physics = function() {
	var x = this.translation[0];
	var y = this.translation[1];
	var self = this;
	if (!this.pathFinder.canWalkOn(x, y)) {
		var queue = [[x-x%5, y-y%5, null, null]];

		function recoverWalkable() {
			var data = queue.shift();
			var x = data[0];
			var y = data[1];
			var xDir = data[2];
			var yDir = data[3];

			if (self.pathFinder.canWalkOn(x, y)) {
				Vec.assign(self.translation, [x, y]);
				if (this.moveTarget && this.moveTarget.length > 0)
				{
					var lastTarget = self.moveTarget[self.moveTarget.length - 1];
					self.setMoveTarget(lastTarget[0], lastTarget[1], self.onArrivalCallback.func, self.onArrivalCallback.params);
				}
				return true;
			}
			if (yDir !== false)
				queue.push([x, y+5, false, true]);
			if (yDir !== true)
				queue.push([x, y-5, false, false]);
			if (xDir !== false)
				queue.push([x+5, y, true, false]);
			if (xDir !== true)
				queue.push([x-5, y, false, false]);
		}
		while (recoverWalkable() !== true) {}
	}

	if (!this.moveTarget || (this.moveTarget && this.moveTarget.length == 0)) {
		try {
			if (this.onArrivalCallback && typeof this.onArrivalCallback.func === 'function')
				this.onArrivalCallback.func.apply(null, this.onArrivalCallback.params);
		} catch (e) {
			console.error(e.stack);
		}
		this.onArrivalCallback = null;
		return;
	}

	if (Vec.equals(this.translation, this.moveTarget[0])) {
		this.moveTarget.shift();
	} else {
		var stuckFixer = 0;
		//do {
			var v = Vec.add(Vec.flipSign(this.translation), this.moveTarget[0]);
			var d = Vec.length(v);

			if (d > this.maxSpeed) {
				var n = Vec.mul(v, 1 / d); // normalized
				v = Vec.mul(n, this.maxSpeed + stuckFixer);
			}
		/*	stuckFixer += 0.5;

			if (stuckFixer >= 4.0) {
				this.setMoveTarget(this.translation[0], this.translation[1]);
				return;
			}*/

			var nextPosition = (d < g_smallValue) ? this.moveTarget[0] : Vec.add(this.translation, v);
		//} while (!this.pathFinder.canWalkOn(nextPosition[0], nextPosition[1]));

		/*if (stuckFixer >= 3.0)
			this.setMoveTarget(this.translation[0], this.translation[1]);
		else*/
			Vec.assign(this.translation, nextPosition);
	}

	Game.eventHandler.triggerEventOn('walkon', this.translation[0], this.translation[1]);

	this.updatePosition();
};
Char.prototype.updatePosition = function() {
	if (!this.image) return;
	var self = this;
	this.image.attr("transform", function() {
		return translate.apply(null, self.translation);
	});
};
/**
 *
 * @param x coord
 * @param y coord
 * @param onArrival optional callback function
 */
Char.prototype.setMoveTarget = function(x, y, onArrival, onArrivalParams) {

	this.onArrivalCallback = {
		func: onArrival,
		params: onArrivalParams
	};

	if (Game.config.usePathFinding)
		this.moveTarget = this.pathFinder.smoothPath(this.pathFinder.findPath(this.translation[0], this.translation[1], x, y));
	else
		this.moveTarget = [[x, y]];

	if (Game.config.verbosePathFinder) {
		var pathfinderlines = this.svg.select('#pathfinderlines');
		if (pathfinderlines.empty()) pathfinderlines = this.svg.append('g').attr('id', 'pathfinderlines');
		var path = 'M';
		for (var i = 0; i < this.moveTarget.length; ++i) {
			var m = this.moveTarget[i];
			path += ' '+m[0]+','+m[1];
		}
		pathfinderlines.append('path')
			.style('stroke', '#f0f')
			.style('stroke-width', 1)
			.style('fill', 'none')
			.attr('d', path)
			.transition()
			.delay(10000)
			.remove();
	}
};

Char.prototype.stopMovement = function() {
	this.moveTarget = null;
};
