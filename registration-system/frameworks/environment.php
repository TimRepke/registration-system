<?php

require __DIR__.'/../config.inc.php';
require __DIR__.'/../lang.php';
require 'medoo.php';
require 'commons.php';
require 'soft_protect.php';

class Environment {

    private static $__instance;

    public $database;
    public $config;
    private $dangling_form_data;

    public static function getEnv() {
        if(self::$__instance == NULL) self::$__instance = new Environment();
        return self::$__instance;
    }

    protected function __construct() {
        global $config_db, $config_studitypen, $config_essen, $config_reisearten, $invalidCharsRegEx;

        $this->database = new medoo(array(
            'database_type' => $config_db["type"],
            'database_name' => $config_db["name"],
            'server'        => $config_db["host"],
            'username'      => $config_db["user"],
            'password'      => $config_db["pass"]
        ));

        $this->config = [
            'studitypen' => $config_studitypen,
            'essen'      => $config_essen,
            'reisearten' => $config_reisearten,
            'invalidChars' => $invalidCharsRegEx
        ];
    }

    // signup method


    // only waitlist

    /**
     * @returns TRUE iff regular registration is allowed
     */
    public function isRegistrationOpen($fid) {
        $ret = $this->getRegistrationState($fid);
        return $ret === 0;
    }

    /**
     * returns value depending on registration status
     * 0 = registration open (slots available)
     * 1 = all slots taken -> waitlist open
     * 2 = registration closed!
     */
    public function getRegistrationState($fid) {
        comm_verbose(3,"checking if fid ". $fid . " is open");
        $open = $this->database->has('fahrten', ['AND' => ['fahrt_id'=>$fid, 'regopen'=>1]]);
        if(!$open)
            return 2;

        $cnt = $this->database->count("bachelor", ["AND"=>
            ["backstepped" => NULL,
                "fahrt_id"    => $fid]]);
        $max = $this->database->get("fahrten", "max_bachelor", ["fahrt_id" => $fid]);
        $wl = $this->database->count('waitlist', ['AND' =>
            ["transferred" => NULL,
                "fahrt_id"    => $fid]]);

        comm_verbose(3,"cnt: ".$cnt.", max: ".$max.", open: ".($open ? "yes" : "no"));

        if ( $cnt < $max && $wl == 0 )
            return 0;

        return 1;
    }

    /**
     * @return trip selected via $_REQUEST (or null)
     */
    public function getSelectedTripId() {
        if(isset($_REQUEST['fid']))
            return $_REQUEST['fid'];
        else
            return null;
    }

    /**
     * @return bool true iff selected trip id is in the DB
     */
    public function isSelectedTripIdValid() {
        $valid = $this->database->has('fahrten',
            ['fahrt_id'=> $this->getSelectedTripId()]);
        if(!$valid) comm_verbose(1,"FID nicht vorhanden!");

        return $valid;
    }

    /**
     * @return bool true iff user confirmed to enter waitlist
     */
    public function isInWaitlistMode() {
        return isset($_REQUEST['waitlist']);
    }

    /**
     * Stash form data in this class (i.e. for edit)
     * @param $data
     */
    public function setDanglingFormData($data) {
        $this->dangling_form_data = $data;
    }

    public function getBachelor($bid = NULL) {
        if(!is_null($this->dangling_form_data))
            return $this->dangling_form_data;
        if(is_null($bid))
            return $this->getEmptyBachelor();
        else
            return $this->getBachelorFromDB($bid);
    }

    /**
     * Given a registration id, return all parameters from the db
     *
     * @param $bid
     * @return null or registration details
     */
    public function getBachelorFromDB($bid) {
        if(!is_null($bid) &&
            $this->database->has('bachelor', ['bachelor_id' => $bid])){

            $bachelor = $this->database->select('bachelor',
                ['forname','sirname','anday','abday','antyp','abtyp','pseudo',
                    'mehl','essen','public','virgin','studityp','comment'],
                ['bachelor_id'=>$bid]);
            $bachelor['id'] = $bid;
            return $bachelor[0];
        }
        return $this->getEmptyBachelor();
    }

    /**
     * Will return an empty registration field
     *
     * @return array
     */
    public function getEmptyBachelor() {
        $possible_dates = comm_get_possible_dates($this->database, $this->getSelectedTripId());

        return [
            'forname' => "", 'sirname' => "",
            'anday' => $possible_dates[0], 'abday' => $possible_dates[count($possible_dates)-1],
            'antyp' => "", 'abtyp' => "",
            'pseudo' => "", 'mehl' => "", 'essen' => "", 'public' => "",
            'studityp' => "", 'comment'=>""
        ];
    }
}