<?php

class FormSignupMethod extends SignupMethod {

    public static function getName() {
        return "Langweiliges Formular";
    }

    public static function getAltText() {
        return "Seite zu bunt? Kein JavaScript? Oder einfach nur Langweiler?";
    }

    public static function getMetaInfo() {
        return [
            "version"      => '1.1',
            "date"         => '20.09.2014',
            "contributors" => ['Tim Repke <tim@repke.eu>']
        ];
    }

    public function getJSDependencies() {
        return [];
    }

    public function getCSSDependencies() {
        return ['style.css'];
    }

    public function getAdditionalHeader() {
        return '';
    }

    public function getInlineHTML() {

    }
}