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
function getInfo(svg, node) {
	var bbox = node[0][0].getBBox();
	var xy = Vec.add(getTranslation(node[0][0], svg[0][0]), [bbox.x, bbox.y]);
	return {
		x: xy[0],
		y: xy[1],
		xCenter: xy[0]+(bbox.width/2),
		yCenter: xy[1]+(bbox.height/2),
		height: bbox.height,
		width: bbox.width
	}

}

function euclidianDistance(a_x, a_y, b_x, b_y){
	return Math.sqrt((a_x-b_x)*(a_x-b_x) + (a_y-b_y)*(a_y-b_y));
}

function Path(svgPathData, offset) {
	this.edges = [];

	var currentPosition = [0,0];
	var lastPosition = null;
	if (!offset) offset = [0,0];
	var currentCommand = 'm';

	var splitData = svgPathData.split(" ");

	for (var i = 0; i < splitData.length; ++i) {
		var d = splitData[i];
		var s = d.split(',');
		var commandPart = s[0][0];
		var commandPartInt = commandPart.charCodeAt(0);
		var isNumber = (commandPartInt >= 48 && commandPartInt <= 57) || commandPart == '-' || commandPart == '+' || commandPart == '.';

		if (isNumber) {
			currentPosition = currentPosition.slice();
			lastPosition = currentPosition.slice();
			switch (currentCommand) {
				case 'm':
					currentPosition[0] = Number(s[0]) - offset[0];
					currentPosition[1] = Number(s[1]) - offset[1];
					currentCommand = 'l';
					break;
				case 'M':
					currentPosition[0] = Number(s[0]) - offset[0];
					currentPosition[1] = Number(s[1]) - offset[1];
					currentCommand = 'L';
					break;
				case 'l':
					currentPosition[0] += Number(s[0]);
					currentPosition[1] += Number(s[1]);
					this.edges.push([lastPosition.slice(), currentPosition.slice()]);
					break;
				case 'L':
					currentPosition[0] = Number(s[0]) - offset[0];
					currentPosition[1] = Number(s[1]) - offset[1];
					this.edges.push([lastPosition.slice(), currentPosition.slice()]);
					break;
			}
		} else {
			switch (commandPart) {
				case 'm':
				case 'M':
				case 'l':
				case 'L':
					currentCommand = commandPart;
					break;
				case 'z':
				case 'Z':
					this.edges.push([this.edges[this.edges.length-1][1].slice(), this.edges[0][0].slice()]);
					break;
				default:
					currentCommand = 'l'; // fallback
			}
		}
	}

	/*while (splitData.length > 0) {
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
	}*/
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

