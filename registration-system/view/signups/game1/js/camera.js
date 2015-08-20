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
	//x *= Math.PI; // stretch to [0, pi] for cos function

	//var camspeed = (-Math.cos(x)+1)/2 // map result from [0,1]
	var camspeed = Math.pow(x,4); // map result from [0,1]
	camspeed *= this.idealDistanceToTarget; // map to [0, wantedspeed]
	camspeed = Math.max(camspeed, x*this.linearMinimum, this.constantMinimum); // map to [minspeed, wantedspeed]
 
	return Vec.mul(n, camspeed); 
}
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
/*	var self = this;
	this.svg.attr("transform", function(d,i) {
		var translation = Vec.add(Vec.flipSign(self.translation), Vec.mul(Game.config.size, 0.5)); // move subject to center
		return translate.apply(null, translation);
	});
*/
	var root = document.getElementById("gameRoot");
	var translation = Vec.add(Vec.flipSign(this.translation), Vec.mul(Game.config.size, 0.5)); // move subject to center
	root.style.left = translation[0]+'px';
	root.style.top = translation[1]+'px';
}

