<?php

class AdminOverviewPage extends AdminPage {

    private $data;

    public function __construct($base) {
        parent::__construct($base);

        $fid = $this->fahrt->getID();
        $db = $this->environment->database;
        // to use in OR
        $notwaiting = ['on_waitlist' => 0,
            'AND' => [
                'transferred[!]' => null,
                'on_waitlist' => 1
            ]];
        $notwaitingQ = '(on_waitlist = 0 OR (transferred IS NOT NULL AND on_waitlist = 1))';

        $baseW = ['fahrt_id' => $fid, 'OR'=>$notwaiting, 'backstepped'=>null];

        $naechte = [
            'erste' => $db->query("SELECT date_format(anday, '%j') as anday, COUNT(anday) as anday_cnt FROM bachelor WHERE fahrt_id = ".$fid." AND ".$notwaitingQ." AND backstepped IS NULL GROUP BY anday ORDER BY anday ASC LIMIT 1")->fetchAll(),
            'zweite' => $db->query("SELECT date_format(abday, '%j') as abday, COUNT(anday) as abday_cnt FROM bachelor WHERE fahrt_id = ".$fid." AND ".$notwaitingQ." AND backstepped IS NULL GROUP BY abday ORDER BY anday ASC LIMIT 1")->fetchAll()
        ];

        $this->data = [
            'mitfahrer' => [
                'gesamt' => $this->fahrt->getNumTakenSpots(),
                'gesamt_all' => $db->count('bachelor', [
                    'AND' => ['fahrt_id' => $fid, 'OR' => $notwaiting]]),
                'erste' => isset($naechte['erste'][0]) ? $naechte['erste'][0]['anday_cnt'] : 0,
                'zweite' => isset($naechte['zweite'][0]) ? $naechte['zweite'][0]['abday_cnt'] : 0,
                'vege' =>  $db->count('bachelor', ['AND' =>
                    ['essen' => 'VEGE', 'fahrt_id'=>$fid, 'backstepped'=>null, 'OR' => $notwaiting]]),
                'vega' => $db->count('bachelor', ['AND' =>
                    ['essen' => 'VEGA', 'fahrt_id'=>$fid, 'backstepped'=>null, 'OR' => $notwaiting]]),
                'backstepped' => $db->count('bachelor', ['AND' => ['fahrt_id' => $fid, 'backstepped[!]' => null]]),
                'treffpunkt' => $db->count('bachelor', ['AND' => array_merge($baseW, ['antyp' => 'BUSBAHN'])]),
                'erstis' => $db->count('bachelor', ['AND' => array_merge($baseW, ['studityp' => 'ERSTI'])]),
                'tuttis' => $db->count('bachelor', ['AND' => array_merge($baseW, ['studityp' => 'TUTTI'])]),
                'hoerstis' => $db->count('bachelor', ['AND' => array_merge($baseW, ['studityp' => 'HOERS'])]),
                'virgins' => $db->count('bachelor', ['AND' => array_merge($baseW, ['virgin' => 1])])
            ],
            'warte' => [
                'transferred' => $db->count('bachelor', ['AND' => ['fahrt_id' => $fid, 'transferred[!]' => null, 'on_waitlist' => 1]]),
                'waiting' => $db->count('bachelor', ['AND' => ['fahrt_id' => $fid, 'transferred' => null, 'on_waitlist' => 1]]),
                'on_waitlist' => $db->count('bachelor', ['AND' => ['fahrt_id' => $fid, 'on_waitlist' => 1]])
            ],
            'money' => [
                'in_erhalten' => $db->count('bachelor', ['AND' => ['fahrt_id' => $fid, 'paid[!]' => null]]),
                'in_ausstehend' => $db->count('bachelor', ['AND' => array_merge($baseW, ['paid' => null])]),
                'out_erhalten' => $db->count('bachelor', ['AND' => array_merge($baseW, ['repaid[!]' => null])]),
                'out_ausstehend' => $db->count('bachelor', ['AND' => array_merge($baseW, ['repaid' => null])]),
            ]
        ];
        $this->data['ratio'] = ['sum' => ($this->data['mitfahrer']['tuttis']+$this->data['mitfahrer']['erstis']+$this->data['mitfahrer']['hoerstis'])];
        if ($this->data['ratio']['sum'] > 0)
            $this->data['ratio']['ratio'] = round($this->data['mitfahrer']['erstis']/$this->data['ratio']['sum']*100, 2);
        else
            $this->data['ratio']['ratio'] = round(100, 2);
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
        return '<h1>Übersichtsseite</h1>
            <div style="float:left; margin-left: 15px">
                <h2>Mitfahrende</h2>
                <ul class="list-nodeco">
                    <li>Gesamt: '.$this->data['mitfahrer']['gesamt'].' ('.$this->data['mitfahrer']['gesamt_all'].')</li>
                    <ul>
                        <li>Erste Nacht: '.$this->data['mitfahrer']['erste'].'</li>
                        <li>Letzte Nacht: '.$this->data['mitfahrer']['zweite'].'</li>
                        <li>Nicht-Allesesser: '.$this->data['mitfahrer']['vege'].' (vegetarisch), '.$this->data['mitfahrer']['vega'].' (vegan)</li>
                        <li>Zurückgetreten: '.$this->data['mitfahrer']['backstepped'].'</li>
                        <li>Personen am Treffpunkt: '.$this->data['mitfahrer']['treffpunkt'].'</li>
                    </ul>
                    <li>Warteliste:</li>
                    <ul>
                        <li>Noch wartend: '.$this->data['warte']['waiting'].'</li>
                        <li>Übertragen: '.$this->data['warte']['transferred'].'</li>
                        <li>Gesamt: '.$this->data['warte']['on_waitlist'].'</li>
                    </ul>
                    <li>Verteilung:</li>
                    <ul>
                        <li>Jungfrauen: '.$this->data['mitfahrer']['virgins'].'</li>
                        <li>Erstis: '.$this->data['mitfahrer']['erstis'].'</li>
                        <li>Hörstis: '.$this->data['mitfahrer']['hoerstis'].'</li>
                        <li>Tuttis:  '.$this->data['mitfahrer']['tuttis'].'</li>
                        <li>= Anteil Erstis: '.$this->data['ratio']['ratio'].'%</li>
                    </ul>
                </ul>
            </div>
            <div style="float:left; margin-left: 15px"><h2>Zahlungen</h2>
                <ul>
                    <li>Zahlungen</li>
                    <ul>
                        <li>Erhalten: '.$this->data['money']['in_erhalten'].'</li>
                        <li>Ausstehende Zahlungen: '.$this->data['money']['in_ausstehend'].'</li>
                        <li>Ausgezahlt: '.$this->data['money']['out_erhalten'].'</li>
                        <li>Ausstehende Rückzahlungen: '.$this->data['money']['out_ausstehend'].'</li>
                    </ul>
                </ul>
            </div>
            <p style="clear:both"></p>';
    }

    public function getAjax() {
        return '';
    }
}

/* Vorlage:
 *
 *
 *
Gesamt	77	80	Personen am Treffpunkt	59
erste Nacht	75
zweite Nacht	75
Vegetarier	4			Arbeitsaufwand	22
					+ nebenbei
Zahlungen erhalten	77
Zahlungen ausstehend	0

Einnahmen (ist)	4.620,00 €
Einnahmen (soll)	4.620,00 €	0 €

voraussichtliche Ausgaben
Gesamt	3.925,66 €
Unterkunft (lt. Vertrag)	2.095,00 €	1990,25 €
Unterkunft (theoretisch)	3.226,30 €	3064,985 €
Verpflegung	389,36 €
Fahrtkosten (nur Bus)	205,20 €

Ausgaben		 	effektiv pro Person:	22,41 €
Einkäufe
Unterkunft
Rücküberweisungen (Storno)	0,00 €		Ausstehende Überweisungen:
	0 €		4620 €	zzgl. Förderung	2.200,00 €

			6820 €
			161,31 €	pro Person

				Frühabreiser Rückzahlungszuschlag	19,70 €

		Ratio:
Erstis	51	66,23%
Hörstis	26	33,77%

Abmeldungen vorher	11
 */