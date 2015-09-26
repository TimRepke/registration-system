function Achievements() {
    var self = this;
    this.achievements = {
        // LANDING
        'first_step': 'Erster Schritt getan',
        'some_water': {
            message: 'An frischem Brunnenwasser gerochen',
            condition: function (context) {
                return euclidianDistance(Game.char.translation[0], Game.char.translation[1], context.x, context.y) < 150;
            }
        },
        'saw_devs1': 'Wilde Informatiker auf Wiese gesehen',
        'spotted_gorilla': {
            messgae: 'Alten Bekannten im Wald gesehen',
            action: function() {
                Story.dialogueHelper([{
                    message: 'Gorilla sagt: HU HU HU!'
                }])
            }
        },
        'hydrant': {
            message: 'Wasser aufgedreht',
            condition: function (context) {
                return euclidianDistance(Game.char.translation[0], Game.char.translation[1], context.x, context.y) < 150;
            }
        },
        'randomwalk': 'Sinnlose Wegwahl',

        // CASTLE ENTRANCE
        'moneyboy': 'Money Boy: Swag ist aufgedreht',
        'batteries': 'Batterien in den Computer eingelegt',
        'bierball': 'BIIIEEEERBAAAAALLLLL!!',

        // FACHSCHAFT
        'wrong_board': {
            message: 'Falsche Tafel!',
            condition: function () {
                return Environment.progress.fs_georgeScreamed;
            }
        },
        'hugo_water': 'Toten Hugo gegossen',
        'laptop2': 'Laptop zugeklappt',
        'laptop1': {
            message: 'Laptop ausgemacht',
            condition: function() {
                return self.achievedAchievements.indexOf('laptop2') >= 0
            }
        },
        'marathon': 'Runtimeerror! dev TIM low on energy',
        'ffa': {
            message: 'FFA Essen gegessen. Mit Tisch.',
            action: function () {
                Game.char.svg.select('#ffa_food').style('display', 'none');
            }
        },
        'stolper': 'Über Teppichkante gestolpert',
        'fs_chair': {
            message: 'Stuhl aus Fachschaft geklaut',
            action: function () {
                Game.char.svg.select('#stuhl').style('display', 'none');
            },
            condition: function (context) {
                return euclidianDistance(Game.char.translation[0], Game.char.translation[1], context.x, context.y) < 80;
            }
        },

        // DORF
        'speedrun': 'Haalt stop! Denkt doch mal an die Kinder!!1!',
        'woman': 'Mit einer Prinzessin gesprochen',
        'plumber': 'Berufung: Gas, Wasser, Scheiße',
        'princess': 'Prinzessin verärgert',
        'stroh': 'Warum liegt hier Stroh rum?',
        'blumen': 'Blumen zertrampelt',
        'maske': {
            message: 'Warum hast du eine Maske auf?',
            condition: function () {
                return self.achievedAchievements.indexOf('stroh') >= 0
            }
        },
        'gentzen': 'Bei diese Baustelle machen die sich den gentzen Aufwand umsonst.',
        'kacke': 'Eine gefährliche Stuhl-Gang',

        // SHOP
        'antler': {
            message: 'Geweih verschönert',
            action: function () {
                Game.char.svg.select('#antler_ball').style('display', 'block');
            }
        },
        'flowers': {
            message: 'Blumen umgestoßen',
            action: function () {
                Game.char.svg.select('#flowerpot').style('transform-origin', '50% 50%').style('transform', 'rotate(70deg)');
            },
            condition: function (context) {
                return euclidianDistance(Game.char.translation[0], Game.char.translation[1], context.x, context.y) < 100;
            }
        },
        'wine': {
            message: 'Weinfass getrunken',
            action: function () {
                Game.char.svg.select('#wine_glass').style('display', 'none');
            },
            condition: function (context) {
                return euclidianDistance(Game.char.translation[0], Game.char.translation[1], context.x, context.y) < 80;
            }
        },
        'chair': {
            message: 'Stuhl geklaut',
            action: function () {
                Game.char.svg.select('#stuhl').style('display', 'none');
            },
            condition: function (context) {
                return euclidianDistance(Game.char.translation[0], Game.char.translation[1], context.x, context.y) < 80;
            }
        },

        // META
        'started_game': 'Bestes Anmeldesystem gestartet',
        'gameDone': 'Zeit verschwendet',
        'achievement42': 'You just found the answer to everything!'
    };
    this.achievedAchievements = [];

    this.domElems = null;

    this.triggerAchievement('started_game');
    Environment.sound.achievements = true;
}
Achievements.prototype.numTotalAchievements = function () {
    return Object.keys(this.achievements).length;
};

Achievements.prototype.numCompletedAchievements = function () {
    return Object.keys(this.achievedAchievements).length;
};

Achievements.prototype.initDomElems = function () {
    this.domElems = {
        'log': document.getElementById('achievement-log'),
        'statusBar': document.getElementById('achievement-progress').getElementsByClassName('status-bar-bar')[0],
        'statusText': document.getElementById('achievement-progress').getElementsByClassName('status-bar-text')[0]
    };
};

Achievements.prototype.getDomElem = function (elem) {
    if (!this.domElems) this.initDomElems();
    return this.domElems[elem];
};

Achievements.prototype.updateStatusBar = function () {
    var percent = Math.ceil((this.numCompletedAchievements() / this.numTotalAchievements()) * 100);
    this.getDomElem('statusBar').style.width = percent + '%';
};

Achievements.prototype.updateStatusText = function () {
    var text = this.numCompletedAchievements() + '/' + this.numTotalAchievements();
    this.getDomElem('statusText').innerText = text;
};

Achievements.prototype.logMessage = function (message) {
    if (Environment.sound.achievements) new Audio(FAPI.resolvePath('sounds/ding.ogg')).play();
    var list = this.getDomElem('log');

    var newElem = document.createElement('li');
    var newElemText = document.createTextNode(message);
    newElem.appendChild(newElemText);

    newElem.style.backgroundColor = '#474c46';
    setTimeout(function () {
        newElem.style.background = 'transparent';
    }, 1000);

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
    if (typeof achievement === 'object' && 'condition' in achievement) {
        return achievement.condition(context);
    }
    return true;
};

Achievements.prototype.triggerAchievement = function (achievementId, context) {
    if (!this.achievements[achievementId]) {
        console.error("No such achievement: " + achievementId);
    }
    else if (this.achievedAchievements.indexOf(achievementId) < 0 && this.isTriggerable(achievementId, context)) {
        this.achievedAchievements.push(achievementId);
        this.updateStatusBar();
        this.updateStatusText();
        this.logMessage(this.getMessage(achievementId));
        if (typeof this.achievements[achievementId] === 'object' && 'action' in this.achievements[achievementId]) {
            this.achievements[achievementId].action();
        }
        return true;
    }
    // else console.warn("Achievement already achieved: " + achievementId);

    if (this.numCompletedAchievements() === 42) this.triggerAchievement('achievement42');
    return false;
};
