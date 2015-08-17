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
            "contributors" => ['Manu Herrmann']
        ];
    }

    public function getJSDependencies() {
        return ['jslibs/d3.min.js', 'game.js'];
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
            <script>load_game();</script>';
    }

}