function EventHandler() {

}

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