function EventHandler(svg) {
    this.eventNodes = {
        hover:  [],
        click:  [],
        walkon: []
    };

    var self = this;
    svg.selectAll('g').each(function(d, i) {
        var label = this.getAttribute('inkscape:label');
        if (!self.rawNodes && label == "EVENT")
            self.rawNodes = this;
    });
    var eventTranslation = getTranslation(svg[0][0], this.rawNodes);
    d3.select(self.rawNodes).selectAll('path').each(function() {
        var trigger = this.getAttribute('trigger');
        if (trigger && self.eventNodes[trigger]) {
            self.eventNodes[trigger].push({
                path: new Path(this.getAttribute("d"), eventTranslation),
                id: this.getAttribute('id'),
                type: this.getAttribute('type'),
                trigger: trigger,
                target: this.getAttribute('target'),
                destination: this.getAttribute('destination'),
                stopsWalk: this.getAttribute('stopsWalk') === 'true'
            });
        }
    });
}

EventHandler.prototype.hasEventOn = function (trigger, x, y) {
    return this.getEventOn(trigger, x, y) !== undefined;
};

EventHandler.prototype.getEventOn = function(trigger, x, y, callback) {
    if (!this.walkOnEvents) this.walkOnEvents = {}; // currently active events

    for (var i = 0; i < this.eventNodes[trigger].length; ++i) {
        var node = this.eventNodes[trigger][i];
        if (node.path.isInside(x, y)) {
            if (!this.walkOnEvents[node.id]) {
                this.walkOnEvents[node.id] = true;
                callback(node, true);
            }
        } else {
            delete this.walkOnEvents[node.id];
        }
    }
    return undefined;
};

EventHandler.prototype.triggerEventOn = function (trigger, x, y) {
    var self = this;
    this.getEventOn(trigger, x, y, function(event, bEnter) {
        self.handleEvent(event, {trigger: trigger, x: x, y: y, bEnter: bEnter});
    });
};

/**
 * Receives an event object and handles the necessary actions
 *
 * Object has:
 *   id: svg elem id,
 *   type: (achievement,...)
 *   trigger: (walkon, hover, click)
 *   stopsWalk: (true, false)
 *
 * @param event
 */
EventHandler.prototype.handleEvent = function (event, context) {
    switch (event.type) {
        case 'achievement':
            if (context.bEnter)
                Game.achievements.triggerAchievement(event.id, context);
            break;
        case 'mapchange':
            Game.instance.nextMap(event.destination, event.target);
            break;
    }

    if (event.stopsWalk) {
        Game.char.stopMovement();
    }
};