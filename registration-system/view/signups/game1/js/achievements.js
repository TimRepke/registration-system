function Achievements() {
    this.achievements = {
        'started_game': {
            message: "Bestes Anmeldesystem gestartet"
        },
        'first_step': {
            message: "Erster Schritt getan"
        }
    };
    this.achievedAchievements = [];

    this.logElem        = null;
    this.statusBarElem  = null;
    this.statusTextElem = null;

    this.triggerAchievement('started_game');
}

Achievements.prototype.getLogElem = function () {
    if(!this.logElem)
        this.logElem = document.getElementById('achievement-log');
    return this.logElem;
};

Achievements.prototype.getStatusBarElem = function () {
    if(!this.statusBarElem)
        this.statusBarElem = document.getElementById('achievement-progress').getElementsByClassName('status-bar-bar')[0];
    return this.statusBarElem;
};

Achievements.prototype.getStatusTextElem = function () {
    if(!this.statusTextElem)
        this.statusTextElem = document.getElementById('achievement-progress').getElementsByClassName('status-bar-text')[0];
    return this.statusTextElem;
};

Achievements.prototype.updateStatusBar = function () {
    var percent = Math.ceil((this.numCompletedAchievements() / this.numTotalAchievements())*100);
    this.getStatusBarElem().style.width = percent + '%';
};

Achievements.prototype.updateStatusText = function () {
    var text = this.numCompletedAchievements() + '/' + this.numTotalAchievements();
    this.getStatusTextElem().innerText = text;
};

Achievements.prototype.numTotalAchievements = function() {
    return Object.keys(this.achievements).length;
};

Achievements.prototype.numCompletedAchievements = function() {
    return Object.keys(this.achievedAchievements).length;
};

/**
 * returns status about a specific achievementId
 * @param achievementId
 * @returns {number} -1 = does not exist, 0 = achievable, 1 = already completed
 */
Achievements.prototype.achievementStatus = function(achievementId) {
    if (!this.achievements[achievementId])
        return -1;
    if (!this.achievedAchievements.indexOf(achievementId) >= 0)
        return 0;
    return 1;
};

Achievements.prototype.logMessage = function (message) {
    var list = this.getLogElem();

    var newElem = document.createElement('li');
    var newElemText = document.createTextNode(message);
    newElem.appendChild(newElemText);

    list.insertBefore(newElem, list.childNodes[0]);
};

Achievements.prototype.getAchievementMessage = function (achievementId) {
    return this.achievements[achievementId].message;
};

Achievements.prototype.triggerAchievement = function (achievementId) {
    var status = this.achievementStatus(achievementId);
    if (status === 0) {
        this.achievedAchievements.push(achievementId);
        this.updateStatusBar();
        this.updateStatusText();
        this.logMessage(this.getAchievementMessage(achievementId));
    }
    else if (status === -1)
        console.error("No such achievement: " + achievementId);
    else
        console.warn("Achievement already achieved: " + achievementId);
};