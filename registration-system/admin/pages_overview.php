<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 9/17/14
 * Time: 8:04 PM
 */

global $text, $headers, $admin_db, $config_current_fahrt_id, $ajax, $config_reisearten, $config_reisearten_o, $config_studitypen_o, $config_admin_verbose_level, $config_verbose_level, $config_essen;
$config_admin_verbose_level = 0;
$config_verbose_level = 0;
$text .= "<h1>Übersichtsseite</h1>";


$mitfahrer['gesam'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id]]);
$mitfahrer['gesaa'] = $admin_db->count("bachelor", ["fahrt_id"    => $config_current_fahrt_id]);

$mitfahrer['erste'] = $admin_db->query("SELECT date_format(anday, '%j') as anday, COUNT(anday) as anday_cnt FROM bachelor WHERE fahrt_id=".$config_current_fahrt_id." GROUP BY anday ORDER BY anday ASC LIMIT 1")->fetchAll();
$mitfahrer['zweit'] = $admin_db->query("SELECT date_format(abday, '%j') as abday, COUNT(abday) as abday_cnt FROM bachelor WHERE fahrt_id=".$config_current_fahrt_id." GROUP BY abday ORDER BY abday DESC LIMIT 1")->fetchAll();
$mitfahrer['erste'] = isset($mitfahrer['erste'][0]) ? $mitfahrer['erste'][0]['anday_cnt'] : 0;
$mitfahrer['zweit'] = isset($mitfahrer['zweit'][0]) ? $mitfahrer['zweit'][0]['abday_cnt'] : 0;;

$mitfahrer['veget'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "essen[!]" => $config_essen[0]]]);
$mitfahrer['backs'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped[!]" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id]]);
$mitfahrer['treff'] = $admin_db->count("bachelor", ["AND" =>
                                        ["antyp"       => $config_reisearten_o["BUSBAHN"],
                                         "backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id]]);
$mitfahrer['ersti'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "studityp" => $config_studitypen_o["ERSTI"]]]);
$mitfahrer['tutti'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "studityp" => $config_studitypen_o["TUTTI"]]]);
$mitfahrer['hoers'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id],
                                         "LIKE"=>["studityp" => $config_studitypen_o["HOERS"]]]);
$mitfahrer['virgi'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "virgin"      => 1]]);
$warte['ntran'] = $admin_db->count("waitlist", ["AND"=>
                                        ["transferred" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id]]);
$warte['gesam'] = $admin_db->count("waitlist", ["fahrt_id"    => $config_current_fahrt_id]);
$money['erhalten'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "paid[!]"     => NULL]]);
$money['aus'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "paid"     => NULL]]);
$money['gezahlt'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "repaid[!]"     => NULL]]);
$money['ausstehend'] = $admin_db->count("bachelor", ["AND"=>
                                        ["backstepped" => NULL,
                                         "fahrt_id"    => $config_current_fahrt_id,
                                         "repaid"     => NULL]]);

$text .= "<div style='float:left; margin-left: 15px'><h2>Mitfahrer</h2>
        <ul class='list-nodeco'>
            <li>Gesamt: ".$mitfahrer['gesam']." (".$mitfahrer['gesaa'].")</li>
            <ul>
                <li>Erste Nacht: ".$mitfahrer['erste']."</li>
                <li>Letzte Nacht: ".$mitfahrer['zweit']."</li>
                <li>Nicht-Allesesser: ".$mitfahrer['veget']."</li>
                <li>Zurückgetreten: ".$mitfahrer['backs']."</li>
                <li>Personen am Treffpunkt: ".$mitfahrer['treff']."</li>
            </ul>
            <li>Warteliste:</li>
            <ul>
                <li>Noch wartend: ".$warte['ntran']."</li>
                <li>Übertragen: ".($warte['gesam']-$warte['ntran'])."</li>
                <li>Gesamt: ".$warte['gesam']."</li>
            </ul>
            <li>Verteilung</li>
            <ul>
                <li>Jungfrauen: ".$mitfahrer['virgi']."</li>
                <li>Erstis: ".$mitfahrer['ersti']."</li>
                <li>Hörstis: ".$mitfahrer['hoers']."</li>
                <li>Tutti:  ".$mitfahrer['tutti']."</li>
                <li>= Anteil Erstis: ".round(($mitfahrer['ersti']/((($mitfahrer['ersti']+$mitfahrer['hoers']+$mitfahrer['tutti']) <=0) ? 1 : ($mitfahrer['ersti']+$mitfahrer['hoers']+$mitfahrer['tutti'])))*100,2)."%</li>
            </ul>
        </ul></div>";

$text .= "<div style='float:left; margin-left: 15px'><h2>Zahlungen</h2>
        <ul>
            <li>Zahlungen</li>
            <ul>
                <li>Erhalten:".$money['erhalten']."</li>
                <li>Ausstehende Zahlungen:".$money['aus']."</li>
                <li>Ausgezahlt: ".$money['gezahlt']."</li>
                <li>Ausstehende Rückzahlungen: ".$money['ausstehend']."</li>
            </ul>
            <!--li>Einnahmen</li>
            <ul>
                <li>Soll:</li>
                <li>Ist:</li>
                <li>Differenz:</li>
            </ul-->
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