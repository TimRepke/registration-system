function translate(x, y) {
	return "translate("+x+", "+y+")";
}
function showCoords(x, y) {
	document.getElementById("coords").innerHTML = '('+x+', '+y+')';
}
function svgFlipY(svg, y) {
	return svg.getBBox().height - y;
}
function getTranslation(svg, node) {
	var matrix = svg.getTransformToElement(node);
	return [matrix.e, matrix.f];
}


function Path(svgPathData, offset) {
	this.edges = [];

	var currentPosition = [0,0];
	if (!offset) offset = [0,0];
	var relativeCommand = true;
	var currentCommand = 'm';

	var splitData = svgPathData.split(" ");
	while (splitData.length > 0) {
		var lastPosition = currentPosition.slice();

		var part = splitData.shift();
		if (part.length == 0) continue;

		var commandPart = part[0].toLowerCase();
		relativeCommand = commandPart == part[0];
		var rest = part.substr(1);
		var c = commandPart.charCodeAt(0);

		var isNumber = (c >= 48 && c <= 57) || commandPart == '-' || commandPart == '+' || commandPart == '.';
		if (isNumber) {
			currentPosition = part.split(",");
			for (var i = 0; i < currentPosition.length; ++i)
				currentPosition[i] = offset[i]+(relativeCommand ? lastPosition[i] : 0) + Number(currentPosition[i]);

			if (currentCommand == 'l')
				this.edges.push([lastPosition, currentPosition]);
			if (currentCommand == 'm')
				currentCommand = 'l';
		} else if (!isNumber) {
			switch (commandPart) {
				case 'm':
				case 'l':
				case 'z':
					currentCommand = commandPart;
					break;
				default:
					currentCommand = 'l'; // fallback
			}
			if (currentCommand == 'z') // close loop
				this.edges.push([this.edges[this.edges.length-1][1], this.edges[0][0]]);
			if (rest.length > 0)
				splitData.unshift(rest);
		}
	}
}
Path.prototype.lineIntersectionCount = function(fromX, fromY, x, y) {
	var hitCount = 0;
	for (var i = 0; i < this.edges.length; ++i) {
		var e = this.edges[i];
		var result = checkLineIntersection(e[0][0], e[0][1], e[1][0], e[1][1], x, y, fromX, fromY);
		if (result.onLine1 && result.onLine2)
			hitCount += 1;
	}
	return hitCount;
}
Path.prototype.isDirectlyReachable = function(fromX, fromY, x, y) {
	return this.lineIntersectionCount(fromX, fromY, x, y) == 0;
}
Path.prototype.isInside = function(x, y) {
	return (this.lineIntersectionCount(-5, -5, x, y) % 2) == 1;
}

