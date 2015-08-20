function Char(svg, options) {
	if (!options) options = {};
	this.svg = svg;
	this.pathFinder = new PathFinder(svg);

	this.translation = options.spawn ? options.spawn : this.findSpawn();
	this.moveTarget = [];
	this.maxSpeed = 2;

	var self = this;
	d3.xml('chars/bernd.svg', 'image/svg+xml', function(xml) {
		self.image = self.svg.append('g').attr('id', 'player');
		var layers = d3.select(xml.documentElement).selectAll('g').filter(function() {
			return this.getAttribute('inkscape:groupmode') == 'layer';
		}).each(function() {
			self.image[0][0].appendChild(this);
		});

		self.updatePosition();
	});
}
Char.prototype.findSpawn = function() {
	// [1320, svgFlipY(svg[0][0], 500)]
	var spawn = this.svg.select("#player_spawn");
	var bbox = spawn[0][0].getBBox();
	return Vec.add(getTranslation(this.svg[0][0], spawn[0][0]), [bbox.x, bbox.y]);
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
	if (!this.image) return;
	var self = this;
	this.image.attr("transform", function() {
		return translate.apply(null, self.translation);
	});
}
Char.prototype.setMoveTarget = function(newX, newY) {
	var matrix = this.svg[0][0].getScreenCTM();
	var x = newX-matrix.e;
	var y = newY-matrix.f;

	if (Game.config.usePathFinding)
		this.moveTarget = this.pathFinder.smoothPath(this.pathFinder.findPath(this.translation[0], this.translation[1], x, y));
	else
		this.moveTarget = [[x, y]];
}

