<?php
require_once 'Environment.php';
require_once 'commons.php';

class Bachelor {
    const SAVE_SUCCESS = 0;
    const SAVE_ERROR_FULL = 1;
    const SAVE_ERROR_DUPLICATE = 2;
    const SAVE_ERROR_CLOSED = 3;
    const SAVE_ERROR_EXCEPTION = 4;
    const SAVE_ERROR_MISSING_RIGHTS = 5;

    /** @var  Environment */
    protected $environment;

    protected $data = [];
    /** @var  bool true iff this is a new bachelor, not yet in DB */
    protected $isNew;
    /** @var  Fahrt the fahrt object this bachelor belongs to */
    protected $fahrt;

    const ALLOWED_FIELDS = ['bachelor_id', 'fahrt_id', 'anm_time', 'forname', 'sirname', 'mehl',
        'pseudo', 'antyp', 'abtyp', 'anday', 'abday', 'comment', 'studityp', 'paid', 'repaid', 'backstepped',
        'virgin', 'essen', 'on_waitlist', 'transferred', 'public', 'version'];
    const NULLFIELDS = ['paid', 'repaid', 'backstepped', 'transferred', 'on_waitlist'];

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
    public static function makeNewBachelor($fahrt) {
        $newBachelor = new Bachelor($fahrt);
        $data = [];
        foreach (Bachelor::ALLOWED_FIELDS as $field) {
            if (!in_array($field, Bachelor::NULLFIELDS))
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
        $newBachelor = new Bachelor($fahrt);
        $data = $newBachelor->environment->database->get('bachelor',
            Bachelor::ALLOWED_FIELDS,
            ['AND' => [
                'fahrt_id' => $fahrt->getID(),
                'bachelor_id' => $bid
            ]]);
        $newBachelor->set($data);
        return $newBachelor;
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
        foreach ($data as $key => $val) {
            if (in_array($key, Bachelor::ALLOWED_FIELDS)) {
                $this->data[$key] = $val;
            } else {
                throw new Exception('Bachelor hat kein Feld: ' . $key);
            }
        }
    }

    public function get() {
        return $this->data;
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

            $this->data['version']++;
            $code = $this->environment->database->update('bachelor', $this->data, ['AND' => [
                'fahrt_id' => $this->fahrt->getID(), 'bachelor_id' => $this->data['bachelor_id']]]);

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
            comm_verbose(3, "generated hex for test: " . $hex);

            if ($this->environment->database->has('fahrten', ['AND' => ['fahrt_id' => $fid]]) and
                !$this->environment->database->has('bachelor', ['AND' => ['fahrt_id' => $fid, 'bachelor_id' => $hex]])
            ) {
                comm_verbose(2, "generated hex: " . $hex);
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
            $mail = comm_get_lang($mail_lang, [
                "{{url}}" => $config_baseurl . "status.php?hash=" . $hash,
                "{{organisator}}" => $fahrt_details['leiter'],
                "{{paydeadline}}" => $fahrt_details['paydeadline'],
                "{{payinfo}}" => $fahrt_details['payinfo'],
                "{{wikilink}}" => $fahrt_details['wikilink']]);
            $bcc = $mail_lang === "lang_payinfomail" ? $fahrt_details['kontakt'] : NULL;
            comm_send_mail($to, $mail, $fahrt_details['kontakt'], $bcc);
        }
    }
}