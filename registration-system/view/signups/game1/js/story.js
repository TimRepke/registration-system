function Story() {

}

Story.actions = {

    // =================================================================================================================
    // Actions in the Fachschaft room

    'fs_firstApproach': {
        state: {
            welcome_message: false, // welcome message spoken
            studityp: false, // asked for studityp
            failed: false, // was approached before, but had no money
            successful: !false // all done with this action (equivalent to Environment.progress.fs_firstApproach)
        },
        possible: function () {
            return (!Story.actions.fs_firstApproach.state.successful && !Story.actions.fs_firstApproach.state.failed) ||
                (!Environment.progress.fs_firstApproach && Environment.progress.inventory_money);
        },
        action: function () {
            var state = Story.actions.fs_firstApproach.state;

            Story.dialogueHelper([{
                    bubble: '#tim_speech',
                    message: 'Willkommen in der Fachschaft!',
                    condition: !state.welcome_message
                }, {
                    bubble: '#manu_speech',
                    message: 'Kenne ich dich?',
                    condition: !state.studityp
                }, {
                    answer: [{
                        message: 'Klar, studiere doch schon lange hier',
                        action: function () {
                            state.studityp = true;
                            Environment.fapi.data.setValue('studityp', 'HOERS');
                        }
                    }, {
                        message: 'Ja, bin sogar Tutor dieses Jahr',
                        action: function () {
                            state.studityp = true;
                            Environment.fapi.data.setValue('studityp', 'TUTTI');
                        }
                    }, {
                        message: 'Nein, bin neu hier',
                        action: function () {
                            state.studityp = true;
                            Environment.fapi.data.setValue('studityp', 'ERSTI');
                        }
                    }]
                },{
                    bubble: '#tim_speech',
                    message: state.failed ?
                        'Wie ich sehe, hast du etwas gefunden? Noch Lust auf die Rüstung?' :
                        'Du bist ohne Rüstung unterwegs, das kann gefährlich werden. Gegen eine Spende könntest du eine bekommen.'
                }, {
                    answer: [{
                        message: 'Gib her, hier ist ein dicker Sack',
                        condition: Environment.progress.inventory_money,
                        action: function () {
                            Environment.progress.inventory_ruestung = true;
                        }
                    }, {
                        message: 'Was? Spende? Ich hab\' nichts!',
                        condition: !Environment.progress.inventory_money,
                        action: function () {
                            Game.log('Finde den Schatz bei den Fahrstühlen.');
                            state.failed = true;
                        }
                    }]
                }, {
                    bubble: '#manu_speech',
                    message: 'Yoh, geh\' mal rüber zu George.',
                    condition: Environment.progress.inventory_ruestung
                }],
                state,
                function () {
                    state.welcome_message = true;
                    Environment.progress.fs_firstApproach = true;
                    if (Environment.progress.inventory_ruestung) {
                        Game.log('Finde George.');
                        state.successful = true;
                    }
                });
        }
    },
    'fs_screamingGeorge': {
        possible: function () {
            return !Environment.progress.fs_georgeScreamed && Story.actions.fs_firstApproach.state.successful;
        },
        action: function () {
            Story.dialogueHelper([
                {
                    bubble: '#george_speech',
                    message: 'EY! MELD\' DICH MAL ZUR FACHSCHAFTSFAHRT AN!!'
                }, {
                    bubble: '#george_speech',
                    message: 'SCHREIB\' DICH AN DER TAFEL DAZU EIN!!!'
                }
            ], null, function () {
                Game.log('Schreib\' an die zur Tafel.');
                Environment.progress.fs_firstApproach = true;
            });
            Environment.progress.fs_georgeScreamed = true;
        }
    },
    // some hint message when trying to leave fs without all actions complete
    'fs_exit_hint': {
        state: {
            hintGiven: false
        },
        possible: function () {
            return !Story.actions.fs_exit_hint.state.hintGiven && !Environment.progress.fs_filledBoard
                && Environment.progress.fs_firstApproach && Environment.progress.inventory_money;
        },
        action: function () {
            Story.actions.fs_exit_hint.state.hintGiven = true;
            Story.dialogueHelper([
                {
                    bubble: '#tim_speech',
                    message: 'Du bist hier noch nicht fertig. Vorher kommst du nicht raus!'
                }
            ]);
        }
    },
    // name/email signup board
    'fs_open_board': {
        possible: function () {
            return Environment.progress.fs_georgeScreamed && !Environment.progress.fs_filledBoard;
        },
        action: function () {
            Game.actionsBlocked = true;
            var blackboardForm = '' +
                '<div id="fs_board" style="background-image: url(' + FAPI.resolvePath('graphics/fs_blackboard.png') + ');background-color: #385123;background-repeat: no-repeat;height:300px;width: 555px;position: absolute; top: 150px;left: 120px;">' +
                '   <div style="margin: 70px; font-size: 15pt; font-family: \'Comic Sans MS\', cursive, sans-serif;color:white">' +
                '       <div style="float:left">' +
                '           <div id="fs_board_name_given_label">Vorname:</div>' +
                '           <input type="text" id="fs_board_name_given" style="width: 174px; border: 1px dotted black; background: transparent; font: inherit; font-size: 13pt;color: inherit; padding: 4pt 8pt" />' +
                '       </div>' +
                '       <div style="float:right">' +
                '           <div id="fs_board_name_family_label">Nachname:</div>' +
                '           <input type="text" id="fs_board_name_family" style="width: 174px; border: 1px dotted black; background: transparent; font: inherit; font-size: 13pt;color: inherit; padding: 4pt 8pt" />' +
                '       </div>' +
                '       <div style="clear:both; height: 1em">&nbsp;</div>' +
                '           <div id="fs_board_email_label">E-Mail-Adresse:</div>' +
                '           <input type="text" id="fs_board_email" style="width: 390px; border: 1px dotted black; background: transparent; font: inherit; font-size: 13pt;color: inherit; padding: 4pt 8pt" />' +
                '   </div>' +
                '   <button id="fs_board_done" style="cursor: pointer; position: absolute;right: 95px;bottom: 16px;border: 0;background-color: rgba(250, 242, 201, 0);height: 45px;width: 106px;color: white;">Fertig</button>' +
                '</div>';
            $('#gameCanvas').append(blackboardForm);

            // hide the done button till done
            $('#fs_board_done')
                .hide()
                .on('click', function () {
                    saveBoard();
                });

            var fields = {
                fs_board_name_given: {
                    elem: $('#fs_board_name_given'),
                    label: 'fs_board_name_given_label',
                    name: 'forname'
                },
                fs_board_name_family: {
                    elem: $('#fs_board_name_family'),
                    label: 'fs_board_name_family_label',
                    name: 'sirname'
                },
                fs_board_email: {
                    elem: $('#fs_board_email'),
                    label: 'fs_board_email_label',
                    name: 'mehl'
                }
            };

            // hook listeners for field validations
            fields.fs_board_name_family.elem.bind('input propertychange', function () {validateFields();});
            fields.fs_board_name_given.elem.bind('input propertychange', function () {validateFields();});
            fields.fs_board_email.elem.bind('input propertychange', function () {validateFields();});

            function validateFields() {
                // test all fields
                var allValid = true;
                for (var id in fields) {
                    if (Environment.fapi.data.testValidValue(fields[id].name, fields[id].elem.val())) {
                        $('#' + fields[id].label).css('color', 'white');
                    } else {
                        $('#' + fields[id].label).css('color', 'red');
                        allValid = false;
                    }
                }

                // enable done button if all fields pass test
                if (allValid) $('#fs_board_done').show();
            }

            function saveBoard() {
                // push data to FAPI
                Environment.fapi.data.setValue('forname', fields.fs_board_name_given.elem.val());
                Environment.fapi.data.setValue('sirname', fields.fs_board_name_family.elem.val());
                Environment.fapi.data.setValue('mehl', fields.fs_board_email.elem.val());

                // log progress
                Game.actionsBlocked = false;
                Environment.progress.fs_filledBoard = true;
                Game.log('Kontaktdaten verloren.');
                Game.log('Lieber schnell wieder raus hier!');

                // remove the overlay
                $('#fs_board').remove();
            }
        }
    },


    // =================================================================================================================
    // Actions on the landing map

    'landing_goatFight': {
        possible: function () {
            return true;
        },
        action: function () {

        }
    }
};

Story.dialogueHelper = function (dialogue, context, done) {

    var speed = {
        talk: UrlComponents.isSet('fastTalk') ? 1 : 45,
        pause: UrlComponents.isSet('fastTalk') ? 50 : 1000
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
            if ('condition' in part && !part.condition) {
                dialogueStepper();
            } else if (part.answer) {
                answerSelection(part.answer);
            } else {
                plotMessage(part.bubble, part.message, dialogueStepper);
            }
        }
    }

    function answerSelection(answers) {
        var possibleAnswers = answers.map(function (answer, i) {
            if ('condition' in answer && answer.condition)
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
                if (typeof answers[answer].action() === 'function') answers[answer].action();
                dialogueStepper();
            })
        } else {
            dialogueStepper();
        }
    }

    function plotMessage(bubble, message, plotDone) {
        var i = 0;
        var bubbleNode = !bubble ? null : Game.char.svg.select(bubble);
        var messageStepper = setInterval(function () {
            i++;
            if (i > message.length) {
                clearInterval(messageStepper);
                if (bubbleNode) bubbleNode.style('display', 'block');
                setTimeout(function () {
                    if (bubbleNode) bubbleNode.style('display', 'none');
                    plotDone();
                }, speed.pause)
            } else {
                if (bubbleNode) bubbleNode.style('display', (i % 2) ? 'none' : 'block');
                dialogueBox.text(message.substring(0, i));
            }
        }, speed.talk);
    }
};