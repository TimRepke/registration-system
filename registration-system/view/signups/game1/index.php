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
        return ['jslib/d3.min.js', 'jslib/checkLineIntersection.js', 'jslib/priority-queue.min.js',
            'js/camera.js','js/character.js','js/pathFinder.js', 'js/svgUtils.js', 'js/vector.js',
            'js/game.js'];
    }

    public function getCSSDependencies() {
        return [];
    }

    public function getAdditionalHeader() {
        return '';
    }

    public function showInlineHTML() {
        echo '<div id="coords">(0, 0)</div>
            <div id="gameCanvas" style="overflow:hidden;position:relative">
                <div id="gameRoot" style="position:relative">
                </div>
            </div>
            <script>
            g_smallValue = 0.000001; // fun with floats

            var game = new Game({
                pathFindingGridSize: 5,
                usePathFinding: true,
                size: [800, 600]
            });
            game.run();

            </script>';
    }

}