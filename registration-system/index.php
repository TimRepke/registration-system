<?php
session_start();
require_once __DIR__ . '/frameworks/Environment.php';
require_once __DIR__ . '/view/default_index.php';
require_once __DIR__ . '/view/signups/index.php';

class IndexPage extends DefaultIndex {

    protected $signupMethodDetails;
    public function __construct() {
        parent::__construct();
        $this->signupMethodDetails = SignupMethods::getInstance()->getSignupMethodsBaseInfo();
    }

    // =====================================================================================================================
    // HEADERS
    private function index_get_css_includes() {
        $basefolder = 'view/';

        $base_styles = ['style.css'];
        $additional_styles = $this->index_get_dependencies_helper('getCSSDependencies');

        $styles = array_merge($base_styles, $additional_styles);

        $ret = '';
        foreach ($styles as $style) {
            $ret .= "<link rel=\"stylesheet\" href=\"" . $basefolder . $style . "\" />\n";
        }
        return $ret;
    }

    private function index_get_js_includes() {
        $basefolder = 'view/';

        $base_js = [
            'js/jquery-1.11.1.min.js',
            'js/jquery-ui.min.js',
            'js/angular.min.js',
            'js/elevator.js',
            'js/hurrdurr.js',
            'js/api.js'
        ];
        $additional_js = $this->index_get_dependencies_helper('getJSDependencies');

        $scripts = array_merge($base_js, $additional_js);

        $ret = '';
        $currPathLength = strlen(realpath(".")) + 1;
        $uniq = array();

        foreach ($scripts as $script) {
            $script = substr(realpath($basefolder . $script), $currPathLength);
            if (isset($uniq[$script])) continue;
            $uniq[$script] = true;
            $ret .= "<script type=\"text/javascript\" src=\"" . $script . "\"></script>\n";
        }
        return $ret;
    }

    private function index_get_dependencies_helper($dependency_function_name) {
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

    private function index_get_additional_headers() {
        $signup_method = SignupMethods::getInstance();
        if ($signup_method->signupMethodExists()) {
            return $signup_method->getActiveMethod()->getAdditionalHeader();
        }
        return '';
    }

    protected function echoHeaders() {
        echo $this->index_get_css_includes();
        echo $this->index_get_js_includes();
        echo $this->index_get_additional_headers();
    }


    // =====================================================================================================================
    // CONTENT
    protected function echoContent() {
        if (!$this->environment->isSelectedTripIdValid()) {
            $this->showAlleFahrten();
            $this->makeHurrDurr();
        } else {
            $fahrt = $this->environment->getTrip();

            // --- Fahrtinfos
            $this->showFahrtDetailBlock($fahrt);
            $this->showFahrtDetailJS($fahrt);

            if ($fahrt->getRegistrationState() != Fahrt::STATUS_IS_COUNTDOWN) {
                // --- Anmeldebox
                $this->showSignup($fahrt);

                // --- Liste der Anmeldungen
                $this->showSignupTable($fahrt);

                $this->makeHurrDurr();
            } else {
                $this->showCountdown($fahrt->getOpenTime());
            }
        }
    }

    /**
     * @param $fahrt Fahrt
     */
    private function showFahrtDetailBlock($fahrt) {
        $details = $fahrt->getFahrtDetails();

        echo '<div class="fahrt">
                <div class="fahrt-left">
                    <a  class="fahrthead" href="index.php?fid=' . $details['fahrt_id'] . '">' . $details['titel'] . '</a>
                    Ziel: <i>' . $details['ziel'] . '</i><br />
                    Datum: <i>' . $this->mysql2german($details['von']) . " - " . $this->mysql2german($details['bis']) . '</i><br />
                    Ansprechpartner: <i>' . $details['leiter'] . ' (' . $this->transformMail($details['kontakt']) . ')</i><br />
                    Anmeldungen: <i>' . $fahrt->getNumTakenSpots() . ' / ' . $fahrt->getNumMaxSpots() . '</i>
                    <p>' . $details['beschreibung'] . '</p>
                </div>
                <div class="map-canvas" id="map-canvas-' . $fahrt->getID() . '"></div>
                <div style="clear:both"></div>
             </div>';
    }

    /**
     * @param $fahrten Fahrt[]|Fahrt
     */
    private function showFahrtDetailJS($fahrten) {
        if (!is_array($fahrten))
            $fahrten = [$fahrten];

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

        $fids = [];
        foreach ($fahrten as $fahrt) {
            $fid = $fahrt->getID();
            $pin = $fahrt->getGPS();
            array_push($fids, $fid);
            echo '
                var ziel_' . $fid . ' = new google.maps.LatLng(' . str_replace(" ", ", ", $pin) . ');
                var marker_' . $fid . ';
                var map_' . $fid . ';';
        }
        echo '
            function initialize() {
            ';

        foreach ($fids as $fid) {
            echo '
                var mapOptions_' . $fid . ' = {
                    zoom: 8,
                    center: ziel_' . $fid . ',
                    panControl: false,
                    zoomControl: false,
                    scaleControl: true,
                    mapTypeControl: false,
                    streetViewControl: false,
                    overviewMapControl: false
                };

                map_' . $fid . ' = new google.maps.Map(document.getElementById(\'map-canvas-' . $fid . '\'), mapOptions_' . $fid . ');

                marker_' . $fid . ' = new google.maps.Marker({
                    map:map_' . $fid . ',
                    draggable:true,
                    animation: google.maps.Animation.DROP,
                    position: ziel_' . $fid . '
                });

                marker_' . $fid . '.setAnimation(google.maps.Animation.BOUNCE);';
        }
        echo '
            }

            google.maps.event.addDomListener(window, \'load\', initialize);

        </script>';
    }

    private function makeHurrDurr() {
        echo "<script>
                $(function () {
                    var hurdur = new HurDur();
                    new Elevator({
                        element: document.getElementById('nyan'),
                        mainAudio: 'view/audio/audio.ogg',
                        endAudio: 'view/audio/end-audio.ogg',
                        duration: 5000,
                        startCallback: hurdur.start,
                        endCallback: hurdur.stop
                    });
                });
             </script>";
    }

    private function showAlleFahrten() {
        echo '<h2>Anmeldung zur Fachschaftsfahrt</h2>';
        $fahrten = Fahrt::getAlleFahrten();

        if (!$fahrten) {
            echo 'Keine Fahrten im System gefunden';
        } else {
            foreach ($fahrten as $fahrt) {
                $this->showFahrtDetailBlock($fahrt);
            }
            $this->showFahrtDetailJS($fahrten);
        }
    }

    private function showErrors($errors) {
        echo '<div class="message error"><ul>';
        foreach ($errors as $e) {
            echo '<li>' . $e . '</li>';
        }
        echo '</ul></div>';
    }

    private function showCountdown($opentime) {
        echo '<script type="text/javascript" src="view/js/jquery.qrcode.js"></script>';
        echo "
        <script>
            var opentime = " . $opentime . ";
            var b = true;

            $(function () {
                var url = window.location.href;
                if(url.indexOf('#showQR')>0) $('#QRcode').qrcode({
                    render: 'canvas',
                    ecLevel: 'Q',
                    size: 150,
                    fill: '#000',
                    background: null,
                    text:  url.replace('#showQR',''),
                    radius: 0.5,
                    quiet:2,
                    mode: 2,
                    label: 'FS Fahrt',
                    fontname: 'sans',
                    fontcolor: '#ff9818'
                });
                var hurdur = new HurDur({
                    cats: 10,
                    loopcb: function() {
                        $('#text, body').stop().animate({color:b?'#ffffff':'#000000'}, 1000);

                        var now = (Date.now() + ((new Date()).getTimezoneOffset()*60))/1000;
                        var diff = opentime - now;
                        var view = '';
                        if (diff <= 0) {
                            view = '00:00:00.00';
                        } else {
                            view = hurrdurrr(parseInt(diff/60/60/24, 10)) + 'd ' + hurrdurrr(parseInt(diff / 60 / 60 % 24, 10))
                                + 'h:' + hurrdurrr(parseInt(diff / 60 % 60, 10)) + 'm.' + hurrdurrr(parseInt(diff%60, 10)) + 's';
                        }
                        $('#countdown').html(view);
                        function hurrdurrr(num) {
                            return ((num < 10) ? '0' : '') + num;
                        }
                        b = !b;
                    }
                });
                hurdur.start();
            });
        </script>";

        echo '<div id="text" style="font-weight:bold;text-align:center;font-size:40pt;font-family:Verdana, Geneva, sans-serif">
            ANMELDUNG IN KÜRZE<br />
            <span id="countdown"></span>
          </div>';

        echo '<div style="width:100%; margin-top: 20px;">
            <div style="margin:0 auto; width:300px">
                <iframe width="300" height="300" scrolling="no" frameborder="no"
                src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/109529816&amp;auto_play=true&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true"></iframe>
            </div>
          </div>';

        echo '<canvas id="QRcode" style="position: fixed; bottom: 10px; right: -240px;width:500px;height:250px;"></canvas>';
        echo '<div style="position: fixed; right: 10px; bottom:10px;"><a href="#QRcode">QR-Code</a></div>';
    }

    /**
     * @param $fahrt Fahrt
     */
    private function showSignupTable($fahrt) {
        echo '<h2>Anmeldungen</h2>';

        $bachelorsData = $fahrt->getBachelors(['public' => true, 'backstepped' => false, 'waiting' => false]);

        if (!$bachelorsData) {
            echo '<div class="signups">Noch keine (sichtbaren) Anmeldungen!</div>';
        } else {
            echo '
            <table class="signups" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Anzeigename</th>
                        <th>Anreisetag</th>
                        <th>Anreiseart</th>
                        <th>Abreisetag</th>
                        <th>Abreiseart</th>
                        <th>Kommentar</th>
                    </tr>
                </thead>';
            foreach ($bachelorsData as $d) {
                echo '<tr>
                    <td style="width: 58px;">'.$this->makeSignupBadge($d['signupstats']).' </td>
                    <td>' . $d["pseudo"] . '</td>
                    <td>' . $this->mysql2german($d["anday"]) . '</td>
                    <td>' . $this->translateTravelType($d["antyp"]) . '</td>
                    <td>' . $this->mysql2german($d["abday"]) . '</td>
                    <td>' . $this->translateTravelType($d["abtyp"]) . '</td>
                    <td style="word-break:break-all;">' . $d["comment"] . '</td>
                </tr>';
            }
            echo '</table>';
        }
    }

    private function makeSignupBadge($stats) {
        $json = (empty($stats)) ? null : json_decode($stats, true);
        if (empty($json) or !isset($json['method']) or !isset($json['methodinfo']) or !isset($this->signupMethodDetails[$json['method']]))
            return '';

        $method = $this->signupMethodDetails[$json['method']];

        $logo = 'view/signups/'.$method['id'].'/'.$method['logo'];
        $score = $method['score']($json['methodinfo']);

        return '<img src="'.$logo.'" style="height: 1.2em;"/>
                <span style="font-size:0.7em;float:right;margin-top:0.8em;">'.$score.'%</span>
                <div class="progressbar"><span style="width: '.$score.'%"></span></div>';
    }

    private function translateTravelType($anabtyp) {
        if ($anabtyp == 'INDIVIDUELL') {
            return $this->environment->config['reiseartenDestroyed'][array_rand($this->environment->config['reiseartenDestroyed'])];
        }
        if (isset($this->environment->oconfig['reiseartenShort'][$anabtyp]))
            return $this->environment->oconfig['reiseartenShort'][$anabtyp];
        // well done, hacked the system, gets props for it :)
        return $anabtyp;
    }

    /**
     * @param $fahrt Fahrt
     */
    private function showSignup($fahrt) {
        $signup_method = SignupMethods::getInstance();

        $fid = $fahrt->getID();
        $status = $fahrt->getRegistrationState();
        $waitlist_confirmed = $this->environment->isInWaitlistMode();


        echo '<div id="signup-container">';
        echo '<h2>Anmeldung</h2>';

        // Anmeldung erfolgreich
        if (isset($_REQUEST['success'])) {
            echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Die Anmeldung war erfolgreich.</div>';
        } // Anmeldung fehlgeschlagen, weil voll oder duplikat
        elseif (isset($_REQUEST['saveerror'])) {
            echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Anmeldung fehlgeschlagen.</div>';
            echo '<div style="text-align:center; font-size: 16pt; font-weight: bold">';

            $error = (int)$_REQUEST['saveerror'];
            if ($error == Bachelor::SAVE_ERROR_FULL) {
                echo 'Die Anmeldegrenze wurde leider erreicht.</div>';
                echo '<div style="text-align:center; font-size: 14pt; ">
                        Es besteht die Möglichkeit sich auf der Warteliste einzutragen.<br />
                        Wenn du das möchtest, klicke hier: <a class="normallink" href="?fid=' . $fid . '&waitlist">&#8694; Warteliste</a></div>';
            } elseif ($error == Bachelor::SAVE_ERROR_DUPLICATE) {
                echo 'Es scheint so, als hättest du dich bereits angemeldet...</div>';
            } elseif ($error == Bachelor::SAVE_ERROR_CLOSED) {
                echo 'Die Anmeldung ist bereits geschlossen!</div>';
            } elseif ($error == Bachelor::SAVE_ERROR_MISSING_RIGHTS) {
                echo 'Das System hat beschlossen, dass du dazu nicht berechtigt bist...</div>';
            } elseif ($error == Bachelor::SAVE_ERROR_INVALID) {
                echo 'Deine Daten sind Murks und das ist erst beim Speichern aufgefallen.</div>';
            } else { // $error == Bachelor::SAVE_ERROR_EXCEPTION
                echo 'Etwas ist gehörig schiefgelaufen. Nochmal probieren oder Mail schreiben!</div>';
            }
        } // Formulardaten empfangen -> auswerten!
        elseif ($this->environment->formDataReceived()) {
            $bachelor = $this->environment->getBachelor(false, false, true);

            if ($bachelor->isDataValid()) {
                $saveResult = $bachelor->save();
                if ($saveResult == Bachelor::SAVE_SUCCESS)
                    header("Location: ?fid=" . $fid . "&success");
                else
                    header("Location: ?fid=" . $fid . "&saveerror=" . $saveResult);
            } else {
                if (!isset($_REQUEST['hideErrors'])) {
                    $this->showErrors($bachelor->getValidationErrors());
                }
                $signup_method->getFallbackMethod()->showInlineHTML();
            }
        } // Anmeldung anzeigen
        elseif ($signup_method->signupMethodExists() && ($status == Fahrt::STATUS_IS_OPEN_NOT_FULL ||
                ($waitlist_confirmed && $status == Fahrt::STATUS_IS_OPEN_FULL))
        ) {
            $signup_method->getActiveMethod()->showInlineHTML();
        } // Anmeldung geschlossen
        elseif ($status == Fahrt::STATUS_IS_CLOSED) {
            echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Die Anmeldung wurde geschlossen.</div>';
        }
        elseif ($status == Fahrt::STATUS_IS_OPEN_FULL && !$waitlist_confirmed) {
            echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Die Fahrt ist voll voll.</div>
                <div style="text-align:center; font-size: 16pt; font-weight: bold">
                    Ein Auge offen halten, ob Plätze frei werden.
                </div><div style="text-align:center; font-size: 14pt;">
                    Es gibt aber auch eine Warteliste.<br />
                    Wenn du da rauf möchtest, klicke hier:
                    <a class="normallink" href="?fid=' . $fid . '&waitlist">&#8694; Warteliste</a>
                </div>';
        } else {
                $methods = $signup_method->getSignupMethodsBaseInfo();
                $link = '?fid=' . $fid . ($waitlist_confirmed ? '&waitlist' : '') . '&method=';
                echo '<p>Es stehen verschiedene Methoden zur Anmeldung zur Verfügung. Wähle eine davon:';

                echo '<ul id="method-list">';
                foreach ($methods as $method) {
                    echo '<li><a href="' . $link . $method['id'] . '#signup-container">' . $method["name"] . '</a> <br />' .
                        $method['description'] . '</li>';
                }
                echo '</ul>';
        }
        echo '</div>'; // close signup-container
    }
}

(new IndexPage())->render();
