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
            "version" => '1.1',
            "date" => '20.09.2014',
            "contributors" => ['Tim Repke <tim@repke.eu>']
        ];
    }

    public function getJSDependencies() {
        return [];
    }

    public function getCSSDependencies() {
        return [];
    }

    public function getAdditionalHeader() {
        return '';
    }

    public function showInlineHTML() {

    }

}