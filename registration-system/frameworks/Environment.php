<?php

require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../lang.php';
require_once __DIR__.'/medoo.php';
require_once __DIR__.'/commons.php';
require_once __DIR__.'/soft_protect.php';

class Environment {

    const LOGIN_RIGHTS_NONE = 0;
    const LOGIN_RIGHTS_ADMIN = 1;
    const LOGIN_RIGHTS_SUDO = 2;

    private static $__instance;

    public $database;
    public $config;
    public $sysconf;

    private $dangling_form_data;
    private $permission_level = Environment::LOGIN_RIGHTS_NONE;

    public static function getEnv($admin = false) {
        if (self::$__instance == NULL) self::$__instance = new Environment($admin);
        return self::$__instance;
    }

    protected function __construct($admin = false) {
        global $config_db, $config_studitypen, $config_essen, $config_reisearten, $invalidCharsRegEx,
               $config_reisearten_o, $config_essen_o, $config_studitypen_o, $config_baseurl, $config_basepath,
               $config_mailtag;

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
            'invalidChars' => $invalidCharsRegEx
        ];

        $this->oconfig = [
            'studitypen' => $config_studitypen_o,
            'essen' => $config_essen_o,
            'reisearten' => $config_reisearten_o
        ];

        $this->sysconf = [
            'currentTripId' => $this->readCurrentTripId(),
            'baseURL' => $config_baseurl,
            'basePath' => $config_basepath,
            'mailTag' => $config_mailtag
        ];

        if ($admin) {
            self::adminCheckIfLogin();
            if (self::adminIsLoggedIn())
                $this->permission_level = self::adminGetLevel();
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


    /**
     * @return int|null trip selected via $_REQUEST (or null)
     */
    public function getSelectedTripId() {
        if (isset($_REQUEST['fid']))
            return (int)$_REQUEST['fid'];
        else
            return null;
    }

    public function getCurrentTripId() {
        return $this->sysconf['currentTripId'];
    }

    /**
     * @return bool true iff selected trip id is in the DB
     */
    public function isSelectedTripIdValid() {
        $fid = $this->getSelectedTripId();
        if ($fid == null) return false;
        return $this->database->has('fahrten', ['fahrt_id' => $fid]);
    }

    private function readCurrentTripId() {
        global $config_current_fahrt_file;
        if (file_exists($config_current_fahrt_file)) {
            $tmp = file_get_contents($config_current_fahrt_file);
            if (is_numeric($tmp))
                return $tmp;
        }
        return null;
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

    /**
     * @return bool true iff formdata is received
     */
    public function formDataReceived() {
        return isset($_REQUEST['submit']) || isset($_REQUEST['storySubmit']);
    }

    public function getBachelor($bid = NULL) {
        if (!is_null($this->dangling_form_data))
            return $this->dangling_form_data;
        if (is_null($bid))
            return $this->getEmptyBachelor();
        else
            return $this->getBachelorFromDB($bid);
    }

}
