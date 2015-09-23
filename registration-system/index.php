<?php
session_start();

require 'frameworks/environment.php';
require 'view/signups/index.php';

require 'view/default_index.php';

// =====================================================================================================================
// HEADERS

function echo_headers() {
    echo index_get_css_includes();
    echo index_get_js_includes();
    echo index_get_additional_headers();
}

function index_get_css_includes() {
    $basefolder = 'view/';

    $base_styles = ['style.css'];
    $additional_styles = index_get_dependencies_helper('getCSSDependencies');

    $styles = array_merge($base_styles, $additional_styles);

    $ret = '';
    foreach ($styles as $style) {
        $ret .= "<link rel=\"stylesheet\" href=\"" . $basefolder . $style . "\" />\n";
    }
    return $ret;
}

function index_get_js_includes() {
    $basefolder = 'view/';

    $base_js = [
        'js/jquery-1.11.1.min.js',
        'js/jquery-ui.min.js',
        'js/angular.min.js',
        'js/api.js'
    ];
    $additional_js = index_get_dependencies_helper('getJSDependencies');

    $scripts = array_merge($base_js, $additional_js);

    $ret = '';
    foreach ($scripts as $script) {
        $ret .= "<script type=\"text/javascript\" src=\"" . $basefolder . $script . "\"></script>\n";
    }
    return $ret;
}

function index_get_dependencies_helper($dependency_function_name) {
    $methods_basefolder = 'signups/';

    $signup_method = SignupMethods::getInstance();

    if ($signup_method->signupMethodExists()) {
        $method_folder = $signup_method->getActiveMethodId();

        return array_map(function ($d) use ($methods_basefolder, $method_folder) {
            return $methods_basefolder . $method_folder . '/' . $d;
        }, $signup_method->getActiveMethod()->$dependency_function_name());
    }
    return [];
}

function index_get_additional_headers() {
    $signup_method = SignupMethods::getInstance();
    if ($signup_method->signupMethodExists()) {
        return $signup_method->getActiveMethod()->getAdditionalHeader();
    }
    return '';
}


// =====================================================================================================================
// CONTENT

function show_content() {
    $environment = Environment::getEnv();

    if (!$environment->isSelectedTripIdValid()) {
        index_show_alleFahrten();
    } else {
        $fid = $environment->getSelectedTripId();

        try {
            $opentime = $environment->database->select('fahrten', ['opentime'], ['fahrt_id' => $fid])[0]['opentime'];
        } catch (Exception $ex) {
            $opentime = 0;
        }

        // --- Fahrtinfos
        index_show_fahrtHeader($fid);
        index_show_fahrtHeader_js($fid);

        if ($opentime < time()) {
            // --- Anmeldebox
            index_show_signup();

            // --- Liste der Anmeldungen
            index_show_signupTable($fid);
        } else index_show_countdown($opentime);

    }
}


// ---------------------------------------------------------------------------------------------------------------------
// SIGNUP AREA

function index_show_signup() {
    $environment = Environment::getEnv();
    $signup_method = SignupMethods::getInstance();

    $fid = $environment->getSelectedTripId();
    $openstatus = $environment->getRegistrationState($fid);
    $waitlist_confirmed = $environment->isInWaitlistMode();


    echo '<div id="signup-container">';
    echo '<h2>Anmeldung</h2>';

    // Anmeldung erfolgreich
    if (isset($_REQUEST['success'])) {
        echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Die Anmeldung war erfolgreich.</div>';
    } // Anmeldung fehlgeschlagen, weil voll
    elseif (isset($_REQUEST['full'])) {
        echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Anmeldung leider fehlgeschlagen.</div>';
        echo '<div style="text-align:center; font-size: 16pt; font-weight: bold">Die Anmeldegrenze wurde leider erreicht.</div>';
        echo '<div style="text-align:center; font-size: 14pt; ">Es besteht die Möglichkeit sich auf der Warteliste einzutragen.<br />
              Wenn du das möchtest, klicke hier: <a class="normallink" href="?fid=' . $fid . '&waitlist">&#8694; Warteliste</a></div>';
    } // Formulardaten empfangen -> auswerten!
    elseif ($environment->formDataReceived()) {
        comm_verbose(1, "Formular bekommen");

        $sub = $signup_method->validateSubmission();

        if ($sub['valid']) {
            if ($environment->sendBachelorToDB($sub['data']))
                header("Location: ?fid=" . $fid . "&success");
            else
                header("Location: ?fid=" . $fid . "&full");
            die();
        } else {
            index_show_errors($sub['errors']);
            $environment->setDanglingFormData($sub['data']);
            $signup_method->getFallbackMethod()->showInlineHTML();
        }
    } // Anmeldung anzeigen (Form or game)
    elseif ($signup_method->signupMethodExists() &&
        ($openstatus == 0 || ($waitlist_confirmed && $openstatus < 2))
    ) {

        $signup_method->getActiveMethod()->showInlineHTML();

        // future feature: option to show edit view (when $_GET['bid'] isset)
    } // Anmeldeoptionen anzeigen
    else {
        if ($openstatus > 0 && !$waitlist_confirmed) {
            echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Die Anmeldung wurde geschlossen.</div>';

            if ($openstatus != 2) {
                echo '
                <div style="text-align:center; font-size: 16pt; font-weight: bold">
                    Ein Auge offen halten, ob Plätze frei werden.
                </div><div style="text-align:center; font-size: 14pt;">
                    Es gibt aber auch eine Warteliste.<br />
                    Wenn du da rauf möchtest, klicke hier:
                    <a class="normallink" href="?fid=' . $fid . '&waitlist">&#8694; Warteliste</a>
                </div>';
            }
        } elseif ($openstatus < 2) {
            $methods = $signup_method->getSignupMethodsBaseInfo();
            $link = '?fid=' . $fid . ($waitlist_confirmed ? '&waitlist' : '') . '&method=';

            echo '<p>Es stehen verschiedene Methoden zur Anmeldung offen. Wähle eine davon:';

            echo '<ul id="method-list">';
            foreach ($methods as $method) {
                echo '<li><a href="' . $link . $method['id'] . '">' . $method["name"] . '</a> <br />' . $method['description'] . '</li>';
            }
            echo '</ul>';
        }

    }

    echo '</div>'; // close signup-container
}

/**
 * puts out a list of all errors
 * @param $errors
 */
function index_show_errors($errors) {
    echo '<div class="message error"><ul>';
    foreach ($errors as $e) {
        echo '<li>' . $e . '</li>';
    }
    echo '</ul></div>';
}


// ---------------------------------------------------------------------------------------------------------------------
// TRIP LISTING


/**
 * show list of all fahrten
 */
function index_show_alleFahrten() {
    $environment = Environment::getEnv();

    comm_verbose(2, "Liste aller Fahrten (Jahr, Ziel, Zeitraum, Anz. Mitfahrer)");
    echo '<h2>Anmeldung zur Fachschaftsfahrt</h2>';
    $foos = $environment->database->select("fahrten",
        ['fahrt_id', 'titel', 'ziel', 'von', 'bis', 'beschreibung', 'leiter', 'kontakt', 'max_bachelor'],
        "ORDER BY fahrt_id DESC");

    if (!$foos) {
        echo 'Keine Fahrten im System gefunden';
        return;
    }

    $fids = [];
    foreach ($foos as $foo) {
        index_show_fahrtHeader($foo);
        array_push($fids, $foo['fahrt_id']);
    }
    index_show_fahrtHeader_js($fids);
}

/**
 * @param $fahrt wenn array, dann Datenbankrow; wenn zahl, dann wird das selektiert
 */
function index_show_fahrtHeader($fahrt) {
    $environment = Environment::getEnv();

    if (!is_array($fahrt)) {
        // select fahrt by ID
        $fahrt = $environment->database->select('fahrten',
            ['fahrt_id', 'titel', 'ziel', 'von', 'bis', 'leiter', 'kontakt', 'beschreibung', 'max_bachelor'],
            ['fahrt_id' => $fahrt]);
        if (!$fahrt)
            return; // break here and show nothing!
        else
            $fahrt = $fahrt[0];
    }

    $cnt = $environment->database->count("bachelor", ["AND" =>
        ["backstepped" => NULL,
            "fahrt_id" => $fahrt['fahrt_id']]]);

    echo '<div class="fahrt">
            <div class="fahrt-left">
            <a  class="fahrthead" href="index.php?fid=' . $fahrt['fahrt_id'] . '">' . $fahrt['titel'] . '</a>';
    echo 'Ziel: <i>' . $fahrt['ziel'] . '</i><br />';
    echo 'Datum: <i>' . comm_from_mysqlDate($fahrt['von']) . " - " . comm_from_mysqlDate($fahrt['bis']) . '</i><br />';
    echo "Ansprechpartner: <i>" . $fahrt['leiter'] . " (" . comm_convert_mail($fahrt['kontakt']) . ")</i><br />";
    echo "Anmeldungen: <i>" . $cnt . " / " . $fahrt['max_bachelor'] . "</i>";
    echo '<p>' . $fahrt['beschreibung'] . '</p></div>
            <div class="map-canvas" id="map-canvas-' . $fahrt['fahrt_id'] . '"></div>
            <div style="clear:both"></div>
    </div>';
}

function index_show_fahrtHeader_js($fahrten) {
    $environment = Environment::getEnv();

    $pins = $environment->database->select("fahrten", ["fahrt_id", "map_pin"], ["fahrt_id" => $fahrten]);

    echo '<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
        <script type="text/javascript">
            window.onload = function() {
                $("div.fahrt-left i").click(function(){
                    });

               $("div.fahrt-left i").hover(
                    function(){
                        $(this).html($(this).html().replace(/·/g, "&#128045;"));
                        $(this).html($(this).html().replace("Ø", "&#128053;"));
                    },
                    function(){
                        $(this).html($(this).html().replace(/🐭/g, "&middot;"));
                        $(this).html($(this).html().replace("🐵", "&Oslash;"));
                    }
                );
            };';

    foreach ($pins as $p) {
        // if not valid GPS pos, fallback to Kheta!
        if (!preg_match("/\\d+\\.\\d+ \\d+\\.\\d+/m", $p["map_pin"]))
            $p["map_pin"] = '71.555267 99.690962';

        echo '
            var ziel_' . $p['fahrt_id'] . ' = new google.maps.LatLng(' . str_replace(" ", ", ", $p["map_pin"]) . ');
            var marker_' . $p['fahrt_id'] . ';
            var map_' . $p['fahrt_id'] . ';
            ';

    }
    echo '
            function initialize() {
            ';

    foreach ($pins as $p) {
        echo '
                var mapOptions_' . $p['fahrt_id'] . ' = {
                    zoom: 8,
                    center: ziel_' . $p['fahrt_id'] . ',
                    panControl: false,
                    zoomControl: false,
                    scaleControl: true,
                    mapTypeControl: false,
                    streetViewControl: false,
                    overviewMapControl: false
                };

                map_' . $p['fahrt_id'] . ' = new google.maps.Map(document.getElementById(\'map-canvas-' . $p['fahrt_id'] . '\'), mapOptions_' . $p['fahrt_id'] . ');

                marker_' . $p['fahrt_id'] . ' = new google.maps.Marker({
                    map:map_' . $p['fahrt_id'] . ',
                    draggable:true,
                    animation: google.maps.Animation.DROP,
                    position: ziel_' . $p['fahrt_id'] . '
                });

                marker_' . $p['fahrt_id'] . '.setAnimation(google.maps.Animation.BOUNCE);';
    }
    echo '
            }

            google.maps.event.addDomListener(window, \'load\', initialize);

        </script>';
}


// ---------------------------------------------------------------------------------------------------------------------
// PUBLIC REGISTRATIONS LISTING

/**
 * show table of public registrations
 */
function index_show_signupTable($fid) {
    $environment = Environment::getEnv();


    echo '<h2>Anmeldungen</h2>';

    $data = $environment->database->select('bachelor',
        ["pseudo", "antyp", "abtyp", "anday", "abday", "comment", "studityp"],
        ["AND" => [
            'fahrt_id' => (int)$fid,
            'public' => 1
        ]]);

    if (!$data) echo '<div class="signups">Noch keine (sichtbaren) Anmeldungen!</div>';
    else {
        echo '
            <table class="signups">
                <thead>
                    <tr>
                        <!--th></th-->
                        <th>Anzeigename</th>
                        <th>Anreisetag</th>
                        <th>Anreiseart</th>
                        <th>Abreisetag</th>
                        <th>Abreiseart</th>
                        <th>Kommentar</th>
                    </tr>
                </thead>';
        foreach ($data as $d) {
            echo '<tr>
                <!--td>' . $d["studityp"] . '</td-->
                <td>' . $d["pseudo"] . '</td>
                <td>' . comm_from_mysqlDate($d["anday"]) . '</td>
                <td>' . index_show_signupTable_destroyTypes($d["antyp"]) . '</td>
                <td>' . comm_from_mysqlDate($d["abday"]) . '</td>
                <td>' . index_show_signupTable_destroyTypes($d["abtyp"]) . '</td>
                <td>' . $d["comment"] . '</td>
            </tr>';
        }
        echo '</table>';
    }
}

function index_show_signupTable_destroyTypes($anabtyp) {
    global $config_reisearten, $config_reisearten_destroyed;
    if (array_search($anabtyp, $config_reisearten) >= 2)
        return $config_reisearten_destroyed[array_rand($config_reisearten_destroyed)];
    return $anabtyp;
}


function index_show_countdown($opentime) {
    echo "
    <script>
        var a = '0123456789abcdef';
        function randstr () {
	        var str = '';
	        for(var i = 0; i < 6; ++i)
		        str += a[Math.floor(Math.random()*16)];
	        return str;
        }
        var b = true;
        function hurrdurr () {
		    $('#text, body').stop().animate({color:b?'#ffffff':'#000000'}, 1000);

		    hurrrdurrr('#menubox');
		    hurrrdurrr('body');

		    function hurrrdurrr(elem) {
                $(elem).stop().animate({backgroundColor:'#'+randstr()}, 333,
                    function () {
                        $(elem).stop().animate({backgroundColor:'#'+randstr()}, 333,
                            function () {
                                $(elem).stop().animate({backgroundColor:'#'+randstr()}, 333);
                            });
                    });
		    }

		    b = !b;
        }
        $(function () {
	        hurrdurr();
	        setInterval(hurrdurr, 1000);
        });
    </script>";

    echo '<div id="text" style="font-weight:bold;text-align:center;font-size:40pt;font-family:Verdana, Geneva, sans-serif">
            ANMELDUNG IN KÜRZE
          </div>';

    echo '<div style="width:100%">
            <div style="margin:0 auto; width:420px">
                <iframe width="420" height="450" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/109529816&amp;auto_play=true&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true"></iframe>
            </div>
          </div>';

}