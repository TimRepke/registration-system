<?php

class AdminAdminPage extends AdminPage {

    public function __construct($base) {
        parent::__construct($base);

        if (isset($_REQUEST['us_submit'])) {
            if (!isset($_REQUEST['users'])) {
                $this->message_err = 'Something went wrong with your user file submission!';
            } else {
                $formatCorrect = true;
                foreach(explode(PHP_EOL, $_REQUEST['users']) as $line){
                    if (!preg_match('/^(S|N) \w+ ({SHA-256})[a-z0-9]+\$[a-z0-9]+ .*$/m', $line)) {
                        $formatCorrect = false;
                        break;
                    }
                }
                if ($formatCorrect) {
                    $saveResult = file_put_contents($this->environment->sysconf['adminUsersFile'], $_REQUEST['users']);
                    if (empty($saveResult)) {
                        $this->message_err = 'Tried writing to ' . $this->environment->sysconf['adminUsersFile'] . '<br />
                            Seems there was an error, please check if file hase chmod rw-rw-rw-!';
                    } else {
                        $this->message_succ = 'Wrote users file ' . $this->environment->sysconf['adminUsersFile'] . ' with exit code ' . $saveResult;
                    }
                } else {
                    $this->message_err = 'users.txt format wrong!';
                }
            }
        }

        if (isset($_REQUEST['nf_submit'])) {
            if (isset($_REQUEST['resubmit'])) {
                $this->message_err = 'Nix mit neue Fahrt!';
            } else {
                try {
                    $newFahrt = Fahrt::getEmptyFahrt();
                    $saveResult = $newFahrt->save();
                    if (is_null($saveResult)) {
                        throw new Exception('Error beim DB insert!');
                    } else {
                        $this->message_succ = 'Neue Fahrt angelegt mit der ID: ' . $saveResult;
                    }
                } catch (Exception $e) {
                    $this->message_err = 'Fehler aufgetreten beim Fahrt anlegen!<br />' . $e->getMessage();
                }
            }
        }

        if (isset($_REQUEST['id_submit'])) {
            if (!isset($_REQUEST['fid'])) {
                $this->message_err = 'Nix mit Fahrt ID ändern!';
            } else {
                $newFid = trim($_REQUEST['fid']);
                if (is_numeric($newFid) and $this->environment->database->has('fahrten', ['fahrt_id' => (int)$newFid])) {
                    $saveResult = file_put_contents($this->environment->sysconf['currentFahrtFile'], $newFid);
                    if (empty($saveResult)) {
                        $this->message_err = 'Fehler beim Fahrt ID speichern in ' . $this->environment->sysconf['currentFahrtFile'] . '<br />
                            check if file has chmod rw-rw-rw-!';
                    } else {
                        $this->message_succ = 'Erfolgreich Fahrt ID ' . $newFid . ' in ' .
                            $this->environment->sysconf['currentFahrtFile'] . ' gespeichert.<br />
                            Save code: ' . $saveResult;
                    }
                } else {
                    $this->message_err = 'Fahrt ID nicht vorhanden oder kein int gegeben! ('.$newFid.')';
                }
            }
        }
    }

    private function getUserFileContent() {
        if (file_exists($this->environment->sysconf['adminUsersFile']))
            return file_get_contents($this->environment->sysconf['adminUsersFile']);
        return '';
    }

    private function getUserFileEditSection() {
        return '<h2>Nutzer bearbeiten</h2>
                ACHTUNG: Tippfehler können Systemfunktionalität beeinträchtigen! <i>Format: {N|S}⎵USERNAME⎵PASSWORD⎵RANDOMSTUFF</i><br />
                <i>N = Organisator der Fahrt; S = Superadmin (sieht auch diese Seite)</i><br />
                line regex: "^(S|N) \w+ ({SHA-256})[a-z0-9]+\$[a-z0-9]+ .*$/m" <br />
                Captain Obvious: "Nutzername darf kein Leerzeichen enthalten!"<br />
                <a href="../passwd/index.html">Passwort-gen tool</a> (an Organisator weiterleiten, der schickt dann Passwort hash zurück)<br />
                <form method="POST">
                    <textarea rows="8" cols="130" name="users" id="users">' . $this->getUserFileContent() . '</textarea><br />
                    <input type="submit" name="us_submit" id="us_submit" value="us_submit" />
                </form>';
    }

    private function getNeueFahrtSection() {
        return '<h2>Neue Fahrt anlegen</h2>
                <form method="POST" target="?resubmit=not">
                    <input type="submit" name="nf_submit" value="nf_submit" id="nf_submit" />
                </form> ';
    }

    private function getCurrentFahrtSelectorSection() {
        $fahrten = Fahrt::getAlleFahrten();
        $current = $this->fahrt->getID();
        $ret = '<h2>Aktuelle Fahrt ID</h2>
                Wählt die Fahrt, die über das Adminpanel bearbeitet/verwaltet werden kann.<br />
                <form method="POST" >
                    <label>Neue ID wählen (aktiv: ' . $current . '):</label>
                    <select name="fid" id="fid">';
        foreach ($fahrten as $fahrt) {
            $ret .= '<option value="' . $fahrt->getID() . '">' . $fahrt->getID() . ' - ' . $fahrt->getFahrtDetails()['titel'] . '</option>';
        }
        $ret .= '</select>
                    <input type="submit" name="id_submit" value="id_submit" id="id_submit" />
                </form>';
        return $ret;
    }

    public function getHeaders() {
        return '';
    }

    public function getHeader() {
        return '';
    }

    public function getFooter() {
        return '';
    }

    public function getText() {
        return '<h1>SuperAdmin Panel</h1>' .
        $this->getMessage() .
        $this->getUserFileEditSection() .
        $this->getNeueFahrtSection() .
        $this->getCurrentFahrtSelectorSection();
    }

    public function getAjax() {
        return '';
    }
}
