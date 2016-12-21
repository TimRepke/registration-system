<?php
require_once __DIR__ . '/Environment.php';

class Bachelor {
    const SAVE_SUCCESS = 0;
    const SAVE_ERROR_FULL = 1;
    const SAVE_ERROR_DUPLICATE = 2;
    const SAVE_ERROR_CLOSED = 3;
    const SAVE_ERROR_EXCEPTION = 4;
    const SAVE_ERROR_MISSING_RIGHTS = 5;
    const SAVE_ERROR_INVALID = 6;

    /** @var  Environment */
    protected $environment;

    protected $data = [];
    /** @var  bool true iff this is a new bachelor, not yet in DB */
    protected $isNew;
    /** @var  Fahrt the fahrt object this bachelor belongs to */
    protected $fahrt;

    protected $validationErrors = null;

    public static $ALLOWED_FIELDS = ['bachelor_id', 'fahrt_id', 'anm_time', 'forname', 'sirname', 'mehl',
        'pseudo', 'antyp', 'abtyp', 'anday', 'abday', 'comment', 'studityp', 'paid', 'repaid', 'backstepped',
        'virgin', 'essen', 'on_waitlist', 'transferred', 'public', 'version', 'signupstats'];
    public static $NULLFIELDS = ['paid', 'repaid', 'backstepped', 'transferred', 'on_waitlist', 'signupstats'];

    /**
     * Bachelor constructor.
     * @param $fahrt Fahrt
     * @param bool|false $isNew
     */
    private function __construct($fahrt, $isNew = false) {
        $this->environment = Environment::getEnv();
        $this->isNew = $isNew;
        $this->fahrt = $fahrt;
    }

    /**
     * @param $fahrt Fahrt
     * @return Bachelor
     * @throws Exception
     */
    public static function makeEmptyBachelor($fahrt) {
        $newBachelor = new Bachelor($fahrt);
        $data = [];
        foreach (Bachelor::$ALLOWED_FIELDS as $field) {
            if (!in_array($field, Bachelor::$NULLFIELDS))
                $data[$field] = '';
        }
        $possible_dates = $fahrt->getPossibleDates();
        $data['anday'] = $possible_dates[0];
        $data['abday'] = $possible_dates[count($possible_dates) - 1];
        $data['fahrt_id'] = $fahrt->getID();

        $newBachelor->set($data);
        $newBachelor->isNew = true;

        return $newBachelor;
    }

    /**
     * @param $fahrt Fahrt
     * @param $bid
     * @return Bachelor
     * @throws Exception
     */
    public static function makeFromDB($fahrt, $bid) {
        try {
            $newBachelor = new Bachelor($fahrt);
            $data = $newBachelor->environment->database->get('bachelor',
                Bachelor::$ALLOWED_FIELDS,
                ['AND' => [
                    'fahrt_id' => $fahrt->getID(),
                    'bachelor_id' => $bid
                ]]);

            $newBachelor->set($data);
            return $newBachelor;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function makeFromForm($isNew = true, $fahrt = null, $ignoreClosed = false, $admincheck = false) {
        $tmpEnv = Environment::getEnv();
        if (empty($fahrt))
            $fahrt = $tmpEnv->getTrip();
        $errorBachelor = new Bachelor(null);

        if (is_null($fahrt))
            $errorBachelor->validationErrors = ['Ungültige Fahrt!'];
        elseif ($fahrt->getRegistrationState() == Fahrt::STATUS_IS_CLOSED and !$ignoreClosed)
            $errorBachelor->validationErrors = ['Anmeldung zur Fahrt bereits geschlossen!'];
        else {
            $newBachelor = new Bachelor($fahrt, $isNew);
            $newBachelor->populateAndValidate($admincheck);
            return $newBachelor;
        }
        return $errorBachelor;
    }

    /**
     * @param $fahrt
     * @param $barr
     * @param bool|false $isNew
     * @return Bachelor
     * @throws Exception
     */
    public static function makeBachelorFromData($fahrt, $barr, $isNew = false) {
        $newBachelor = new Bachelor($fahrt, $isNew);
        $newBachelor->set($barr);
        return $newBachelor;
    }

    public function set($data) {
        if (is_null($data) or !isset($data) or empty($data))
            throw new Exception('No data to set!');
        foreach ($data as $key => $val) {
            if (in_array($key, Bachelor::$ALLOWED_FIELDS)) {
                $this->data[$key] = $val;
            } else {
                throw new Exception('Bachelor hat kein Feld: ' . $key);
            }
        }
    }

    public function getSignupStats() {
        if (isset($this->data['signupstats']) and !is_null(json_decode($this->data['signupstats'], true))) {
            return json_decode($this->data['signupstats'], true);
        }
        return null;
    }

    public function getData() {
        return $this->data;
    }

    public function isOnWaitlist() {
        return $this->data['on_waitlist'] == 1;
    }

    public function isTransferred() {
        return $this->data['transferred'] == null;
    }

    public function isWaiting() {
        return $this->isOnWaitlist() and !$this->isTransferred();
    }

    public function isBackstepped() {
        if (isset($this->data['backstepped']) and !is_null($this->data['backstepped']))
            return true;
        return false;
    }

    /**
     * Move Bachelor from Waitlist to full registration
     *
     * @return int see SAVE_* constants
     */
    public function waitlistToRegistration() {
        if ($this->data['on_waitlist'] == 1 and
            (!isset($this->data['transferred']) or is_null($this->data['transferred']))
        ) {
            $this->data['transferred'] = time();

            $code = $this->save();

            if ($code == Bachelor::SAVE_SUCCESS) {
                $this->feedbackHelper(['lang_waittoregmail', 'lang_payinfomail']);
            }
            return $code;
        } else {
            return Bachelor::SAVE_ERROR_EXCEPTION;
        }
    }

    /**
     * Move Bachelor from Registration to Waitlist.
     *   TODO check if paid already?
     *   TODO send mail?
     *
     * @return int see SAVE_* constants
     */
    public function registrationToWaitlist() {
        $this->data['on_waitlist'] = 1;
        $this->data['transferred'] = null;
        return $this->save();
    }

    /**
     * Save this bachelor to database
     *
     * @return int see SAVE_* constants
     */
    public function save() {
        if ($this->isNew) {
            try {
                // lock database tables to prevent funny things
                $this->environment->database->exec("LOCK TABLES fahrten, bachelor WRITE");

                if (!$this->isDataValid())
                    return Bachelor::SAVE_ERROR_INVALID;

                // get status
                $state = $this->fahrt->getRegistrationState();
                $wlmode = $this->environment->isInWaitlistMode();
                $duplicate = $this->hasDuplicate();

                // registration closed already, return!
                if ($state == Fahrt::STATUS_IS_CLOSED)
                    return Bachelor::SAVE_ERROR_CLOSED;

                // duplicate detected, return!
                if ($duplicate)
                    return Bachelor::SAVE_ERROR_DUPLICATE;

                // is full, but waitlist not accepted, return!
                if (!$wlmode and $state == Fahrt::STATUS_IS_OPEN_FULL)
                    return Bachelor::SAVE_ERROR_FULL;

                $this->data['version'] = 1;
                $this->data['anm_time'] = time();
                $this->data['anday'] = date('Y-m-d', DateTime::createFromFormat('d.m.Y', $this->data['anday'])->getTimestamp());
                $this->data['abday'] = date('Y-m-d', DateTime::createFromFormat('d.m.Y', $this->data['abday'])->getTimestamp());
                $this->data['bachelor_id'] = $this->generateKey();

                if ($wlmode) {
                    $this->data['on_waitlist'] = 1;
                }

                $code = $this->environment->database->insert('bachelor', $this->data);

                if (is_null($code))
                    return Bachelor::SAVE_ERROR_EXCEPTION;

                if ($wlmode)
                    $this->feedbackHelper('lang_waitmail');
                else
                    $this->feedbackHelper(['lang_regmail', 'lang_payinfomail']);

                return Bachelor::SAVE_SUCCESS;

            } catch (Exception $e) {
                return Bachelor::SAVE_ERROR_EXCEPTION;
            } finally {
                $this->environment->database->exec("UNLOCK TABLES");
            }
        } else {
            if (!$this->environment->isAdmin())
                return Bachelor::SAVE_ERROR_MISSING_RIGHTS;

            if (!$this->isDataValid())
                return Bachelor::SAVE_ERROR_INVALID;

            $this->data['version']++;

            if (preg_match('/\d{1,2}\.\d{1,2}\.\d{4}/', $this->data['anday']))
                $this->data['anday'] = date('Y-m-d', DateTime::createFromFormat('d.m.Y', $this->data['anday'])->getTimestamp());
            if (preg_match('/\d{1,2}\.\d{1,2}\.\d{4}/', $this->data['abday']))
                $this->data['abday'] = date('Y-m-d', DateTime::createFromFormat('d.m.Y', $this->data['abday'])->getTimestamp());

            $code = $this->environment->database->update('bachelor', $this->data,
                ['AND' => ['fahrt_id' => $this->fahrt->getID(), 'bachelor_id' => $this->data['bachelor_id']]]);

            if (is_null($code))
                return Bachelor::SAVE_ERROR_EXCEPTION;
            else
                return Bachelor::SAVE_SUCCESS;
        }
    }

    private function hasDuplicate() {
        return $this->environment->database->has('bachelor', ['AND' => [
            'fahrt_id' => $this->fahrt->getID(),
            'mehl' => $this->data['mehl']]]);
    }

    /**
     * Tries to generate a unique id.
     *
     * @return string
     * @throws Exception
     */
    private function generateKey() {
        $fid = $this->fahrt->getID();
        for ($run = 0; $run < 10; $run++) {
            $bytes = openssl_random_pseudo_bytes(8);
            $hex = bin2hex($bytes);

            if ($this->environment->database->has('fahrten', ['AND' => ['fahrt_id' => $fid]]) and
                !$this->environment->database->has('bachelor', ['AND' => ['fahrt_id' => $fid, 'bachelor_id' => $hex]])
            ) {
                return $hex;
            }
        }
        throw new Exception('Too many retries finding unique random bachelor_id!');
    }

    private function feedbackHelper($mail_langs) {
        global $config_baseurl;

        if (!is_array($mail_langs)) $mail_langs = [$mail_langs];

        $fahrt_details = $this->fahrt->getFahrtDetails();
        $to = $this->data['mehl'];
        $hash = $this->data['bachelor_id'];

        foreach ($mail_langs as $mail_lang) {
            $mail = $this->environment->getLanguageString($mail_lang, [
                '{{url}}' => $config_baseurl . 'status.php?fid=' . $this->fahrt->getID() . 'hash=' . $hash,
                '{{organisator}}' => $fahrt_details['leiter'],
                '{{paydeadline}}' => $fahrt_details['paydeadline'],
                '{{payinfo}}' => $fahrt_details['payinfo'],
                '{{wikilink}}' => $fahrt_details['wikilink']]);
            $bcc = $mail_lang === 'lang_payinfomail' ? $fahrt_details['kontakt'] : NULL;
            $this->environment->sendMail($to, $mail, $fahrt_details['kontakt'], $bcc);
        }
    }

    public function isDataValid() {
        return empty($this->validationErrors);
    }

    public function getValidationErrors() {
        return $this->validationErrors;
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
    private function populateAndValidate($admincheck = false) {
        $possibleDates = $this->fahrt->getPossibleDates();
        $invalidChars = $this->environment->config['invalidChars'];
        $oconf = $this->environment->oconfig;

        $this->set(['fahrt_id' => $this->fahrt->getID(), 'version' => 1]);

        $this->validationErrors = [];
        $this->validateField('forname', $invalidChars, 'Fehlerhafter oder fehlender Vorname!');
        $this->validateField('sirname', $invalidChars, 'Fehlerhafter oder fehlender Nachname!');
        $this->validateField('pseudo', $invalidChars, 'Fehlerhafter oder fehlender Anzeigename!');
        $this->validateField('mehl', 'mail', 'Fehlerhafte oder fehlende E-Mail-Adresse!');
        $this->validateField('anday', array_slice($possibleDates, 0, -1), 'Hilfe beim Ausfüllen: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
        $this->validateField('abday', array_slice($possibleDates, 1), 'Ruth hat mitgedacht: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
        $this->validateField('antyp', $oconf['reisearten'], 'Trolle hier lang: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
        $this->validateField('abtyp', $oconf['reisearten'], 'Entwicklern Bier geben und: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
        $this->validateField('essen', $oconf['essen'], 'Hat das wirklich nicht gereicht??');
        $this->validateField('studityp', $oconf['studitypen'], 'Das bist du ganz bestimmt nicht!');
        $this->validateField('public', 'checkbox', 'Trollololol');
        $this->validateField('virgin', 'virgin', 'Bitte Altersbereich wählen!');
        $this->validateField('comment', 'comment', 'Trollololol');
        if (!$admincheck) {
            $this->validateField('captcha', 'captcha', 'Captcha falsch eingegeben.');
            if (!isset($_REQUEST['disclaimer']))
                array_push($this->validationErrors, 'Disclaimer *muss* akzeptiert werden!');
        }

        if ($this->data['anday'] == $this->data['abday'])
            array_push($this->validationErrors, 'Anreisetag = Abreisetag -> Bitte prüfen!');

        // try parsing stats
        if (isset($_REQUEST['signupstats']) and !is_null(json_decode($_REQUEST['signupstats'], true)))
            $this->data['signupstats'] = $_REQUEST['signupstats']; // TODO make checks for signupmethods?
        else
            $this->data['signupstats'] = null;

        unset($_SESSION['captcha']);
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
     * @param $errmess
     */
    private function validateField($index, $check, $errmess) {
        try {
            // check that first because if unchecked it doesnt exist
            if ($check == 'checkbox') {
                $this->set([$index => (isset($_REQUEST[$index])) ? 0 : 1]);
            } // if index is missing -> error!
            elseif (!isset($_REQUEST[$index])) {
                array_push($this->validationErrors, $errmess);
                // set it in every case so corrections are possible
                $this->set([$index => '']);
            } // index is there -> check if value is allowed
            else {
                $tmp = trim($_REQUEST[$index]);

                // do specific check if a set of variables is allowed
                if (is_array($check)) {
                    $vals = array_values($check);
                    $keys = array_keys($check);

                    if (in_array($tmp, $vals))
                        $val = array_search($tmp, $check);
                    elseif (in_array($tmp, $keys))
                        $val = $tmp;
                    else {
                        array_push($this->validationErrors, $errmess);
                        $val = $keys[0];
                    }
                    if ($index == 'anday' or $index == 'abday')
                        $val = $tmp;
                    $this->set([$index => $val]);
                } // check captcha
                elseif ($check == "captcha") {
                    if (!(isset($_SESSION['captcha']) && strtolower($tmp) == strtolower($_SESSION['captcha']))) {
                        array_push($this->validationErrors, $errmess);
                    }
                } // check mail address
                elseif ($check == "mail") {
                    if (!filter_var($tmp, FILTER_VALIDATE_EMAIL))
                        array_push($this->validationErrors, $errmess);

                    // set it in every case so corrections are possible
                    $this->set([$index => $tmp]);
                } // check comment field
                elseif ($check == "comment") {
                    $this->set([$index => htmlspecialchars($tmp, ENT_QUOTES)]);
                } // check virgin field
                elseif ($check == "virgin") {
                    // NOTE: for consistency: virgin = 0 means > 18
                    if (empty($tmp) or $tmp == 'UNSET')
                        array_push($this->validationErrors, $errmess);
                    else
                        $this->set([$index => ($tmp == 'JA') ? 0 : 1]);
                } //everything else
                else {
                    // check with regex
                    if (!(preg_match($check, $tmp) == 1))
                        array_push($this->validationErrors, $errmess);

                    // set it in every case so corrections are possible
                    $this->set([$index => $tmp]);
                }
            }
        } catch (Exception $e) {
            array_push($this->validationErrors, 'Neeee du, voll daneben!');
        }

    }

    /**
     * @param $newBachelor Bachelor
     */
    public function updateBachelor($newBachelor) {
        $nBdata = $newBachelor->getData();
        foreach ($this->data as $key => $val) {
            if (isset($nBdata[$key]) and !empty($nBdata[$key]))
                $this->data[$key] = $nBdata[$key];
        }
    }
}