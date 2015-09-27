function Story() {

}

Story.actions = {

    // =================================================================================================================
    // Actions in the Fachschaft room

    'castlee_becomemoneyboy': {
        possible: function () {
            return Environment.progress.inventory_money !== true;
        },
        action: function (event, context) {
            if (context.bEnter === true) {
                Game.achievements.triggerAchievement('moneyboy');
                if (Environment.progress.fs_firstApproach)
                    Game.log("Du hast das Geld. Hol dir die Rüstung in der Fachschaft ab.");
                Environment.progress.inventory_money = true;
                d3.select('#moneybags').attr('opacity', 0);
            }
        }
    },
    'castlee_door': {
        state: {
            doorInitialPos: {}
        },
        possible: function () {
            return true;
        },
        action: function (event, context) {
            if (context.bEnter === null) return;

            var state = Story.actions.castlee_door.state;
            var doorLeftId = '#' + event.id + '_l';
            var doorLeft = Game.char.svg.select(doorLeftId);
            var doorRightId = '#' + event.id + '_r';
            var doorRight = Game.char.svg.select(doorRightId);

            if (state.doorInitialPos[doorLeftId] === void 0)
                state.doorInitialPos[doorLeftId] = Number(doorLeft[0][0].getAttribute('x'));
            if (state.doorInitialPos[doorRightId] === void 0)
                state.doorInitialPos[doorRightId] = Number(doorRight[0][0].getAttribute('x'));

            var l = state.doorInitialPos[doorLeftId];
            var r = state.doorInitialPos[doorRightId];

            var moveTo = context.bEnter ? 50 : 0;

            doorLeft.transition()
                .duration(300)
                .attr('x', l - moveTo);
            doorRight.transition()
                .duration(300)
                .attr('x', r + moveTo);
        }
    },
    'fs_firstApproach': {
        state: {
            welcome_message: false, // welcome message spoken
            studityp: false, // asked for studityp
            failed: false, // was approached before, but had no money
            successful: false // all done with this action (equivalent to Environment.progress.fs_firstApproach)
        },
        possible: function () {
            var state = Story.actions.fs_firstApproach.state;
            return (!state.successful && !state.failed) ||
                (!state.successful && state.failed && Environment.progress.inventory_money);
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
                    }],
                    condition: !state.studityp
                }, {
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
            fields.fs_board_name_family.elem.bind('input propertychange', function () {
                validateFields();
            });
            fields.fs_board_name_given.elem.bind('input propertychange', function () {
                validateFields();
            });
            fields.fs_board_email.elem.bind('input propertychange', function () {
                validateFields();
            });

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
    'landing_askingNickname': {
        possible: function () {
            return !Environment.progress.landing_askedNickname;
        },
        action: function () {
            Environment.progress.landing_askedNickname = true;
            Story.dialogueHelper([{
                bubble: '#stranger_speech',
                message: 'Hallo Fremder!'
            }, {
                bubble: '#stranger_speech',
                message: 'Bei welch\' Namen willst du gerufen werden?'
            }, {
                input: {
                    message: 'Gib\' hier einen Nicknamen an',
                    check: function (value) {
                        return Environment.fapi.data.testValidValue('pseudo', value);
                    },
                    action: function (value) {
                        Environment.fapi.data.setValue('pseudo', value);
                    }
                }
            }, {
                bubble: '#stranger_speech',
                message: 'Komischer Name! Nun gut, ich lasse dich wohl weiter ziehen.'
            }]);

        }
    },

    'landing_goatFight': {
        possible: function () {
            return !Environment.progress.landing_killedGoat;
        },
        action: function () {
            var nodes = {
                blast: Game.char.svg.select('#goat_blast'),
                goat: Game.char.svg.select('#goat'),
                milk: Game.char.svg.select('#goat_milk'),
                meat: Game.char.svg.select('#goat_meat')
            };
            Game.actionsBlocked = true;
            appearanceBlast();

            function startDialogue() {
                Story.dialogueHelper([{
                    message: 'Ohje, Ohjé! Eine wilde Ziege!',
                    action: goatAttacking
                }, {
                    message: 'Vielleicht lässt sie sich ja zähmen...',
                    action: goatAttacking
                }, {
                    answer: [{
                        message: 'Ziege zähmen und melken',
                        action: function () {
                            goatShaking(function () {
                                nodes.milk.style('display', 'block');
                                goatAttacking();
                            })
                        }
                    }]
                }, {
                    message: 'Hm, sie ist immer noch wütend...'
                }, {
                    answer: [{
                        message: 'Ziege streicheln',
                        action: function () {
                            goatShaking(function () {
                                nodes.meat.style('display', 'block');
                                nodes.goat.style('display', 'none');
                            })
                        }
                    }]
                }, {
                    message: 'Huch, jetzt ist sie zu einem Haufen Schinken zerfallen...'
                }], null, function () {
                    Environment.progress.landing_killedGoat = true;
                    Environment.progress.inventory_goatDroppings = true;
                    setTimeout(function () {
                        nodes.milk.style('display', 'none');
                        nodes.meat.style('display', 'none');
                    }, 2000);
                });
            }

            function appearanceBlast() {
                var cnt = 0;
                var looper = setInterval(function () {
                    cnt++;
                    nodes.blast.style('display', (cnt % 2) ? 'block' : 'none');
                    if (cnt > 10) {
                        clearInterval(looper);
                        nodes.goat.style('display', 'block');
                        nodes.blast.style('display', 'none');

                        startDialogue();
                    }
                }, 60);
            }

            function goatAttacking() {
                nodes.goat
                    .transition().attr('transform', translate(-10, 10))
                    .transition().attr('transform', translate(10, -10))
                    .transition().attr('transform', translate(-10, 10))
                    .transition().attr('transform', translate(10, -10));

                function translate(relx, rely) {
                    return 'translate(' + relx + ',' + rely + ')';
                }
            }

            function goatShaking(callback) {
                var goatPos = getInfo(Game.char.svg, nodes.goat);
                nodes.goat
                    .transition().attr('transform', rotate(15))
                    .transition().attr('transform', rotate(-15))
                    .transition().attr('transform', rotate(0))
                    .call(helper_endAll, callback);

                function rotate(deg) {
                    return 'rotate(' + deg + ',' + goatPos.xCenter + ',' + goatPos.yCenter + ')';
                }

                function helper_endAll(transition, callback) {
                    var n = 0;
                    transition.each(function () {
                        ++n;
                    }).each('end', function () {
                        if (!--n && callback) callback.apply(this, arguments);
                    });
                }
            }
        }
    },

    'landing_dorfEntranceApproach': {
        possible: function () {
            return !Environment.progress.landing_dorfEntranceApproach;
        },
        action: function () {
            Environment.progress.landing_dorfEntranceApproach = true;
            Game.char.svg.select('#dorfritter')
                .transition().attr('transform', 'translate(0,15)');

            Story.dialogueHelper([{
                bubble: '#dorfritter_speech',
                message: 'Wie ich sehe, willst du in das Dorf.',
                action: function () {
                    Game.char.svg.select('#dorfritter')
                        .transition().duration(400).attr('transform', 'translate(30,5)');
                }
            }, {
                bubble: '#dorfritter_speech',
                message: 'Wenn du über 18 bist, nutze das rechte Tor.',
                action: function () {
                    Game.char.svg.select('#dorfritter')
                        .transition().duration(800).attr('transform', 'translate(-30,5)');
                }
            }, {
                bubble: '#dorfritter_speech',
                message: 'Wenn nicht, dann das Linke!',
                action: function () {
                    Game.char.svg.select('#dorfritter')
                        .transition().duration(200).attr('transform', 'translate(0,0)');
                }
            }], null, function () {
                Game.log('Gehe ins Dorf.');
                Game.log('18+ rechtes Tor, sonst das linke');
            });
        }
    },

    'landing_ageChoice': {
        possible: function () {
            return Environment.progress.landing_dorfEntranceApproach && !Environment.progress.landing_ageChosen;
        },
        action: function (event) {
            Environment.progress.landing_ageChosen = true;
            if (event.id === '18plusEntrance') {
                Environment.fapi.data.setValue('virgin', 'Ja');
            } else {
                Environment.fapi.data.setValue('virgin', 'Nein');
            }
        }
    },


    // =================================================================================================================
    // Actions im Dorf

    'dorf_ticket': {
        possible: function () {
            return Environment.progress.dorf_pickedFood && !Environment.progress.dorf_boughtTicket;
        },
        state: {
            datepick: -1,
            bike: false,
            train: false,
            indi: false
        },
        action: function () {
            Game.actionsBlocked = true;
            var relevant_dates = env_possible_dates.slice(0, 2);
            var state = Story.actions.dorf_ticket.state;
            Game.achievements.triggerAchievement('woman');

            Environment.progress.dorf_boughtTicket = true;
            Game.char.svg.select('#inn_nowalk').remove();
            delete Game.char.pathFinder.noWalkNodes['inn_nowalk'];
            Game.char.pathFinder.generateRaster();

            Story.dialogueHelper([{
                bubble: '#prinzessin_speech',
                message: 'Hallo, wie geht es dir?'
            }, {
                answer: [
                    {message: 'Gut'},
                    {message: 'Sehr gut'},
                    {
                        message: 'Ich bin Klepner, heiße Mario, und werde dich retten.',
                        action: function () {
                            Game.achievements.triggerAchievement('plumber');
                        }
                    }
                ]
            }, {
                bubble: '#prinzessin_speech',
                message: 'Mir eigentlich total egal! Du bist hier für ein Ticket schätze ich.'
            }, {
                bubble: '#prinzessin_speech',
                message: 'An welchem Tag willst du denn zur Fahrt aufziehen?'
            }, {
                answer: [{
                    message: 'Mit allen zusammen, also am ' + relevant_dates[0],
                    action: function () {
                        Environment.fapi.data.setValue('anday', relevant_dates[0]);
                        state.datepick = 0;
                    }
                }, {
                    message: 'Später, also am ' + relevant_dates[1],
                    action: function () {
                        Environment.fapi.data.setValue('anday', relevant_dates[1]);
                        state.datepick = 1;
                    }
                }]
            }, {
                bubble: '#prinzessin_speech',
                message: 'Dann musst du dich aber allein um die Anreise kümmern.',
                condition: function () {
                    return state.datepick === 1;
                }
            }, {
                bubble: '#prinzessin_speech',
                message: 'Das wird ein Spaß! Du kannst mit allen zusammen fahren oder dich allein um die Anreise kümmern.',
                condition: function () {
                    return state.datepick === 0;
                }
            }, {
                answer: [{
                    message: 'Mit allen zusammen in der Bahn',
                    condition: function () {
                        return state.datepick === 0;
                    },
                    action: function () {
                        Environment.fapi.data.setValue('antyp', 'BUSBAHN');
                        blinkChoice('train');
                        state.train = true;
                    }
                }, {
                    message: 'Ich möchte mit anderen Rad fahren. Ist ja nicht weit.',
                    condition: function () {
                        return state.datepick === 0;
                    },
                    action: function () {
                        Environment.fapi.data.setValue('antyp', 'RAD');
                        blinkChoice('bike');
                        state.bike = true;
                    }
                }, {
                    message: 'Ich nehme das Boot und kümmere mich allein um die Anreise.',
                    action: function () {
                        Environment.fapi.data.setValue('antyp', 'INDIVIDUELL');
                        blinkChoice('boat');
                        state.indi = true;
                    }
                }]
            }, {
                bubble: '#prinzessin_speech',
                message: 'Meine Güte bist du langweilig!'
            }, {
                bubble: '#prinzessin_speech',
                message: 'Ich gehe jetzt zurück ins Haus und warte weiter auf den Klempner.'
            }], null, function () {
                Game.achievements.triggerAchievement('princess');
                Game.char.svg.select('#ticketfrau')
                    .transition().attr('transform', 'translate(10, 15)')
                    .transition().attr('transform', 'translate(50, 20)')
                    .transition().attr('transform', 'translate(50, -20)')
                    .transition().attr('display', 'none');
                Game.log("Schlafe im INN bis die Reise beginnt.");
            });

            function blinkChoice(id) {
                var node = Game.char.svg.select('#' + id);
                var cnt = 0;
                var looper = setInterval(function () {
                    cnt++;
                    node.style('display', (cnt % 2) ? 'block' : 'none');
                    if (cnt > 8) {
                        clearInterval(looper);
                        node.style('display', 'block');
                    }
                }, 60);
            }
        }
    },

    'dorf_wirt': {
        possible: function () {
            return !Environment.progress.dorf_talkedToWirt;
        },
        action: function (event) {
            Environment.progress.dorf_talkedToWirt = true;
            var food = {
                fleisch: Game.char.svg.select('#fleisch'),
                kaese: Game.char.svg.select('#kaese'),
                griess: Game.char.svg.select('#griess')
            };
            var nodes = {
                fleischBlast: Game.char.svg.select('#fleisch_blast'),
                kaeseBlast: Game.char.svg.select('#kaese_blast'),
                griessBlast: Game.char.svg.select('#griess_blast')
            };

            function appearanceBlast(blastElement, food) {
                var cnt = 0;
                var looper = setInterval(function () {
                    cnt++;
                    blastElement.style('display', (cnt % 2) ? 'block' : 'none');
                    if (cnt > 10) {
                        clearInterval(looper);
                        food.style('display', 'block');
                        blastElement.style('display', 'none');
                    }
                }, 60);
            }

            Story.dialogueHelper([{
                bubble: '#wirt_speech',
                message: 'Na du!? Du bist wohl her gekommen um etwas zum Essen zu holen...'
            }, {
                bubble: '#wirt_speech',
                message: 'Leider habe ich nichts da.'
            }, {
                answer: [{
                    message: 'Kein Problem, ich habe Milch und Schinken einer Ziege.',
                    action: function () {
                    }
                }, {
                    message: 'Egal, mach irgendwas!',
                    action: function () {
                    }
                }]
            }, {
                bubble: '#wirt_speech',
                message: 'Prima! Ich habe dir ein Essen auf den Tisch gestellt.',
                action: function () {
                    appearanceBlast(nodes.fleischBlast, food.fleisch);
                }
            }, {
                bubble: '#wirt_speech',
                message: 'Wenn du kein Fleisch isst, mache ich dir Käse und Brot.',
                action: function () {
                    appearanceBlast(nodes.kaeseBlast, food.kaese);
                }
            }, {
                bubble: '#wirt_speech',
                message: 'Aus der Sojamilch der Ziege habe ich Grießbrei gemacht.',
                action: function () {
                    appearanceBlast(nodes.griessBlast, food.griess);
                }
            }], null, function () {
                Game.log('Wähle deinen Essenswunsch');
            });
        }
    },

    'dorf_pickFood': {
        possible: function () {
            return Environment.progress.dorf_talkedToWirt && !Environment.progress.dorf_pickedFood;
        },
        action: function (event) {
            var food = {
                fleisch: Game.char.svg.select('#fleisch'),
                kaese: Game.char.svg.select('#kaese'),
                griess: Game.char.svg.select('#griess')
            };
            if (event.id === 'click_fleisch') {
                Story.dialogueHelper([{
                    answer: [{
                        message: 'Ich esse alles!',
                        action: function () {
                            Game.log("Du isst also alles.");
                            Environment.progress.dorf_pickedFood = true;
                            Environment.fapi.data.setValue('essen', 'ALLES');
                            food.fleisch.style('display', 'none');
                        }
                    }, {
                        message: 'Fleisch mag ich nicht!',
                        action: function () {
                        }
                    }]
                }], null, onPickDialogEnd);
            } else if (event.id === 'click_kaese') {
                Story.dialogueHelper([{
                    answer: [{
                        message: 'Ich esse vegetarisch!',
                        action: function () {
                            Game.log("Du isst also vegetarisch.");
                            Environment.progress.dorf_pickedFood = true;
                            Environment.fapi.data.setValue('essen', 'VEGE');
                            food.kaese.style('display', 'none');
                        }
                    }, {
                        message: 'Neeee, ich bin nicht auf Diät!',
                        action: function () {
                        }
                    }]
                }], null, onPickDialogEnd);
            } else if (event.id === 'click_griess') {
                Story.dialogueHelper([{
                    answer: [{
                        message: 'Grieß schmeckt gut! Ich bringe mein eigenes Essen mit zur Fahrt!',
                        action: function () {
                            Game.log("Du bringst dein eigenes Essen mit.");
                            Environment.progress.dorf_pickedFood = true;
                            Environment.fapi.data.setValue('essen', 'VEGA');
                            food.griess.style('display', 'none');
                        }
                    }, {
                        message: 'Neee! Da werd ich doch nicht von satt!',
                        action: function () {
                        }
                    }]
                }], null, onPickDialogEnd);
            }

            function onPickDialogEnd() {
                if (Environment.progress.dorf_pickedFood) {
                    setTimeout(function () {
                        food.fleisch.style('display', 'none');
                        food.kaese.style('display', 'none');
                        food.griess.style('display', 'none');
                    }, 1000);
                    Game.log('Gehe zurück ins Dorf');
                }
            }
        }
    },

    'sleep_inn': {
        state: {
        },
        possible: function () {
            return Environment.progress.dorf_boughtTicket && !Environment.progress.sleep_inn;
        },
        action: function(event, context) {
            if (!context.bEnter) return;
            Environment.progress.sleep_inn = true;

            var gameOverlay = $('#game-overlay');
            gameOverlay.fadeIn(300);

            var queue = [
                color('#000000'),
                color('#ffffff'),
                delay(2000),
                storyTalk(0),
                delay(2000),
                color('#000000'),
                color('#30f040'),
                color('#6020f0'),
                color('#205010'),
                color('#6020f0'),
                color('#30f040'),
                color('#205010', 800),
                color('#6020f0', 600),
                color('#30f040', 400),
                color('#205010', 400),
                color('#6020f0', 350),
                color('#205010', 300),
                color('#6020f0', 200),
                color('#30f040', 200),
                color('#205010', 200),
                color('#6020f0', 200),
                color('#30f040', 150),
                color('#205010', 150),
                color('#6020f0', 150),
                color('#205010', 100),
                color('#30f040', 100),
                color('#6020f0', 100),
                color('#30f040', 60),
                color('#6020f0', 60),
                color('#205010', 60),
                color('#000000', 1000),
                delay(2000),
                color('#ffffff', 1000),
                delay(3000),
                storyTalk(1),
                delay(3000),
                color('#000000', 2000),
                nextMap
            ];
            nextAction(); // start the LSD process

            function storyTalk(num) {
                return function() {
                    switch (num) {
                        case 0:
                            Story.dialogueHelper([{
                                message: 'George: Trinkt nicht zu viel ja?!'
                            },{
                                message: 'Jonas: BIIIIIIIIIER BAAAAAALLLLLLLLLLL!'
                            },{
                                message: 'George: ...'
                            }], null, nextAction);
                            break;
                        case 1:
                            Story.dialogueHelper([{
                                message: 'George: Ich habs euch gesagt! Ihr hättet nicht so viel trinken dürfen!'
                            }], null, nextAction);
                            break;
                    }
                };
            }
            function delay(time) {
                return function() {
                    setTimeout(nextAction, time);
                };
            }
            function nextAction() {
                if (queue.length == 0) return;
                queue.shift()();
            }
            function color(color, duration) {
                if (!duration) duration = 1000;
                return function() {
                    gameOverlay.animate({'backgroundColor': color}, {
                        'duration': duration,
                        'complete': function () {
                            nextAction();
                        }
                    });
                }
            }
            function nextMap() {
                Game.instance.nextMap('ufer');
            }
        }
    },


    // =================================================================================================================
    // Actions am Ufer

    'ufer_princess': {
        state: {
            datepick: -1
        },
        possible: function () {
            return !Environment.progress.ufer_princessApproach;
        },
        action: function (event) {
            Environment.progress.ufer_princessApproach = true;
            var state = Story.actions.ufer_princess.state;
            var relevant_dates = env_possible_dates;
            Story.dialogueHelper([
                {
                    bubble: '#prinzessin_speech',
                    message: 'Du schon wieder?'
                }, {
                    bubble: '#prinzessin_speech',
                    message: 'Sicher willst du zurück nach Hause. Wann denn?'
                }, {
                    answer: [{
                        message: 'Zusammen mit den anderen am ' + relevant_dates[2],
                        action: function () {
                            Environment.fapi.data.setValue('abday', relevant_dates[2]);
                            state.datepick = 2;
                        }
                    }, {
                        message: 'Muss schon früher los. (' + relevant_dates[1] + ')',
                        action: function () {
                            Environment.fapi.data.setValue('abday', relevant_dates[1]);
                            state.datepick = 1;
                            Game.char.svg.select('#train').style('display', 'none');
                            Game.char.svg.select('#bike').style('display', 'none');
                        }
                    }]
                }, {
                    bubble: '#prinzessin_speech',
                    message: 'Dann musst du aber mit dem Boot allein fahren.',
                    condition: function () {
                        return state.datepick === 1;
                    },
                    action: function () {
                        Game.log('Klicke auf das Boot')
                    }
                }, {
                    bubble: '#prinzessin_speech',
                    message: 'Okay, ist notiert. Gehe einfach zum Transportmittel deiner Wahl.',
                    condition: function () {
                        return state.datepick === 2;
                    },
                    action: function () {
                        Game.log('Rücktransportmittel deiner Wahl klicken.')
                    }
                }
            ])
        }
    },

    'ufer_pickTransport': {
        possible: function () {
            return Environment.progress.ufer_princessApproach && !Environment.progress.ufer_pickedTransport;
        },
        action: function (event) {
            if ((event.id === 'pick_boat' && Story.actions.ufer_princess.state.datepick === 1) ||
                (Story.actions.ufer_princess.state.datepick === 2)) {

                Environment.progress.ufer_pickedTransport = true;
                Game.actionsBlocked = true;
                Game.char.image.style('opacity', '0');
                new Audio(FAPI.resolvePath('sounds/plop.ogg')).play();

                Story.credits();
            } else {
                console.log('not possible');
            }

            if (event.id === 'pick_train' && Story.actions.ufer_princess.state.datepick === 2) {
                Environment.fapi.data.setValue('abtyp', 'BUSBAHN');
                Game.char.svg.select('#train')
                    .transition().delay(20).duration(3000).attr('transform', 'translate(-800, -35)');
            } else if (event.id === 'pick_bike' && Story.actions.ufer_princess.state.datepick === 2) {
                Environment.fapi.data.setValue('abtyp', 'RAD');
                var bike = Game.char.svg.select('#bike');
                var transl = getTranslation(Game.char.svg[0][0], bike[0][0]);
                bike.transition().attr('transform', 'translate(' + (transl[0] - 800) + ',' + (-transl[1]) + ')');
            } else if (event.id === 'pick_boat') {
                Environment.fapi.data.setValue('abtyp', 'INDIVIDUELL');
                Game.char.svg.select('#boat')
                    .transition().delay(20).duration(3000).attr('transform', 'translate(5000, 800)');
            }
        }
    }
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