<?php

abstract class SignupMethod {

    // =================================================================================================================
    // Abstract functions
    // to be implemented by each method
    // =================================================================================================================

    /**
     * @return string with humanly readable name of this method
     */
    abstract public static function getName();

    /**
     * @return string with a short description
     */
    abstract public static function getAltText();

    /**
     * This method will return some meta info about that method. It should return an associative array of that form:
     *
     * [
     *   version      => '1.0',
     *   date         => '20.08.2014',
     *   contributors => ['MH <mail>', 'TR <mail>']
     * ]
     *
     * @return object containing meta info (see description)
     */
    abstract public static function getMetaInfo();

    /**
     * @return array of (relative to '/view/signups/<folder>/') scripts to include in frontend
     */
    abstract public function getJSDependencies();

    /**
     * @return array of (relative to '/view/signups/<folder>/') css files to include in frontend
     */
    abstract public function getCSSDependencies();

    /**
     * @return string to be added to to the HTML page header
     */
    abstract public function getAdditionalHeader();

    /**
     * @return string containing the necessary HTML code to bind the stuff needed into
     * the placeholder on the signup page
     */
    abstract public function showInlineHTML();





    // =================================================================================================================
    // Shared functions
    // available to each method
    // =================================================================================================================

    // ...
}