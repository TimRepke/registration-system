var Vec = {
	equals: function(v1, v2) {
		for (var i = 0; i < v1.length; ++i)
			if (v1[i] != v2[i]) return false;
		return true;
	},
	distance: function(v1, v2) {
		return this.length(this.add(this.flipSign(v1), v2));
	},
	add: function(v1, v2) {
		var r = new Array(v1.length);
		for (var i = 0; i < v1.length; ++i)
			r[i] = v1[i] + v2[i];
		return r;
	},
	mul: function(v, s) {
		var r = new Array(v.length);
		for (var i = 0; i < v.length; ++i)
			r[i] = v[i] * s;
		return r;
	},
	flipSign: function(v) {
		var r = new Array(v.length);
		for (var i = 0; i < v.length; ++i)
			r[i] = -v[i];
		return r;
	},
	assign: function(v1, v2) {
		for (var i = 0; i < v2.length; ++i)
			v1[i] = v2[i];
	},
	length: function(v) {
		var sum = 0;
		for (var i = 0; i < v.length; ++i)
			sum += Math.pow(v[i], 2);
		return Math.sqrt(sum);
	}
};

