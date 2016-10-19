<?php

require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../lang.php';
require_once __DIR__ . '/medoo.php';
require_once __DIR__ . '/soft_protect.php';
require_once __DIR__ . '/Fahrt.php';
require_once __DIR__ . '/Bachelor.php';

class Environment {

    const LOGIN_RIGHTS_NONE = 0;
    const LOGIN_RIGHTS_ADMIN = 1;
    const LOGIN_RIGHTS_SUDO = 2;

    private static $__instance;

    public $database;
    public $config;
    public $sysconf;

    private $permission_level = Environment::LOGIN_RIGHTS_NONE;
    private $adminEnv;

    // if the context provides a specific trip or bachelor, these are set
    /** @var Fahrt */
    private $fahrt;
    /** @var Bachelor */
    private $bachelor;

    public static function getEnv($admin = false) {
        if (self::$__instance == NULL) self::$__instance = new Environment($admin);
        if (!self::$__instance->adminEnv and $admin) self::$__instance = new Environment($admin);
        return self::$__instance;
    }

    protected function __construct($admin = false) {
        global $config_db, $config_studitypen, $config_essen, $config_reisearten, $config_invalidCharsRegEx,
               $config_reisearten_o, $config_essen_o, $config_studitypen_o, $config_baseurl, $config_basepath,
               $config_mailtag, $config_impressum, $config_reisearten_destroyed, $config_databse_debug,
               $config_userfile, $config_current_fahrt_file, $config_reisearten_o_short;

        $this->adminEnv = $admin;

        $this->database = new medoo(array(
            'database_type' => $config_db["type"],
            'database_name' => $config_db["name"],
            'server' => $config_db["host"],
            'username' => $config_db["user"],
            'password' => $config_db["pass"]
        ));

        $this->config = [
            'studitypen' => $config_studitypen,
            'essen' => $config_essen,
            'reisearten' => $config_reisearten,
            'invalidChars' => $config_invalidCharsRegEx,
            'reiseartenDestroyed' => $config_reisearten_destroyed
        ];

        $this->oconfig = [
            'studitypen' => $config_studitypen_o,
            'essen' => $config_essen_o,
            'reisearten' => $config_reisearten_o,
            'reiseartenShort' => $config_reisearten_o_short
        ];

        $this->sysconf = [
            'currentTripId' => $this->readCurrentTripId(),
            'baseURL' => $config_baseurl,
            'basePath' => $config_basepath,
            'mailTag' => $config_mailtag,
            'impressum' => $config_impressum,
            'databaseDebug' => $config_databse_debug,
            'adminUsersFile' => $config_userfile,
            'currentFahrtFile' => $config_current_fahrt_file
        ];

        $this->bachelor = null;
        $this->fahrt = null;

        if ($admin) {
            self::adminCheckIfLogin();
            if (self::adminIsLoggedIn())
                $this->permission_level = self::adminGetLevel();
        }
    }

    public function __destruct() {
        if ($this->sysconf['databaseDebug']) {
            echo '<pre>';
            var_dump($this->database->log());
            echo '</pre>';
        }
    }
    // ========================================================================================================
    // ADMIN STUFF

    private static function adminCheckIfLogin() {
        if (isset($_GET['logout'])) {
            Environment::adminLogOut();
        } else if (isset($_POST['user']) and isset($_POST['password'])) {
            $user = $_POST['user'];
            $password = $_POST['password'];
            if (Environment::adminIsValidUser($user, $password))
                $_SESSION['loggedIn'] = $user;
        }
    }

    private static function adminIsValidUser($user, $password) {
        $config_admins = Environment::adminReadUsersFile();
        foreach ($config_admins as $cfg_user => $cfg_password) {
            if ($cfg_user != $user)
                continue;
            $cfg_password = $cfg_password["pw"];
            if ($cfg_password[0] == '{') {
                if (strpos($cfg_password, "{SHA254}") >= 0) {
                    $beginOfSalt = strpos($cfg_password, "$");
                    $salt = substr($cfg_password, 9, strpos($cfg_password, "$") - 9);
                    $hash = substr($cfg_password, $beginOfSalt + 1);

                    if (hash('sha256', $password . $salt) == $hash)
                        return true;
                }
            } else {
                // ONLY sha256 yet, others not implemented
            }
        }
        return false;
    }

    private static function adminReadUsersFile() {
        global $config_userfile;
        $ret = [];
        $handle = fopen($config_userfile, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $tmp = explode(" ", $line);
                if (count($tmp) >= 3) {
                    $ret[$tmp[1]] = ["pw" => $tmp[2], "sa" => $tmp[0]];
                }
            }
        }
        fclose($handle);
        return $ret;
    }

    private static function adminIsLoggedIn() {
        return isset($_SESSION['loggedIn']) and $_SESSION['loggedIn'] != '';
    }

    private static function adminGetLevel() {
        $config_admins = Environment::adminReadUsersFile();
        if (isset($_SESSION['loggedIn']) && isset($config_admins[$_SESSION['loggedIn']])) {
            if ($config_admins[$_SESSION['loggedIn']]['sa'] === 'S')
                return Environment::LOGIN_RIGHTS_SUDO;
            elseif ($config_admins[$_SESSION['loggedIn']]['sa'] === 'N')
                return Environment::LOGIN_RIGHTS_ADMIN;
        }
        return Environment::LOGIN_RIGHTS_NONE;
    }

    public function isAdmin() {
        return $this->permission_level == Environment::LOGIN_RIGHTS_ADMIN or
        $this->permission_level == Environment::LOGIN_RIGHTS_SUDO;
    }

    public function isSuperAdmin() {
        return $this->permission_level == Environment::LOGIN_RIGHTS_SUDO;
    }

    public static function adminLogOut() {
        session_destroy();
        header("location: ..");
    }


    // ===========================================================================================================
    // Some context based trip getters

    private function readCurrentTripId() {
        global $config_current_fahrt_file;
        if (file_exists($config_current_fahrt_file)) {
            $tmp = file_get_contents($config_current_fahrt_file);
            if (is_numeric($tmp))
                return $tmp;
        }
        return null;
    }

    private function isTripIdValid($fid) {
        if (empty($fid) and $fid != 0) return false;
        return $this->database->has('fahrten', ['fahrt_id' => $fid]);
    }

    public function getCurrentTripId() {
        return $this->sysconf['currentTripId'];
    }

    public function getSelectedTripId() {
        if (isset($_REQUEST['fid']))
            return (int)$_REQUEST['fid'];
        else
            return null;
    }

    public function isSelectedTripIdValid() {
        return $this->isTripIdValid($this->getSelectedTripId());
    }

    public function getTrip($fallbackCurrent = false) {
        $tripID = $this->getSelectedTripId();

        if (is_null($tripID) and !$fallbackCurrent)
            return null;

        if (!is_null($this->fahrt))
            return $this->fahrt;

        if (is_null($tripID) and $fallbackCurrent)
            $tripID = $this->getCurrentTripId();

        if (!$this->isTripIdValid($tripID))
            return null;

        $this->fahrt = new Fahrt($tripID);
        return $this->fahrt;
    }


    // ===========================================================================================================
    // Some context based bachelor getters

    public function isInWaitlistMode() {
        return isset($_REQUEST['waitlist']);
    }


    /**
     * @return bool true iff formdata is received
     */
    public function formDataReceived() {
        return isset($_REQUEST['submit']) || isset($_REQUEST['storySubmit']);
    }

    public function getSelectedBachelorId() {
        if (isset($_REQUEST['bid']))
            return $_REQUEST['bid'];
        if (isset($_REQUEST['hash']))
            return $_REQUEST['hash'];

        return null;
    }

    public function getBachelor($allowTripIdFallback = false, $fallbackNew = false, $declareNew = false) {
        if ($this->formDataReceived())
            return Bachelor::makeFromForm($declareNew);
        $bid = $this->getSelectedBachelorId();
        $trip = $this->getTrip($allowTripIdFallback);
        if (!is_null($bid) and !is_null($trip))
            return Bachelor::makeFromDB($trip, $bid);
        if ($fallbackNew)
            return Bachelor::makeEmptyBachelor($trip);
        return null;
    }


    // ==========================================================================================================
    // SOME OTHER STUFF

    /**
     * sends mail
     *
     * first line of $cont is used as subject iff terminated by double backslash (\\)
     * note that there should be no "\\" anywhere else in the string!!!
     *
     * returns true/false depending on success
     */
    public function sendMail($addr, $cont, $from = null, $bcc = null) {
        $subj = "Wichtige Information";
        $mess = $cont;
        $tmp = explode("\\\\", $cont);
        if (count($tmp) > 1) {
            $subj = $tmp[0];
            $mess = $tmp[1];
        }
        $subj = $this->sysconf['mailTag'] . $subj;

        $headers = 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $from . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        if (!is_null($bcc)) $headers .= "\r\nBcc: " . $bcc;

        return mail($addr, $subj, $mess, $headers);
    }

    public function getLanguageString($lang, $replace) {
        global $$lang;
        return str_replace(array_keys($replace), array_values($replace), $$lang);
    }
}
