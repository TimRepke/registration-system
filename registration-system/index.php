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
    return '<link rel="stylesheet" href="view/style.css" />';
}

function index_get_js_includes() {
    return '
        <script type="text/javascript" src="view/js/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="view/js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="view/js/angular.min.js"></script>
        <script type="text/javascript" src="view/js/soft_protect.js"></script>
        <script type="text/javascript" src="view/js/story.js"></script>';
}

function index_get_additional_headers() {
    return '';
}







// =====================================================================================================================
// CONTENT

function show_content() {
    $environment = Environment::getEnv();

    if(!$environment->isSelectedTripIdValid()) {
        index_show_alleFahrten();
    } else {
        $fid = $environment->getSelectedTripId();

        // --- Fahrtinfos
        index_show_fahrtHeader($fid);
        index_show_fahrtHeader_js($fid);

        // --- Anmeldebox
        index_show_signup();

        // --- Liste der Anmeldungen
        index_show_signupTable($fid);
    }
}



// ---------------------------------------------------------------------------------------------------------------------
// SIGNUP AREA

function index_show_signup() {

    $environment   = Environment::getEnv();
    $signup_method = new SignupMethods();

    $fid        = $environment->getSelectedTripId();
    $openstatus = $environment->getRegistrationState($fid);
    $waitlist_confirmed = $environment->isInWaitlistMode();

    // Anmeldung erfolgreich
    if(isset($_REQUEST['success'])) {
        echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Die Anmeldung war erfolgreich.</div>';
    }

    // Anmeldung fehlgeschlagen, weil voll
    elseif (isset($_REQUEST['full'])) {
        echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Anmeldung leider fehlgeschlagen.</div>';
        echo '<div style="text-align:center; font-size: 16pt; font-weight: bold">Die Anmeldegrenze wurde leider erreicht.</div>';
        echo '<div style="text-align:center; font-size: 14pt; ">Es besteht die Möglichkeit sich auf der Warteliste einzutragen.<br />
              Wenn du das möchtest, klicke hier: <a class="normallink" href="?fid='.$fid.'&waitlist">&#8694; Warteliste</a></div>';
    }

    // Formulardaten empfangen -> auswerten!
    elseif(isset($_REQUEST['submit']) || isset($_REQUEST['storySubmit'])){
        comm_verbose(1,"Formular bekommen");

        $sub = $signup_method->validateSubmission();

        if ($sub['valid']) {
            if (index_form_to_db($sub['data']))
                header("Location: ?fid=".$fid."&success");
            else
                header("Location: ?fid=".$fid."&full");
            die();
        } else {
            //TODO include that behaviour:
            // index_show_errors($errors);
            // index_show_formular($fid, NULL, $data);
        }
    }

    // Anmeldung anzeigen (Form or game)
    elseif ($signup_method->signupMethodExists() &&
        ($openstatus == 0 || ($waitlist_confirmed && $openstatus < 2 ))) {

        $signup_method->getActiveMethod()->showInlineHTML();
    }

    // Anmeldeoptionen anzeigen
    else {

        if($openstatus > 0 && !$waitlist_confirmed) {
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

            echo '<ul id="method-list">';
            foreach ($methods as $method) {
                echo '<li><a href="' . $link . $method['id'] . '">' . $method["name"] . '</a> <br />' . $method['description'] . '</li>';
            }
            echo '</ul>';
        }

    }
}






// ---------------------------------------------------------------------------------------------------------------------
// TRIP LISTING


/**
 * show list of all fahrten
 */
function index_show_alleFahrten() {
    $environment = Environment::getEnv();

    comm_verbose(2,"Liste aller Fahrten (Jahr, Ziel, Zeitraum, Anz. Mitfahrer)");
    echo '<h2>Anmeldung zur Fachschaftsfahrt</h2>';
    $foos = $environment->database->select("fahrten",
        ['fahrt_id','titel','ziel','von','bis','beschreibung','leiter','kontakt', 'max_bachelor'],
        "ORDER BY fahrt_id DESC");
    $fids = [];
    foreach($foos as $foo){
        index_show_fahrtHeader($foo);
        array_push($fids, $foo['fahrt_id']);
    }
    index_show_fahrtHeader_js($fids);
}

/**
 * @param $fahrt wenn array, dann Datenbankrow; wenn zahl, dann wird das selektiert
 */
function index_show_fahrtHeader($fahrt){
    $environment = Environment::getEnv();

    if(!is_array($fahrt)){
        // select fahrt by ID
        $fahrt = $environment->database->select('fahrten',
            ['fahrt_id','titel','ziel', 'von', 'bis', 'leiter', 'kontakt', 'beschreibung', 'max_bachelor'],
            ['fahrt_id'=> $fahrt]);
        if(!$fahrt)
            return; // break here and show nothing!
        else
            $fahrt = $fahrt[0];
    }

    $cnt = $environment->database->count("bachelor", ["AND"=>
        ["backstepped" => NULL,
            "fahrt_id"    => $fahrt['fahrt_id']]]);

    echo '<div class="fahrt">
            <div class="fahrt-left">
            <a  class="fahrthead" href="index.php?fid='.$fahrt['fahrt_id'].'">'.$fahrt['titel'].'</a>';
    echo 'Ziel: <i>'.$fahrt['ziel'].'</i><br />';
    echo 'Datum: <i>'.comm_from_mysqlDate($fahrt['von'])." - ".comm_from_mysqlDate($fahrt['bis']).'</i><br />';
    echo "Ansprechpartner: <i>".$fahrt['leiter']." (".comm_convert_mail($fahrt['kontakt']).")</i><br />";
    echo "Anmeldungen: <i>".$cnt." / ".$fahrt['max_bachelor']."</i>";
    echo '<p>'.$fahrt['beschreibung'].'</p></div>
            <div class="map-canvas" id="map-canvas-'.$fahrt['fahrt_id'].'"></div>
            <div style="clear:both"></div>
    </div>';
}

function index_show_fahrtHeader_js($fahrten){
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

    foreach($pins as $p){
        echo'
            var ziel_'.$p['fahrt_id'].' = new google.maps.LatLng('.str_replace(" ", ", ", $p["map_pin"] ).');
            var marker_'.$p['fahrt_id'].';
            var map_'.$p['fahrt_id'].';
            ';
    }
    echo'
            function initialize() {
            ';

    foreach($pins as $p){
        echo'
                var mapOptions_'.$p['fahrt_id'].' = {
                    zoom: 8,
                    center: ziel_'.$p['fahrt_id'].',
                    panControl: false,
                    zoomControl: false,
                    scaleControl: true,
                    mapTypeControl: false,
                    streetViewControl: false,
                    overviewMapControl: false
                };

                map_'.$p['fahrt_id'].' = new google.maps.Map(document.getElementById(\'map-canvas-'.$p['fahrt_id'].'\'), mapOptions_'.$p['fahrt_id'].');

                marker_'.$p['fahrt_id'].' = new google.maps.Marker({
                    map:map_'.$p['fahrt_id'].',
                    draggable:true,
                    animation: google.maps.Animation.DROP,
                    position: ziel_'.$p['fahrt_id'].'
                });

                marker_'.$p['fahrt_id'].'.setAnimation(google.maps.Animation.BOUNCE);';
    }
    echo'
            }

            google.maps.event.addDomListener(window, \'load\', initialize);

        </script>';
}






// ---------------------------------------------------------------------------------------------------------------------
// PUBLIC REGISTRATIONS LISTING

/**
 * show table of public registrations
 */
function index_show_signupTable($fid){
    $environment = Environment::getEnv();


    echo '<h2>Angemeldet</h2>';

    $data = $environment->database->select('bachelor',
        ["pseudo","antyp","abtyp","anday","abday","comment","studityp"],
        ["AND" => [
            'fahrt_id' => (int) $fid,
            'public'   => 1
        ]]);

    if(!$data) echo'<div class="signups">Noch keine (sichtbaren) Anmeldungen!</div>';
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
        foreach($data as $d){
            echo '<tr>
                <!--td>'.$d["studityp"].'</td-->
                <td>'.$d["pseudo"].'</td>
                <td>'.comm_from_mysqlDate($d["anday"]).'</td>
                <td>'.index_show_signupTable_destroyTypes($d["antyp"]).'</td>
                <td>'.comm_from_mysqlDate($d["abday"]).'</td>
                <td>'.index_show_signupTable_destroyTypes($d["abtyp"]).'</td>
                <td>'.$d["comment"].'</td>
            </tr>';
        }
        echo '</table>';
    }
}

function index_show_signupTable_destroyTypes($anabtyp){
    global $config_reisearten, $config_reisearten_destroyed;
    if(array_search($anabtyp, $config_reisearten)>=2)
        return $config_reisearten_destroyed[array_rand($config_reisearten_destroyed)];
    return $anabtyp;
}