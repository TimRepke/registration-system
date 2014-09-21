<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 9/17/14
 * Time: 8:04 PM
 */

global $text, $headers, $admin_db, $config_current_fahrt_id, $ajax, $config_reisearten, $config_admin_verbose_level, $config_verbose_level, $config_essen;
$config_admin_verbose_level = 4;
$config_verbose_level = 4;
$text .= "<h1>Übersichtsseite</h1>";


$mitfahrer['gesam'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id]]);
$mitfahrer['gesaa'] = $admin_db->count("bachelor", ["fahrt_id"    => $config_current_fahrt_id]);

$antag = $admin_db->query("SELECT date_format(von, '%j') as von FROM fahrten WHERE fahrt_id=$config_current_fahrt_id")->fetchAll()[0]['von'];
$abtag = date('z', DateTime::createFromFormat('Y-m-d',$admin_db->get("fahrten","bis", ["fahrt_id"=>$config_current_fahrt_id]))->getTimestamp());
$mitfahrer['erste'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "anday"       => $antag]]);
$mitfahrer['zweit'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "abday"       => $abtag]]);
$mitfahrer['veget'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "essen[!]" => $config_essen[0]]]);
$mitfahrer['backs'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped[!]" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id]]);
$mitfahrer['treff'] = $admin_db->count("bachelor", ["AND" =>
                                        ["antyp"       => $config_reisearten[0],
                                         "backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id]]);


$text .= "<div style='float:left; margin-left: 15px'><h2>Mitfahrer</h2>
        <ul class='list-nodeco'>
            <li>Gesamt: ".$mitfahrer['gesam']." (".$mitfahrer['gesaa'].")</li>
            <ul>
                <li>Erste Nacht: ".$mitfahrer['erste']."</li>
                <li>Zweite Nacht: ".$mitfahrer['zweit']."</li>
                <li>Vegetarier: ".$mitfahrer['veget']."</li>
                <li>Zurückgetreten: ".$mitfahrer['backs']."</li>
                <li>Personen am Treffpunkt: ".$mitfahrer['treff']."</li>
            </ul>
            <li>Verteilung</li>
            <ul>
                <li>Erstis:</li>
                <li>Hörstis:</li>
                <li>Tutti: </li>
                <li>= Ratio (E vs T+H): </li>
            </ul>
        </ul></div>";

$text .= "<div style='float:left; margin-left: 15px'><h2>Zahlungen</h2>
        <ul>
            <li>Zahlungen</li>
            <ul>
                <li>Erhalten:</li>
                <li>Ausstehend:</li>
            </ul>
            <li>Einnahmen</li>
            <ul>
                <li>Soll:</li>
                <li>Ist:</li>
                <li>Differenz:</li>
            </ul>
        </ul></div>";

$text .= "<p style='clear:both'></p>";


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