<?php

class Game1SignupMethod extends SignupMethod {

    public static function getName() {
        return '↠ Mindblow Mode ↞';
    }

    public static function getAltText() {
        return "Ein Spiel mit Rittern, Schloss und Dorf! [Empfohlene Methode]";
    }

    public static function getMetaInfo() {
        return [
            "version" => '1.0',
            "date" => '15.09.2015',
            "contributors" => ['Manu Herrmann', 'Tim Repke']
        ];
    }

    public function getJSDependencies() {
        return ['../../js/jquery-1.11.1.min.js', '../../js/jquery-ui.min.js', 'jslib/d3.min.js', 'jslib/priority-queue.min.js', 'jslib/checkLineIntersection.js',
            'js/events.js', 'js/achievements.js', 'js/svgUtils.js', 'js/pathFinder.js', 'js/vector.js', 'js/character.js', 'js/camera.js',
            'js/environment.js', 'js/story.js', 'js/game.js'];
    }

    public function getCSSDependencies() {
        return ['ui.css'];
    }

    public function getAdditionalHeader() {
        return '';
    }

    public function showInlineHTML() {
        $environment = Environment::getEnv();

        $dates = comm_get_possible_dates($environment->database, $environment->getSelectedTripId());
        foreach ($dates as &$date)
            $date = '"' . $date . '"';
        echo '
			<script type="text/javascript">
				var env_possible_dates = [' . implode(', ', $dates) . '];
            </script>';

        echo '
            <div id="game-root-container">
                <div id="game-sidebar" class="bordered-box">
                    <div class="sidebar-section">
                        <div class="sidebar-section-head">Game log</div>
                        <div class="sidebar-log"><div>
                            <ul id="game-log">
                                <li>Gehe in das Schloss!</li>
                                <li>Bestes Anmeldesystem aller Zeiten gestartet!</li>
                            </ul>
                        </div></div>
                    </div>
                    <div class="sidebar-section">
                        <div class="sidebar-section-head">Achievements</div>
                        <div class="status-bar" id="achievement-progress" style="margin-bottom: 0.5em"><span style="width:25%" class="status-bar-bar"></span> <div class="status-bar-text">5/43</div> </div>
                        <div class="sidebar-log"><div>
                            <ul id="achievement-log"></ul>
                        </div></div>
                    </div>
                </div>

                <div id="game-game">
                    <div id="gameCanvas" style="overflow:hidden;position:relative">
                        <div id="gameRoot" style="position:relative"></div>
                        <div id="gameDialogue" class="bordered-box"></div>
                    </div>
                </div>

                <div id="game-overlay">
                </div>
            </div>

            <script>
                g_smallValue = 0.000001; // fun with floats

                var FAPI = new FAPI();

                var game = new Game({
                    startMap: \'map_landing\',
                    showEventLayers: false,
                    pathFindingGridSize: 5,
                    usePathFinding: true,
                    size: [800, 600]
                });

                // this following stuff is to prevent the page from scrolling, when the user
                // actually just wants to scroll inside the logs.
                // it removes the main scrollbar and adds a padding of its size to replace the space
                $(function() {
                    game.run();

                    var scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
                    var x = document.getElementsByClassName("sidebar-log");
                    for (var i = 0; i < x.length; i++) {
                        x[i].addEventListener("mouseout", function(){
                            document.body.style.overflow=\'auto\';
                            document.body.style.paddingRight = "0px";
                        }, false);
                        x[i].addEventListener("mouseover", function(){
                            document.body.style.overflow=\'hidden\';
                            document.body.style.paddingRight = scrollbarWidth+"px";
                        }, false);
                    }
                });
            </script>';
    }

}
