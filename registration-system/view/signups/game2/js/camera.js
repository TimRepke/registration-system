function Camera(svg, target) {
	this.translation = target.slice();
	this.target = target;
	this.svg = svg;

	this.idealDistanceToTarget = 300;
	this.speedWhenOverIdealDistance = 2;
	this.linearMinimum = 0.5;
	this.constantMinimum = 0.65;

	this.updatePosition();
}
Camera.prototype.moveTowards = function(v) {
	var dir = Vec.add(Vec.flipSign(this.translation), v);
	var d = Vec.length(dir);
	if (d > this.idealDistanceToTarget)
		return Vec.mul(dir, this.speedWhenOverIdealDistance / d);

	var n = Vec.mul(dir, 1/d);

	var x = d / this.idealDistanceToTarget; // map distance to range [0,1]

	var camspeed = Math.pow(x,4); // map result from [0,1]
	camspeed *= this.idealDistanceToTarget; // map to [0, wantedspeed]
	camspeed = Math.max(camspeed, x*this.linearMinimum, this.constantMinimum); // map to [minspeed, wantedspeed]
 
	return Vec.mul(n, camspeed); 
};
Camera.prototype.movement = function() {
	if (!this.target) return;

	if (Vec.equals(this.translation, this.target)) return;
	var d = Vec.distance(this.translation, this.target);

	if (d < g_smallValue || d < this.minimalSpeed) {
		Vec.assign(this.translation, this.target);
	} else {
		var subTarget = this.moveTowards(this.target);
		var subD = Vec.length(subTarget);
		if (subD >= d)
			Vec.assign(this.translation, this.target);
		else
			Vec.assign(this.translation, Vec.add(this.translation, subTarget));
	}
	
	this.updatePosition();
};
Camera.prototype.updatePosition = function() {
	var root = document.getElementById("gameRoot");
	var svg = root.firstChild;
	var translation = Vec.add(Vec.flipSign(this.translation), Vec.mul(Game.config.size, 0.5)); // move subject to center
	if(translation[0] > 0) translation[0] = 0;
	if(translation[1] > 0) translation[1] = 0;

	var width = $(svg).width();
	var height = $(svg).height();
	if(translation[0] < (Game.config.size[0] - width))  translation[0] = Game.config.size[0] - width;
	if(translation[1] < (Game.config.size[1] - height)) translation[1] = Game.config.size[1] - height;
	root.style.left = translation[0]+'px';
	root.style.top = translation[1]+'px';
};

