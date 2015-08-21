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
                stopsWalk: this.getAttribute('stopsWalk') === 'true'
            });
        }
    });
}

EventHandler.prototype.hasEventOn = function (trigger, x, y) {
    return this.getEventOn(trigger, x, y) !== undefined;
};

EventHandler.prototype.getEventOn = function(trigger, x, y) {
    for (var i = 0; i < this.eventNodes[trigger].length; ++i) {
        if (this.eventNodes[trigger][i].path.isInside(x, y)) {
            return this.eventNodes[trigger][i];
        }
    }
    return undefined;
};

EventHandler.prototype.triggerEventOn = function (trigger, x, y) {
    var event = this.getEventOn(trigger, x, y);
    if (event) this.handleEvent(event);
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
EventHandler.prototype.handleEvent = function (event) {
    switch (event.type) {
        case 'achievement':
            Game.achievements.triggerAchievement(event.id);
            break;
    }

    if (event.stopsWalk) {
        Game.char.stopMovement();
    }
};