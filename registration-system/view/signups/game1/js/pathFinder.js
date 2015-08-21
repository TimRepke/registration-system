function PathFinder(svg) {
	this.svg = svg;

	this.walkNodes = [];
	this.noWalkNodes = [];

	this.raster = null;

	this.scanWalkables();
	this.generateRaster();
}

PathFinder.prototype.scanWalkables = function() {
	var self = this;
	this.svg.selectAll('g').each(function(d, i) {
		var label = this.getAttribute('inkscape:label');
		if (!self.walkNode && label == "WALK")
			self.walkNode = this;
		if (!self.noWalkNode && label == "NOWALK")
			self.noWalkNode = this;
	});
	var walkTranslation = getTranslation(this.svg[0][0], this.walkNode);
	d3.select(self.walkNode).selectAll('path').each(function() {
		self.walkNodes.push(new Path(this.getAttribute("d"), walkTranslation));
	});
	var noWalkTranslation = getTranslation(this.svg[0][0], this.noWalkNode);
	d3.select(self.noWalkNode).selectAll('path').each(function() {
		self.noWalkNodes.push(new Path(this.getAttribute("d"), noWalkTranslation));
	});
};

PathFinder.prototype.generateRaster = function() {
	if (!Game.config.usePathFinding) return;

	this.raster = [];
	var subraster = null;

	var bbox = this.svg[0][0].getBBox();
	var ymax = Math.floor(bbox.height/Game.config.pathFindingGridSize);
	var xmax = Math.floor(bbox.width/Game.config.pathFindingGridSize);
	for (var y = 0; y < ymax; ++y) {
		subraster = this.raster[y] = [];
		for (var x = 0; x < xmax; ++x) {
			subraster[x] = {
				walkable: this.canWalkOn(x*Game.config.pathFindingGridSize, y*Game.config.pathFindingGridSize),
				score: -1,
				from: null
			};
		}
	}
};
PathFinder.prototype.clearPathScore = function() {
	var bbox = this.svg[0][0].getBBox();
	var ymax = Math.floor(bbox.height/Game.config.pathFindingGridSize);
	var xmax = Math.floor(bbox.width/Game.config.pathFindingGridSize);
	for (var y = 0; y < ymax; ++y) {
		var subraster = this.raster[y];
		for (var x = 0; x < xmax; ++x) {
			var e = subraster[x];
			e.score = -1;
			e.from = null;
		}
	}
};
PathFinder.prototype.findPath = function(fromX, fromY, toX, toY) {
	if (!this.canWalkOn(toX, toY))
		return [];
	this.clearPathScore();

	var self = this;

	// Path finding from a to z
	var aX = Math.floor(fromX/Game.config.pathFindingGridSize);
	var aY = Math.floor(fromY/Game.config.pathFindingGridSize);
	var zX = Math.floor(toX/Game.config.pathFindingGridSize);
	var zY = Math.floor(toY/Game.config.pathFindingGridSize);

	var toScan = new PriorityQueue({comparator: function(a, b) { return a.score - b.score; }});
	this.raster[aY][aX].score = 1;
	toScan.queue({x:aX, y:aY, score:1});

	function scan(laX, laY, lzX, lzY, lScore) {
		var r = self.raster[lzY][lzX];
		if (r.walkable && (r.score == -1 || lScore < r.score)) {
			r.from = [laX+0, laY+0];
			r.score = lScore+0;
			toScan.queue({x:lzX+0, y:lzY+0, score:lScore+0});
		}
	}

	var res;
	while (toScan.length > 0) {
		var d = toScan.dequeue();

		//alert(d.x +":"+ d.y);

		var current = this.raster[d.y][d.x];
		if (current.score < d.score)
			continue; // outdated information

		// Reached destination?
		if (d.x == zX && d.y == zY) {
			res = current;
			break; // yep
		}

		// Scan 4 directions, add penalty if wrong direction
		scan(d.x, d.y, d.x+1, d.y, d.score + (d.x+1 > zX ? 5 : 1));
		scan(d.x, d.y, d.x-1, d.y, d.score + (d.x-1 < zX ? 5 : 1));
		scan(d.x, d.y, d.x, d.y+1, d.score + (d.y+1 > zY ? 5 : 1));
		scan(d.x, d.y, d.x, d.y-1, d.score + (d.y-1 < zY ? 5 : 1));
	}

	if (!res)
		return [];

	var resultPath = [[toX, toY]];
	while (res.from != null) {
		resultPath.unshift([res.from[0]*Game.config.pathFindingGridSize, res.from[1]*Game.config.pathFindingGridSize]);
		res = this.raster[res.from[1]][res.from[0]];
	}
	resultPath.unshift([fromX, fromY]);

	return resultPath;
};
PathFinder.prototype.smoothPath = function(path) {
	if (!path || path.length == 0) return [];

	var lastPos = path[0];
	var resultPath = [];
	for (var i = 1; i < path.length; ++i) {
		if (this.isDirectlyReachable(lastPos[0], lastPos[1], path[i][0], path[i][1]))
			continue;
		resultPath.push(lastPos);
		resultPath.push(path[i-1]);
		lastPos = path[i];
	}
	resultPath.push(lastPos);
	resultPath.push(path[path.length-1]);

	return resultPath;
};
PathFinder.prototype.isDirectlyReachable = function(fromX, fromY, x, y) {
	var canWalk = true;
	for (var i = 0; i < this.walkNodes.length; ++i) {
		if (!this.walkNodes[i].isDirectlyReachable(fromX, fromY, x, y)) {
			canWalk = false;
			break;
		}
	}
	if (!canWalk) return false;

	for (var i = 0; i < this.noWalkNodes.length; ++i) {
		if (!this.noWalkNodes[i].isDirectlyReachable(fromX, fromY, x, y)) {
			canWalk = false;
			break;
		}
	}
	return canWalk;
};
PathFinder.prototype.canWalkOn = function(x, y) {
	var canWalk = false;
	for (var i = 0; i < this.walkNodes.length; ++i) {
		if (this.walkNodes[i].isInside(x, y)) {
			canWalk = true;
			break;
		}
	}
	if (!canWalk) return false;

	for (var i = 0; i < this.noWalkNodes.length; ++i) {
		if (this.noWalkNodes[i].isInside(x, y)) {
			canWalk = false;
			break;
		}
	}
	return canWalk;
};