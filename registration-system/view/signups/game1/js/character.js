function Char(svg, options) {
	if (!options) options = {};
	this.svg = svg;
	this.pathFinder = new PathFinder(svg);

	this.translation = options.spawn ? options.spawn : this.findSpawn();
	this.moveTarget = [];
	this.maxSpeed = 2;

	this.rect = this.svg.append("rect");
	this.rect.attr("backgroundColor", "#f0f")
		.attr("width", "20")
		.attr("height", "20");
	
	this.updatePosition();
}
Char.prototype.findSpawn = function() {
	// [1320, svgFlipY(svg[0][0], 500)]
	var spawn = this.svg.select("#player_spawn");
	var b = spawn[0][0].getBBox();
	return [b.x, b.y];
}
Char.prototype.animate = function() {
}
Char.prototype.physics = function() {
	if (this.moveTarget && this.moveTarget.length == 0) return;
	if (Vec.equals(this.translation, this.moveTarget[0])) {
		this.moveTarget.shift();
		return;
	}

	var v = Vec.add(Vec.flipSign(this.translation), this.moveTarget[0]);
	var d = Vec.length(v);

	if (d > this.maxSpeed) {
		var n = Vec.mul(v, 1/d); // normalized
		v = Vec.mul(n, this.maxSpeed);
	}

	var nextPosition = (d < g_smallValue) ? this.moveTarget[0] : Vec.add(this.translation, v);

	if (this.pathFinder.canWalkOn(nextPosition[0], nextPosition[1]))
		Vec.assign(this.translation, nextPosition);
	else
		this.moveTarget.shift();

	this.updatePosition();
}
Char.prototype.updatePosition = function() {
	var self = this;
	this.rect.attr("transform", function() {
		return translate.apply(null, self.translation);
	});
}
Char.prototype.setMoveTarget = function(newX, newY) {
	if (!this.rect) return;

	var matrix = this.svg[0][0].getScreenCTM();
	var x = newX-matrix.e;
	var y = newY-matrix.f;

	if (Game.config.usePathFinding)
		this.moveTarget = this.pathFinder.smoothPath(this.pathFinder.findPath(this.translation[0], this.translation[1], x, y));
	else
		this.moveTarget = [[x, y]];
}

