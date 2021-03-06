<?php

require_once __DIR__ . '/../../frameworks/Environment.php';

interface SignupMethodStatics {

    /**
     * @return string with humanly readable name of this method
     */
    public static function getName();

    /**
     * @return string with a short description
     */
    public static function getAltText();

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
    public static function getMetaInfo();

    public static function getLogo();

    public static function getScore($stats);

    public static function getBadgeDetails($stats);
}

abstract class SignupMethod implements SignupMethodStatics {

    // =================================================================================================================
    // Abstract functions
    // to be implemented by each method
    // =================================================================================================================



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
    // available to each signup method
    // =================================================================================================================

    protected static $signupsBasepath = 'signups/';
    protected $environment;

    public function __construct() {
        $this->environment = Environment::getEnv();
    }

    /**
     * @return string containing the basic form submit parameters
     */
    protected function getFormSubmitBaseParams() {
        $environment    = Environment::getEnv();
        $waitlist_mode  = $environment->isInWaitlistMode();
        $bachelor       = $environment->getBachelor(false, true, true);
        $bachelorData   = $bachelor->getData();

        return '?fid=' . $environment->getSelectedTripId() .
            '&method=' . SignupMethods::getInstance()->getActiveMethodId() .
            (isset($bachelorData['id']) ? '&bid=' . $bachelorData['id'] : '') .
            ($waitlist_mode         ? '&waitlist'               : '');
    }

}