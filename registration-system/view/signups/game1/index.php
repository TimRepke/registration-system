<?php

class Game1SignupMethod extends SignupMethod {

    public static function getName() {
        return "Das Dorf [working title]";
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
        return ['jslib/d3.min.js', 'jslib/priority-queue.min.js', 'jslib/checkLineIntersection.js',
            'js/svgUtils.js', 'js/pathFinder.js', 'js/vector.js', 'js/character.js','js/camera.js',
            'js/game.js'];
    }

    public function getCSSDependencies() {
        return ['ui.css'];
    }

    public function getAdditionalHeader() {
        return '';
    }

    public function showInlineHTML() {
        echo '
            <div id="game-root-container">
                <div id="game-sidebar">
                    <div class="sidebar-section">
                        <div class="sidebar-section-head">Game log</div>
                        <div class="sidebar-log"><div>
                            <ul id="game-log">
                                <li>Find the students union room</li>
                                <li>Found the castle entrance</li>
                                <li>Go inside the castle!</li>
                                <li>Find the students union room</li>
                                <li>Found the castle entrance</li>
                                <li>Go inside the castle!</li>
                            </ul>
                        </div></div>
                    </div>
                    <div class="sidebar-section">
                        <div class="sidebar-section-head">Achievements</div>
                        <div class="status-bar" id="achievement-progress" style="margin-bottom: 0.5em"><span style="width:25%" class="status-bar-bar"></span> <div class="status-bar-text">5/43</div> </div>
                        <div class="sidebar-log"><div>
                            <ul id="achievement-log">
                                <li>Five clicks in 2 seconds!</li>
                                <li>Took first step!</li>
                            </ul>
                        </div></div>
                    </div>
                </div>

                <div id="game-game">
                    <div id="gameCanvas" style="overflow:hidden;position:relative">
                        <div id="gameRoot" style="position:relative"></div>
                    </div>
                </div>
            </div>

            <script>
                g_smallValue = 0.000001; // fun with floats

                var FAPI = new FAPI();

                var game = new Game({
                    startMap: \'map_landing.svg\',
                    showEventLayers: false,
                    pathFindingGridSize: 5,
                    usePathFinding: true,
                    size: [800, 600]
                });
                game.run();

            </script>';
    }

}