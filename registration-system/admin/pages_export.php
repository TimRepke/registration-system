<?php

class AdminExportPage extends AdminPage {

    private $text = '';
    private $headers = '';
    private $header = '';
    private $footer = '';

    private static $ALLOWED_PAGES = ['refra', 'treff', 'konto', 'mord', 'room', 'unter'];

    public function __construct($base) {
        parent::__construct($base);

        try {
            if (!isset($_REQUEST['ex']) or array_search($_REQUEST['ex'], AdminExportPage::$ALLOWED_PAGES) === false)
                throw new Exception('Invalide Seite!');
            $exportFunction = 'export' . ucfirst($_REQUEST['ex']);
            $this->$exportFunction();
            $this->template = AdminPage::TEMPLATE_PRINT_FULL;
        } catch (Exception $e) {
            $this->exportDefault();
        }
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getHeader() {
        return $this->header;
    }

    public function getFooter() {
        return $this->footer;
    }

    public function getText() {
        return $this->text;
    }

    public function getAjax() {
        return '';
    }

    private function exportDefault() {
        $this->text = '
            <ul>
                <li><a href="?page=export&ex=refra" target="_blank">RefRat-Liste</a> (<a href="http://www.refrat.hu-berlin.de/finanzen/TeilnehmerInnenlisteMitEinverstaendniserklaerung.pdf">orig</a>)</li>
                <li><a href="?page=export&ex=treff" target="_blank">Treffpunktanwesenheitsliste</a></li>
                <li><a href="?page=export&ex=konto" target="_blank">Kontodatenliste</a></li>
                <li><a href="?page=export&ex=mord" target="_blank">Mörderspiel</a></li>
                <li><a href="?page=export&ex=room" target="_blank">Zimmerliste</a></li>
                <li><a href="?page=export&ex=unter" target="_blank">Anwesenheitsunterschriftsliste für Unterkunft</a></li>
                <li><a href="http://www.refrat.de/docs/finanzen/FormularFSErstattung_sepa_form_2.pdf">Erstattungsformular</a></li>
            </ul>
            NOTE: No Chrome support! Webkit/Blink haven\'t implemented support for @media print... This feature is developed to support Firefox.';
    }

    private function exportRefra() {
        $people = $this->fahrt->getBachelors(['backstepped' => false, 'waiting' => false], ['forname'=> 'ASC']);
        $data = $this->fahrt->getFahrtDetails();

        $tabdata = [];
        foreach ($people as $p) {
            array_push($tabdata, [$p['forname'] . ' ' . $p['sirname'], '', '']);
        }
        // leerfelder (just in case)
        for ($run = 0; $run < 8; $run++) {
            array_push($tabdata, ['&nbsp;', '&nbsp;', '&nbsp;']);
        }

        $tabconf = ['colwidth' => ['20%', '55%', '25%'],
            'cellheight' => '35pt'];

        $this->text = $this->tableGenerator(['Name', 'Anschrift', 'Unterschrift'], $tabdata, $tabconf);

        $this->header = '
            <h1>TeilnehmerInnenliste und Einverständniserklärung</h1>
            <h2>Fachschaftsfahrt</h2>
            Fachschaft: <u>Informatik</u><br />
            Ziel: <u>' . $data['ziel'] . '</u><br />
            Datum der Fahrt: <u>' . $this->mysql2german($data['von']) . ' - ' . $this->mysql2german($data['bis']) . '</u><br />
            Hiermit erklären wir uns mit unserer Unterschrift damit einverstanden, dass das von uns
            ausgelegten Geld für die Fachschaftsfahrt auf das Konto des/der Finanzverantwortlichen/r,
            <u>' . $data['leiter'] . '</u>, überwiesen wird.';
        $this->footer = 'Einverstädniserklärung - ' . $data['titel'];
    }

    private function exportTreff() {
        $data = $this->fahrt->getFahrtDetails();
        $people = $this->fahrt->getBachelors(['antyp' => 'BUSBAHN', 'waiting' => false, 'backstepped' => false], ['forname'=> 'ASC']);
        $ttabdata = [];
        foreach ($people as $p) {
            array_push($ttabdata, $p['forname'] . ' ' . $p['sirname']);
        }
        // leerfelder (just in case)
        for ($run = 0; $run < 8; $run++) {
            array_push($ttabdata, '&nbsp;');
        }

        $tabdata = [];
        // transpose long list to have multiple columns
        for ($run = 0; $run < count($ttabdata); $run++) {
            $c1 = (isset($ttabdata[$run]) ? $ttabdata[$run] : '&nbsp;');
            $run++;
            $c2 = (isset($ttabdata[$run]) ? $ttabdata[$run] : '&nbsp;');
            $run++;
            $c3 = (isset($ttabdata[$run]) ? $ttabdata[$run] : '&nbsp;');
            array_push($tabdata, [$c1, '&nbsp;', '', $c2, '&nbsp;', '', $c3, '&nbsp;']);
        }

        $tabconf = ['colwidth' => ['20%', '10pt', '1px; margin:0; padding:0; font-size:1pt', '20%', '10pt', '1px; margin:0; padding:0; font-size:1pt', '20%', '10pt'],
            'cellheight' => '25pt'];

        $this->text = $this->tableGenerator(['Name', 'X', '', 'Name', 'X', '', 'Name', 'X'], $tabdata, $tabconf);

        $this->header = '
            <h1>Anwesenheitsliste Treffpunkt</h1>
            Liste aller Teilnehmer, die angegeben haben, gemeinsam mit Bus/Bahn anzureisen';
        $this->footer = 'Anwesenheitsliste - ' . $data['titel'];
    }

    private function exportKonto() {
        $data = $this->fahrt->getFahrtDetails();
        $people = $this->fahrt->getBachelors(['waiting' => false, 'backstepped' => false], ['forname'=> 'ASC']);

        $tabdata = [];
        foreach ($people as $p) {
            array_push($tabdata, [$p['forname'] . ' ' . $p['sirname'], '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;']);
        }
        // leerfelder (just in case)
        for ($run = 0; $run < 8; $run++) {
            array_push($tabdata, ['&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;']);
        }

        $tabconf = ['colwidth' => ['25%', '30%', '25%', '15%', '5%'],
            'cellheight' => '35pt',
            'class' => [3 => 'graycell', 4 => 'graycell']];

        $this->text = $this->tableGenerator(['Name', 'Kontonummer/IBAN', 'Bankleitzahl/BIC', 'Betrag*', 'Erl*'], $tabdata, $tabconf);

        $this->header = '
            <h1>Kontodaten für Rücküberweisung</h1>
            Diese Liste verbleibt bei dem/der Fahrtverantwortlichen <u>' . $data['leiter'] . '</u> und wird benötigt um die Förderung und den Differenzbetrag nach der Fahrt zurück zu überweisen.<br />
            <b>Graue/mit Sternchen gekennzeichnete Fehler freilassen</b> (Trolle bekommen kein Geld!!)';
        $this->footer = 'Kontodaten (Rücküberweisung) - ' . $data['titel'];
    }

    private function exportMord() {
        $instructions = ['Gib deinem Opfer in einem Moment des Verlusts der Aufmerksamkeit einen Gegenstand um es umzubringen und melde es dem Spielleiter.',
            'Verrühre 2 Eier, 80g Mehl, 20g Zucker, 120ml Milch, 2 TL Backpulver; backe Waffeln daraus und gib eine davon deinem Opfer.',
            '"Hast du schon ein Knicklicht..?"',
            'Borge dir einen Stift von deinem Opfer und gib ihn zurück.',
            'Kannst du mir Mal bitte kurz die Taschenlampe halten?',
            'Verwechslungsgefahr: Deinem Opfer ein Rad geben zählt, einen Rat geben allerdings nicht.',
            'Leih dir das Telefon deines Opfers um "mamamameine Mutter anzurufen" und gib es zurück.',
            'Sammle die Wimper deines Opfers ein und händige sie über mit der Bitte sich etwas zu wünschen um dein Opfer zu ermorden. Wimper darf nicht ausgerissen werden. Andere pustbare Dinge auch erlaubt. ',
            'Hilf deinem Opfer bei einer Übungsaufgabe und gib den Stift zurück um es umzubringen.'];

        $data = $this->fahrt->getFahrtDetails();
        $people = $this->fahrt->getBachelors(['waiting' => false, 'backstepped' => false], ['forname'=> 'ASC']);

        $tabdata = [];
        foreach ($people as $p) {
            array_push($tabdata, [$p['forname'] . " " . $p['sirname'], '&nbsp;', '&nbsp;', '&nbsp;']);
        }

        $tabconf = ['colwidth' => ['25%', '20%', '20%', '35%'], 'cellheight' => '35pt'];

        $people = array_map(function ($piece) {
            return $piece[0];
        }, $tabdata);
        shuffle($people);

        $this->text = $this->tableGenerator(['Opfer', 'Zeitpunkt', 'Mörder', 'Tathergang'], $tabdata, $tabconf) .
            '<div class="page-break"></div>
            <div style="page-break-inside: avoid;"><h1>Cheatsheet</h1><p>' . implode('&nbsp;->&nbsp;', $people) .
            '</p></div>';

        for ($i = 1; $i <= count($people); $i++) {
            $this->text .= '<div class="killbox">
                    <span role="killer">' . $people[$i - 1] . '</span>
                    <span role="victim">' . $people[$i % count($people)] . '</span>
                    <p role="instruction">
                        <u>Mordvorschlag:</u> ' . $instructions[array_rand($instructions)] .
                ' <i>Das darf nicht erzwungen werden; keine Zeugen.</i></p>
                  </div>';
        }

        $this->header = '
            <h1>Mordaufträge und Übersicht</h1>
            Fröhliches Morden! Bitte keine tödlichen Gegenstände benutzen.';
        $this->footer = 'Mörderspiel - ' . $data['titel'];
    }

    private function exportRoom() {
        $this->text = '
            <script type="text/javascript">
                function updateRoomCnt(val) {
                    var table = document.getElementById("roomtab").getElementsByTagName("tbody")[0];
                    table.innerHTML = "";
                    for(var i = 0; i < val; i++) {
                        var row = table.insertRow(table.rows.length);
                        row.insertCell(0).style.height="30pt";
                        row.insertCell(1);
                        row.insertCell(2);
                        row.insertCell(3);
                        row.insertCell(4);
                        row.insertCell(5);
                    }
                }
            </script>
            <p class="hide-print">Anzahl Zimmer: <input type="number" id="roomcnt" value="10" onchange="updateRoomCnt(this.value)" /><br>
            Hint: Es kann sinnvoll sein ein paar mehr Spalten zu generieren als nötig.</p>';

        $tabconf = [
            'colwidth' => ['16%', '10%', '10%', '30%', '12%', '12%'],
            'cellheight' => '35pt',
            'id' => 'roomtab'
        ];

        $this->text .= $this->tableGenerator(['Haus/ Etage/ Raum', '# Betten', '# Schlüssel', 'Verantwortlich', 'Erhalten', 'Zurück'], [], $tabconf);

        $data = $this->fahrt->getFahrtDetails();

        $this->header = '
            <h1>Übersicht der Schlüssel</h1>
            Mit der Unterschrift in der Spalte "Erhalten" bestätigt die Person, angegeben in der Spalte "Verantwortlich", den/die
            Schlüssel zum entsprechenden Raum erhalten zu haben. Bei Verlust des Schlüssels oder Schäden im Zimmer wird diese
            Person Rechenschaft tragen.
            In der Spalte "Zurück" bestätigt der/die Organisator/in der Fahrt (' . $data['leiter'] . ') den Schlüssel wieder in Empfang genommen zu haben.<br>
            Diese Liste ist gültig für die Fahrt "' . $data['titel'] . '" nach "' . $data['ziel'] . '" von ' . $data['von'] . ' bis ' . $data['bis'] . '.';

        $this->footer = 'Schlüsselliste - ' . $data['titel'];
    }

    private function exportUnter() {
        $data = $this->fahrt->getFahrtDetails();
        $people = $this->fahrt->getBachelors(['backstepped' => false, 'waiting' => false], ['forname'=> 'ASC']);
        $tabdata = [];
        foreach ($people as $p) {
            array_push($tabdata, [$p['forname'] . ' ' . $p['sirname'], '&nbsp;', '&nbsp;']);
        }
        // leerfelder (just in case)
        for ($run = 0; $run < 8; $run++) {
            array_push($tabdata, ['&nbsp;', '&nbsp;', '&nbsp;']);
        }

        $tabconf = ['colwidth' => ['20%', '25%', '55%'],
            'cellheight' => '25pt'];

        $this->text = $this->tableGenerator(['Name', 'Unterschrift', '&nbsp;'], $tabdata, $tabconf);

        $this->header = '
            <h1>TeilnehmerInnenliste</h1>
            <h2>Fachschaftsfahrt</h2>
            Fachschaft: <u>Informatik</u><br />
            Datum der Fahrt: <u>' . $this->mysql2german($data['von']) . ' - ' . $this->mysql2german($data['bis']) . '</u><br />
            Verantwortlicher: <u>' . $data['leiter'] . '</u><br />
            Liste aller Teilnehmer der Fachschaftsfahrt in der Einrichtung <u>' . $data['ziel'] . '</u>';
        $this->footer = 'TeilnehmerInnenliste - ' . $data['titel'];
    }

    private function tableGenerator($headers, $data, $tabconf = []) {
        $thead = '';
        foreach ($headers as $cell => $h) {
            $thead .= '<th ' . $this->tableGeneratorCellStyle($tabconf, $cell) . '>' . $h . '</th>';
        }

        $tbody = '';
        foreach ($data as $row) {
            $tbody .= '<tr>';
            foreach ($row as $cell => $dc) {
                $tbody .= '<td' . $this->tableGeneratorCellStyle($tabconf, $cell) . '>' . $dc . '</td>';
            }
            $tbody .= '</tr>';
        }

        return '
            <table class="dattable" '. (isset($tabconf['id']) ? 'id="'.$tabconf['id'].'"' : '') . '">
                <thead>
                    <tr>' . $thead . '</tr>
                </thead>
                <tbody>' . $tbody . '</tbody>
            </table>';
    }

    private function tableGeneratorCellStyle($tabconf, $cell) {
        $ret = '';
        if (isset($tabconf['cellheight']) || isset($tabconf['colwidth']) || isset($tabconf['class'])) {
            $ret .= ' style="';
            if (isset($tabconf['cellheight']))
                $ret .= 'height:' . $tabconf['cellheight'] . ';';
            if (isset($tabconf['colwidth']) && isset($tabconf['colwidth'][$cell]))
                $ret .= 'width:' . $tabconf['colwidth'][$cell] . ';';
            $ret .= '""';

            if (isset($tabconf['class']) && isset($tabconf['class'][$cell]))
                $ret .= ' class="' . $tabconf['class'][$cell] . '"';
        }
        return $ret;
    }
}
