function Achievements() {
    this.achievements = {
        'started_game': 'Bestes Anmeldesystem gestartet',
        'first_step': 'Erster Schritt getan',
        'some_water': {
            message: 'An frischem Brunnenwasser gerochen',
            condition: function(context) {
                return euclidianDistance(Game.char.translation[0], Game.char.translation[1], context.x, context.y) < 150;
            }
        },
        'stroh': 'Warum liegt hier Stroh rum?',
        'saw_devs1': 'Wilde Informatiker auf Wiese gesehen',
        'moneyboy': 'Money Boy: Swag ist aufgedreht',
        // TODO: add more!
        'achievement42': 'You just found the answer to everything!'
    };
    this.achievedAchievements = [];

    this.domElems = null;

    this.triggerAchievement('started_game');
}
Achievements.prototype.numTotalAchievements = function() {
    return Object.keys(this.achievements).length;
};

Achievements.prototype.numCompletedAchievements = function() {
    return Object.keys(this.achievedAchievements).length;
};

Achievements.prototype.initDomElems = function () {
    this.domElems = {
        'log':        document.getElementById('achievement-log'),
        'statusBar':  document.getElementById('achievement-progress').getElementsByClassName('status-bar-bar')[0],
        'statusText': document.getElementById('achievement-progress').getElementsByClassName('status-bar-text')[0]
    };
};

Achievements.prototype.getDomElem = function (elem) {
    if(!this.domElems) this.initDomElems();
    return this.domElems[elem];
};

Achievements.prototype.updateStatusBar = function () {
    var percent = Math.ceil((this.numCompletedAchievements() / this.numTotalAchievements())*100);
    this.getDomElem('statusBar').style.width = percent + '%';
};

Achievements.prototype.updateStatusText = function () {
    var text = this.numCompletedAchievements() + '/' + this.numTotalAchievements();
    this.getDomElem('statusText').innerText = text;
};

Achievements.prototype.logMessage = function (message) {
    var list = this.getDomElem('log');

    var newElem = document.createElement('li');
    var newElemText = document.createTextNode(message);
    newElem.appendChild(newElemText);

    list.insertBefore(newElem, list.childNodes[0]);
};

Achievements.prototype.getMessage = function (achievementId) {
    if (typeof this.achievements[achievementId] === 'object') {
        return this.achievements[achievementId].message;
    }
    return this.achievements[achievementId];
};

Achievements.prototype.isTriggerable = function (achievementId, context) {
    var achievement = this.achievements[achievementId];
    if (typeof achievement === 'object' && 'condition' in achievement){
        return achievement.condition(context);
    }
    return true;
};

Achievements.prototype.triggerAchievement = function (achievementId, context) {
    if (!this.achievements[achievementId]){
        console.error("No such achievement: " + achievementId);
    }
    else if (this.achievedAchievements.indexOf(achievementId) < 0 && this.isTriggerable(achievementId, context)) {
        this.achievedAchievements.push(achievementId);
        this.updateStatusBar();
        this.updateStatusText();
        this.logMessage(this.getMessage(achievementId));
    }
    // else console.warn("Achievement already achieved: " + achievementId);

    if (this.numCompletedAchievements() === 42 ) this.triggerAchievement('achievement42')
};