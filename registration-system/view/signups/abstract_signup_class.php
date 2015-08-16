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
    abstract public function getInlineHTML();





    // =================================================================================================================
    // Shared functions
    // to be implemented by each method
    // =================================================================================================================

    /**
     * This function takes submitted form data from $_REQUEST and validates the input.
     *
     * returns assoc array looking like this:
     * [
     *   "valid" => true|false,
     *   "message" => 'a message',
     *   "errors"  => ['array of', 'errors'],
     *   "data"    => [<validated data as assoc array>]
     * ]
     *
     * @return array (see above)
     */
    private function validateSubmission() {
        global $config_studitypen, $config_essen, $config_reisearten, $index_db, $invalidCharsRegEx;
        $errors = [];
        $data   = [];

        $fid  = $_REQUEST['fid'];
        $data['fahrt_id'] = $fid;
        if(comm_isopen_fid_helper($index_db, $fid)>1){
            $errors = ["Ungültige Fahrt!"];
        } else {
            $possible_dates = comm_get_possible_dates($index_db, $fid);

            $this->validateField('forname', $invalidCharsRegEx, $data, $errors, "Fehlerhafter oder fehlender Vorname!");
            $this->validateField('sirname', $invalidCharsRegEx, $data, $errors, "Fehlerhafter oder fehlender Nachname!");
            $this->validateField('pseudo', $invalidCharsRegEx, $data, $errors, "Fehlerhafter oder fehlender Anzeigename!");
            $this->validateField('mehl', 'mail', $data, $errors, "Fehlerhafte oder fehlende E-Mail-Adresse!");
            $this->validateField('anday', array_slice($possible_dates,0, -1), $data, $errors, 'Hilfe beim Ausfüllen: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
            $this->validateField('antyp', $config_reisearten, $data, $errors, 'Trolle hier lang: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
            $this->validateField('abday', array_slice($possible_dates,1), $data, $errors, 'Ruth hat mitgedacht: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
            $this->validateField('abtyp', $config_reisearten, $data, $errors, 'Entwickler Bier geben und: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
            $this->validateField('essen', $config_essen, $data, $errors, 'Hat das wirklich nicht gereicht??'); // ggf trollable machen mit /^[a-zA-Z]{2,50}$/
            $this->validateField('studityp', $config_studitypen, $data, $errors, 'Neue Chance, diesmal FS-Ini wählen!');
            $this->validateField('public', 'public', $data, $errors, 'Trollololol');
            $this->validateField('virgin', array("Ja","Nein"), $data, $errors, 'Bitte Altersbereich wählen!');
            $this->validateField('comment', 'comment', $data, $errors, 'Trollololol');
            $this->validateField('captcha', 'captcha', $data, $errors, 'Captcha falsch eingegeben.');

            if($data['anday'] == $data['abday'])
                array_push($errors, "Anreisetag = Abreisetag -> Bitte prüfen!");
        }

        if(count($errors)>0){
            return [
                "valid"   => false,
                "errors"  => $errors,
                "message" => "Fehlerhafte Angaben!",
                "data"    => $data
            ];
        } else {
            return [
                "valid"   => true,
                "errors"  => null,
                "message" => "Angaben gültig!",
                "data"    => $data
            ];
        }
    }

    /**
     * checks for correctness of a given field ($index) by trying $check.
     * pushes $errmess into $errarr, if $check fails
     * pushes empty data on fail or correct data on success into $data
     *
     * check can be regex or array or special (public, mail, comment).
     * if array, than check only succeeds if sent data is inside that array
     *
     * @param $index
     * @param $check
     * @param $datarr
     * @param $errarr
     * @param $errmess
     */
    function validateField($index, $check, &$datarr, &$errarr, $errmess){
        $pushdat = "";
        comm_verbose(3,"checking ".$index);

        // check that first because if unchecked it doesnt exist
        if($check == "public"){
            if(isset($_REQUEST[$index])) $datarr[$index] = 0;
            else  $datarr[$index] = 1;
        }

        // if index is missing -> error!
        elseif(!isset($_REQUEST[$index])){
            array_push($errarr, $errmess);

            // set it in every case so corrections are possible
            $datarr[$index] = "";
        }

        // index is there -> check if value is allowed
        else {
            $tmp = trim($_REQUEST[$index]);

            // do specific check if a set of variables is allowed
            if(is_array($check)){
                if(!in_array($tmp,$check))
                    array_push($errarr, $errmess);
                else
                    $datarr[$index] = $tmp;
            }

            // check captcha
            elseif($check == "captcha"){
                if(isset($_SESSION['captcha']) && strtolower($tmp) == strtolower($_SESSION['captcha'])){
                    unset($_SESSION['captcha']);
                } else{
                    array_push($errarr, $errmess);
                    $datarr[$index] = "";
                }
            }

            // check mail address
            elseif($check == "mail"){
                if(!filter_var($tmp, FILTER_VALIDATE_EMAIL))
                    array_push($errarr, $errmess);

                // set it in every case so corrections are possible
                $datarr[$index] = $tmp;
            }

            // check comment field
            elseif($check == "comment"){
                $datarr[$index] = htmlspecialchars($tmp, ENT_QUOTES);
            }

            // check virgin field
            elseif($index == "virgin"){
                if($_REQUEST[$index]=="Ja") $datarr[$index] = 0; // NOTE: for consistency: virgin = 0 means > 18
                else  $datarr[$index] = 1;
            }

            //everything else
            else {
                // check with regex
                if(!(preg_match($check, $tmp)==1))
                    array_push($errarr, $errmess);

                // set it in every case so corrections are possible
                $datarr[$index] = $tmp;
            }
        }
    }
}