<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/view/default_index.php';
require_once __DIR__.'/frameworks/Fahrt.php';
require_once __DIR__.'/frameworks/Bachelor.php';

class Status extends DefaultIndex {

    private $hash;
    private $bachelor;
    private $fahrt;

    public function __construct() {
        parent::__construct();
        $this->hash = (isset($_REQUEST['hash'])) ? $_REQUEST['hash'] : null;

        $tripID = $this->environment->getSelectedTripId();
        if (is_null($tripID))
            $tripID = $this->environment->getCurrentTripId();

        $this->fahrt = new Fahrt($tripID);
        $this->bachelor = Bachelor::makeFromDB($this->fahrt, $this->hash);
    }

    private function assertDataAvailable() {
        if (is_null($this->fahrt) or is_null($this->bachelor)) {
            echo '<h2>Fehler</h2> 
                  Es wurde kein, oder nur ein ungültiger Hash angegeben. <br />
                  Auf jeden Fall konnten keine Daten gefunden werden!';
            return false;
        }
        return true;
    }

    private function displayWarningIfNeeded() {
        $msgs = [];
        if ($this->bachelor->isBackstepped())
            array_push($msgs, 'Achtung, die Anmeldung wurde ungültig gemacht!<br />
                              Sofern keine weiteren Auskünfte folgen, kannst du leider NICHT mitfahren...');

        if ($this->bachelor->isWaiting())
            array_push($msgs, 'Achtung, dies ist nur ein Eintrag auf der Warteliste!<br />
                               Sofern keine weiteren Auskünfte folgen, kannst du leider NICHT mitfahren...');

        foreach ($msgs as $msg) {
            echo '<div style="color: red; font-weight: bold; font-size: 14pt;margin-bottom: 1em;">' . $msg . '</div>';
        }
    }

    private function getTransformedData() {
        $data = $this->bachelor->getData();

        return [
            'Anmelde ID' => $data['bachelor_id'],
            'Anmeldetag' => date('d.m.Y', $data['anm_time']),
            'Vor-/Nachname' => $data['forname'] . ' ' . $data['sirname'] . (strlen($data['pseudo']) > 0 ? ' (' . $data['pseudo'] . ')' : ''),
            'eMail-Adresse' => $data['mehl'],
            'Anreisetag &amp; Art' => $this->mysql2german($data['anday']) . ' (' . $this->translateOption('reisearten', $data['antyp']) . ')',
            'Abreisetag &amp; Art' => $this->mysql2german($data['abday']) . ' (' . $this->translateOption('reisearten', $data['abtyp']) . ')',
            'Essenswunsch' => $this->translateOption('essen', $data['essen']),
            'Zahlung erhalten' => ((is_null($data['paid'])) ? 'nein' : date('d.m.Y', $data['paid'])),
            'Rückzahlung gesendet' => ((is_null($data['repaid'])) ? 'nein' : date('d.m.Y', $data['repaid'])),
            //'Zurückgetreten' => (($data['backstepped']==1) ? 'ja' : 'nein'),
            'Kommentar' => $data['comment']
        ];
    }

    private function displayTable() {
        $infolist = $this->getTransformedData();

        echo '<div class="fahrttable">';

        foreach ($infolist as $key => $value) {
            echo '<div>';
            echo '<div style="display: table-cell; font-weight: bold; padding: 3px 40px 3px 0">' . $key . '</div>';
            echo '<div style="display: table-cell">' . $value . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private function displayMailLink() {
        $fdata = $this->fahrt->getFahrtDetails();
        $bdata = $this->bachelor->getData();

        $subject = $this->environment->sysconf['mailTag'] . ' Änderung zu ' .
            $bdata['forname'] . ' ' . $bdata['sirname'] . ' (' . $bdata['pseudo'] . ')';

        echo 'Fehler entdeckt? Dann schreib eine Mail an ' . $fdata['leiter'] . ' (' . $fdata['kontakt'] . '): ';
        echo '<a style="float:none;font-weight:bold"
                 href="mailto:' . $fdata['kontakt'] . '?subject=' . str_replace(" ", "%20", $subject) . '">Änderung melden</a>';
    }

    private function displayFahrtInfo() {
        $fdata = $this->fahrt->getFahrtDetails();
        echo 'Angaben für ' . $fdata['titel'] .
             ' ('.$this->mysql2german($fdata['von']).' - '.$this->mysql2german($fdata['bis']).')';
    }

    protected function echoHeaders() {
        echo '<link rel="stylesheet" href="view/style.css" />';
    }

    protected function echoContent() {
        echo '<div class="fahrt" style="background: #f9f9f9"><div class="fahrttitle">Anmeldedaten</div>';
        if ($this->assertDataAvailable()) {
            $this->displayFahrtInfo();
            $this->displayWarningIfNeeded();
            $this->displayTable();
            $this->displayMailLink();
        }
        echo '</div>';
    }
}

(new Status())->render();