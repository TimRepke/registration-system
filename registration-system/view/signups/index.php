<?php

require_once "abstract_signup_class.php";

class SignupMethods {

    private static $__instance = NULL;
    private $signup_methods = [];
    private $fallback_method = 'form';

    public static function getInstance() {
        if(self::$__instance == NULL) self::$__instance = new SignupMethods();
        return self::$__instance;
    }

    protected function __construct() {
        $this->signup_methods = $this->loadSignupMethods();
    }

    public function getSignupMethods() {
        return $this->signup_methods;
    }

    /**
     * @return array of assoc_arrays with name, desc and contribs
     */
    public function getSignupMethodsBaseInfo() {
        $tmp = [];
        foreach($this->signup_methods as $method) {
            array_push($tmp, [
                "id"           => $method["id"],
                "name"         => $method["class"]::getName(),
                "description"  => $method["class"]::getAltText(),
                "meta"         => $method["class"]::getMetaInfo()
            ]);
        }
        return $tmp;
    }

    /**
     * @return bool returns true iff a method is set and exists
     */
    public function signupMethodExists() {
        try {
            $this->getActiveMethod();
            return true;
        } catch (Exception $e) {
            //echo $e;
            return false;
        }
    }

    public function getFallbackMethod() {
        $method = $this->getMethodObj($this->fallback_method);
        return new $method['class']();
    }

    /**
     * @return class (instantiated) of the active signup method
     * @throws ErrorException when $_GET["method"] is missing or not available in the list
     */
    public function getActiveMethod() {
        $method = $this->getActiveMethodObj();
        return new $method['class']();
    }

    /**
     * @return id of the class (and with that the folder)
     * @throws ErrorException when $_GET["method"] is missing or not available in the list
     */
    public function getActiveMethodId() {
        $method = $this->getActiveMethodObj();
        return $method['id'];
    }

    /**
     * @return array
     * @throws ErrorException when $_GET["method"] is missing or not available in the list
     */
    private function getActiveMethodObj() {
        if(!isset($_REQUEST["method"])) throw new ErrorException("No signup-method selected!");
        return $this->getMethodObj($_REQUEST["method"]);
    }

    private function getMethodObj($mode) {
        if(Environment::getEnv()->formDataReceived()) $mode = $this->fallback_method;
        if(!isset($this->signup_methods[$mode])) throw new ErrorException("Signup-method does not exist!");
        return [ 'id' => $mode, 'class' => $this->signup_methods[$mode]['class']];
    }

    private function getMethodDirs() {
        return glob(__DIR__ . '/*' , GLOB_ONLYDIR);
    }

    private function loadSignupMethod($folder_name) {
        $tmp_method_folder = basename($folder_name);
        $tmp_file_name     = __DIR__ . '/' . $tmp_method_folder . '/index.php';

        try {
            if (file_exists($tmp_file_name)) {
                require_once $tmp_file_name;

                $tmp_class_name = ucfirst($tmp_method_folder . 'SignupMethod');
                if (class_exists($tmp_class_name)) {
                    return [
                        'id'        => $tmp_method_folder,
                        'class'     => $tmp_class_name,
                        'classFile' => $tmp_file_name
                    ];
                }
            }
        } catch (Exception $e) { /* do nothing */  }

        return false;
    }

    private function loadSignupMethods() {
        $tmp_method_dirs = $this->getMethodDirs();
        $tmp_methods     = [];

        foreach ($tmp_method_dirs as $method_dir) {
            $tmp_method = $this->loadSignupMethod($method_dir);
            if ($tmp_method) $tmp_methods[basename($method_dir)] =  $tmp_method;
        }

        return $tmp_methods;
    }


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
    public function validateSubmission() {
        $environment = Environment::getEnv();
        $errors = [];
        $data   = [];

        $fid  = $environment->getSelectedTripId();
        $data['fahrt_id'] = $fid;
        if($environment->getRegistrationState() > 1){
            $errors = ["Ungültige Fahrt!"];
        } else {
            $possible_dates = comm_get_possible_dates($environment->database, $fid);

            $this->validateField('forname', $environment->config['invalidChars'], $data, $errors, "Fehlerhafter oder fehlender Vorname!");
            $this->validateField('sirname', $environment->config['invalidChars'], $data, $errors, "Fehlerhafter oder fehlender Nachname!");
            $this->validateField('pseudo',  $environment->config['invalidChars'], $data, $errors, "Fehlerhafter oder fehlender Anzeigename!");
            $this->validateField('mehl', 'mail', $data, $errors, "Fehlerhafte oder fehlende E-Mail-Adresse!");
            $this->validateField('anday',    array_slice($possible_dates,0, -1), $data, $errors, 'Hilfe beim Ausfüllen: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
            $this->validateField('antyp',    $environment->oconfig['reisearten'], $data, $errors, 'Trolle hier lang: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
            $this->validateField('abday',    array_slice($possible_dates,1),     $data, $errors, 'Ruth hat mitgedacht: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
            $this->validateField('abtyp',    $environment->oconfig['reisearten'], $data, $errors, 'Entwickler Bier geben und: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
            $this->validateField('essen',    $environment->oconfig['essen'],      $data, $errors, 'Hat das wirklich nicht gereicht??'); // ggf trollable machen mit /^[a-zA-Z]{2,50}$/
            $this->validateField('studityp', $environment->oconfig['studitypen'], $data, $errors, 'Neue Chance, diesmal FS-Ini wählen!');
            $this->validateField('public',  'public',          $data, $errors, 'Trollololol');
            $this->validateField('virgin',  'virgin',          $data, $errors, 'Bitte Altersbereich wählen!');
            $this->validateField('comment', 'comment',         $data, $errors, 'Trollololol');
            $this->validateField('captcha', 'captcha',         $data, $errors, 'Captcha falsch eingegeben.');

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
                $vals = array_values($check);
                $keys = array_keys($check);

                // on error
                if (!in_array($tmp, $vals) && !in_array($tmp, $keys)) array_push($errarr, $errmess);

                // on success
                // take val directly
                if (in_array($tmp,$vals)) $datarr[$index] = $tmp;
                // or translate
                else $datarr[$index] = $check[$tmp];
            }

            // check captcha
            elseif($check == "captcha"){
                unset($_SESSION['captcha']);
                if(!(isset($_SESSION['captcha']) && strtolower($tmp) == strtolower($_SESSION['captcha']))) {
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
