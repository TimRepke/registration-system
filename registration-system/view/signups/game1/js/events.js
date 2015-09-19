function EventHandler(svg) {
    this.eventNodes = {
        hover: [],
        click: [],
        walkon: []
    };
    this.svg = svg;
    var self = this;
    svg.selectAll('g').each(function (d, i) {
        var label = this.getAttribute('inkscape:label');
        if (!self.rawNodes && label == "EVENT")
            self.rawNodes = this;
    });
    var eventTranslation = getTranslation(svg[0][0], this.rawNodes);
    d3.select(self.rawNodes).selectAll('path').each(function () {
        var trigger = this.getAttribute('trigger');
        if (trigger && self.eventNodes[trigger]) {
            self.eventNodes[trigger].push({
                path: new Path(this.getAttribute("d"), eventTranslation),
                id: this.getAttribute('id'),
                type: this.getAttribute('type'),
                trigger: trigger,
                target: this.getAttribute('target'),
                destination: this.getAttribute('destination'),
                stopsWalk: this.getAttribute('stopsWalk') === 'true',
                action: this.getAttribute('action'),
                walkTo: this.getAttribute('walkTo'),
                condition: this.getAttribute('condition')
            });
        }
    });
}

EventHandler.prototype.hasEventOn = function (trigger, x, y) {
    return this.getEventOn(trigger, x, y, function () {
    });
};

EventHandler.prototype.getEventOn = function (trigger, x, y, callback) {
    if (!this.walkOnEvents) this.walkOnEvents = {}; // currently active events

    var hasEvent = false;
    for (var i = 0; i < this.eventNodes[trigger].length; ++i) {
        var node = this.eventNodes[trigger][i];
        if (node.path.isInside(x, y)) {
            hasEvent = true;
            if (trigger == 'walkon' && !this.walkOnEvents[node.id]) {
                this.walkOnEvents[node.id] = true;
                callback(node, true);
            } else {
                callback(node);
            }
        } else {
            if (trigger == 'walkon' && this.walkOnEvents[node.id]) {
                delete this.walkOnEvents[node.id];
                callback(node, false);
            }
        }
    }
    return hasEvent;
};

EventHandler.prototype.triggerEventOn = function (trigger, x, y) {
    var self = this;
    return this.getEventOn(trigger, x, y, function (event, bEnter) {
        var isActive = true;
        if (event.condition) {
            var conditions = event.condition.split(',');
            for (var i = 0; i < conditions.length; i++) {
                isActive = isActive && Environment.progress[conditions[i]];
            }
        }
        if (isActive) self.handleEvent(event, {trigger: trigger, x: x, y: y, bEnter: bEnter});
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
            Game.achievements.triggerAchievement(event.id, context);
            break;
        case 'mapchange':
            Game.instance.nextMap(event.destination, event.target);
            break;
        case 'special':
            EventHandler.handleAction(event);
            break;
    }

    if (event.stopsWalk) {
        Game.char.stopMovement();
    }
};

EventHandler.handleAction = function (event) {
    if (event.action && event.action in EventHandler.actions) {
        if (event.walkTo) {
            var spawn = Game.char.svg.select('#' + event.walkTo);
            if (spawn[0][0]) {
                var bbox = spawn[0][0].getBBox();
                var xy = Vec.add(getTranslation(spawn[0][0], Game.char.svg[0][0]), [bbox.x, bbox.y]);
                Game.char.setMoveTarget(xy[0], xy[1], EventHandler.actions[event.action]);
            }
        } else {
            EventHandler.actions[event.action]();
        }
    }
};

EventHandler.actions = {
    'fs_open_board': function () {
        console.log('fuck yeah!');
        // TODO implement board fill
    },
    'fs_screamingGeorge': function () {
        // TODO implement dialogue
        console.warn('George is screaming');
        Environment.progress.fs_georgeScreamed = true;
    },
    'fs_firstApproach': function () {
        console.log('some talking going on');
        Game.actionsBlocked = true;
        dialogueHelper([
            {
                bubble: '#tim_speech',
                message: 'Willkommen in der Fachschaft!'
            }, {
                bubble: '#manu_speech',
                message: 'Yoh, geh\' mal rüber zu George.'
            }
        ], function () {
            Environment.progress.fs_firstApproach = true;
            console.log('some talking went on');
            Game.actionsBlocked = false;
        });
    }
};

function dialogueHelper(dialogue, done) {
    var dialogueBox = $('#gameDialogue');
    dialogueBox.html('');
    dialogueBox.show();

    var dialogue_i = 0;
    dialogueStepper();

    function dialogueStepper() {
        if (dialogue_i >= dialogue.length) { done(); return; }
        var part = dialogue[dialogue_i];
        dialogue_i++;
        plotMessage(part.bubble, part.message, dialogueStepper);
    }

    function plotMessage(bubble, message, plotDone) {
        var i = 0;
        var messageStepper = setInterval(function () {
            i++;
            if (i > message.length) {
                clearInterval(messageStepper);
                Game.char.svg.select(bubble).style('display', 'block');
                setTimeout(function() {
                    Game.char.svg.select(bubble).style('display', 'none');
                    plotDone();
                }, 1000)
            } else {
                Game.char.svg.select(bubble).style('display', (i % 2) ? 'none' : 'block');
                dialogueBox.text(message.substring(0, i));
            }
        }, 40);
    }
}