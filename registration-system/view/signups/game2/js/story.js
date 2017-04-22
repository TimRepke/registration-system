function Story() {

}

Story.actions = {
    // TODO: Write story actions here ...
};

Story.credits = function () {
    Game.actionsBlocked = true;
    Game.achievements.triggerAchievement('gameDone');
    $('#game-overlay').html('' +
        '<div id="stand_by" style="margin: 40px auto; text-align: center;font-family: \'Courier New\', Courier, monospace;">' +
        '<span style="font-size: 30pt;font-weight: bold;">Bitte warten!</span><br /><span>Submit auf nächster Seite</span></div><div class="starWars"><div>' +
        '   <p>Anmeldung wird übertragen.</p>' +
        '   <p></p>' +
        '   <p>Kudos to:<br />Manuel Herrmann<br />Tim Repke</p>' +
        '   <p>Made in 2015.</p>' +
        '   <p>Lasst die Fahrten beginnen!' +
        '   <p>Viel Spaß!</p>' +
        '</div></div>' +
        '<button style="position: absolute; bottom: 0; right: 0;" id="skipButton">skip</button>').fadeIn(3000, function () {
        setTimeout(function () {
            allDone();
        }, 8000);
    });
    var cnt = 0;
    var standbyLoop = setInterval(function () {
        cnt++;
        $('#stand_by').css('display', (cnt % 2) ? 'none' : 'block');
    }, 300);
    $('#skipButton').on('click', allDone);

    function allDone() {
        clearInterval(standbyLoop);
        Environment.fapi.data.setSignupStats('game1', {'achievedAchievements': Game.achievements.achievedAchievements});
        Environment.fapi.submitSignup();
    }
};

Story.dialogueHelper = function (dialogue, context, done) {

    var speed = {
        talk: UrlComponents.isSet('fastTalk') ? 1 : 50,
        pause: UrlComponents.isSet('fastTalk') ? 50 : 2000
    };

    Game.actionsBlocked = true;
    var dialogueBox = $('#gameDialogue');
    dialogueBox.html('');
    dialogueBox.show();

    var dialogue_i = 0;
    dialogueStepper();

    function dialogueStepper() {
        if (dialogue_i >= dialogue.length) {
            dialogueBox.hide();
            Game.actionsBlocked = false;
            if (typeof done === 'function') done();
        } else {
            var part = dialogue[dialogue_i];
            dialogue_i++;
            if ('condition' in part && ((typeof part.condition === 'function') ? !part.condition() : !part.condition)) {
                dialogueStepper();
            } else if (part.input) {
                manualInput(part.input);
            } else if (part.answer) {
                answerSelection(part.answer);
            } else {
                plotMessage(part, dialogueStepper);
            }
        }
    }

    function manualInput(input) {
        var inputHTML = '<div>' + input.message + '</div>' +
            '<input type="text" id="gameDialogueInput" />' +
            '<button id="gameDialogueInputDone" style="width: 80px; border: 1px dotted #4e8260;">Fertig</button>';
        dialogueBox.html(inputHTML);
        $('#gameDialogueInputDone').prop('disabled', true).click(function () {
            input.action($('#gameDialogueInput').val());
            dialogueStepper();
        });
        $('#gameDialogueInput').bind('input propertychange', function () {
            if (input.check($(this).val())) {
                $('#gameDialogueInputDone').prop('disabled', false);
            }
        });
    }

    function answerSelection(answers) {
        var possibleAnswers = answers.map(function (answer, i) {
            if (!('condition' in answer) || (typeof answer.condition === 'function' ? answer.condition() : answer.condition))
                return '<li gameDialogueAnswer="' + i + '">' + answer.message + '</li>';
            else return null;
        }).filter(function (answer) {
            return !!answer;
        });

        if (possibleAnswers.length > 0) {
            var list = '<ul>' + possibleAnswers.join('') + '</ul>';
            dialogueBox.html(list);
            $('[gameDialogueAnswer]').on('click', function () {
                var answer = $(this).attr('gameDialogueAnswer');
                if (typeof answers[answer].action === 'function') answers[answer].action();
                dialogueStepper();
            })
        } else {
            dialogueStepper();
        }
    }

    function plotMessage(part, plotDone) {
        var bubbleNode = !part.bubble ? null : Game.char.svg.select(part.bubble);
        var message = part.message;

        var i = 0;
        var messageStepper = setInterval(function () {
            i++;
            if (i > message.length) {
                clearInterval(messageStepper);
                if (bubbleNode) bubbleNode.style('display', 'block');
                setTimeout(function () {
                    if (bubbleNode) bubbleNode.style('display', 'none');
                    if (part.action) part.action();
                    plotDone();
                }, speed.pause)
            } else {
                if (bubbleNode) bubbleNode.style('display', (i % 2) ? 'none' : 'block');
                dialogueBox.text(message.substring(0, i));
            }
        }, speed.talk);
    }
};
