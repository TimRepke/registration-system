<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 8/26/14
 * Time: 10:05 PM
 */

global $config_studitypen, $config_reisearten, $config_essen, $admin_db, $config_current_fahrt_id, $config_admin_verbose_level, $config_verbose_level, $text, $headers, $ajax, $header, $footer;

$text .= '
<ul>
    <li><a href="?page=export&ex=refra" target="_blank">RefRat-Liste</a> (<a href="http://www.refrat.hu-berlin.de/finanzen/TeilnehmerInnenlisteMitEinverstaendniserklaerung.pdf">orig</a>)</li>
    <li><a href="?page=export&ex=treff" target="_blank">Treffpunktanwesenheitsliste</a></li>
    <li><a href="?page=export&ex=konto" target="_blank">Kontodatenliste</a></li>
    <li><a href="?page=export&ex=mord" target="_blank">Mörderspiel</a></li>
    <li><a href="?page=export&ex=unter" target="_blank">Anwesenheitsunterschriftsliste für Unterkunft</a></li>
    <li><a href="http://www.refrat.de/docs/finanzen/FormularFSErstattung_sepa_form_2.pdf">Erstattungsformular</a></li>
</ul>
NOTE: No Chrome support! Webkit/Blink haven\'t implemented support for @media print... This feature is developed to support Firefox.
';

if(isset($_REQUEST['ex'])){
    global $template;
    $template = file_get_contents("../view/print_template.html");
    $text = "";

    switch($_REQUEST['ex']){
        case "refra": genRefRa(); break;
        case "treff": genTreff(); break;
        case "konto": genKonto(); break;
        case "mord":  genMord();  break;
        case "unter": genUnter(); break;
        default:
            break;
    }
}

function genRefRa(){
    global $header, $footer, $admin_db, $config_current_fahrt_id;

    $people = $admin_db->select('bachelor',["forname", "sirname"], ["AND" => ["fahrt_id"=>$config_current_fahrt_id, "backstepped" => NULL], "ORDER" => "forname ASC"]);
    $tabdata = [];
    foreach($people as $p){
        array_push($tabdata, [$p['forname']." ".$p['sirname'],"",""]);
    }
    // leerfelder (just in case)
    for($run = 0; $run < 8; $run++){
        array_push($tabdata, ["&nbsp;","&nbsp;","&nbsp;"]);
    }

    $tabconf = ["colwidth" => ["20%", "55%", "25%"],
                "cellheight" => "35pt"];

    printTable(["Name", "Anschrift", "Unterschrift"], $tabdata, $tabconf);

    $data = getFahrtInfo();

    $header = "
<h1>TeilnehmerInnenliste und Einverständniserklärung</h1>
<h2>Fachschaftsfahrt</h2>
Fachschaft: <u>Informatik</u><br />
Ziel: <u>".$data['ziel']."</u><br />
Datum der Fahrt: <u>".comm_from_mysqlDate($data['von'])." - ".comm_from_mysqlDate($data['bis'])."</u><br />
Hiermit erklären wir uns mit unserer Unterschrift damit einverstanden, dass das von uns
ausgelegten Geld für die Fachschaftsfahrt auf das Konto des/der Finanzverantwortlichen/r,
<u>".$data['leiter']."</u>, überwiesen wird.";
    $footer = "Einverstädniserklärung - ".$data['titel'];
}

function genTreff(){
    global $header, $footer, $admin_db, $config_current_fahrt_id, $config_reisearten_o;

    $people = $admin_db->select('bachelor',["forname", "sirname"], ["AND" => ["fahrt_id"=>$config_current_fahrt_id, "backstepped" => NULL, "antyp" => $config_reisearten_o['BUSBAHN']], "ORDER" => "forname ASC"]);
    $ttabdata = [];
    foreach($people as $p){
        array_push($ttabdata, $p['forname']." ".$p['sirname']);
    }
    // leerfelder (just in case)
    for($run = 0; $run < 8; $run++){
        array_push($ttabdata, "&nbsp;");
    }

    $tabdata = [];
    // transpose long list to have multiple columns
    for($run = 0; $run < count($ttabdata); $run++){
        $c1 = (isset($ttabdata[$run]) ? $ttabdata[$run] : "&nbsp;");
        $run++;
        $c2 = (isset($ttabdata[$run]) ? $ttabdata[$run] : "&nbsp;");
        $run++;
        $c3 = (isset($ttabdata[$run]) ? $ttabdata[$run] : "&nbsp;");
        array_push($tabdata, [$c1, "&nbsp;", "", $c2, "&nbsp;", "", $c3, "&nbsp;"]);
    }

    $tabconf = ["colwidth" => ["20%", "10pt", "1px; margin:0; padding:0; font-size:1pt", "20%", "10pt", "1px; margin:0; padding:0; font-size:1pt", "20%", "10pt"],
        "cellheight" => "25pt"];

    printTable(["Name", "X", "", "Name", "X", "", "Name", "X"], $tabdata, $tabconf);

    $data = getFahrtInfo();

    $header = "
<h1>Anwesenheitsliste Treffpunkt</h1>
Liste aller Teilnehmer, die angegeben haben, gemeinsam mit Bus/Bahn anzureisen";
    $footer = "Anwesenheitsliste - ".$data['titel'];
}

function genKonto(){
    global $header, $footer, $admin_db, $config_current_fahrt_id;

    $people = $admin_db->select('bachelor',["forname", "sirname"], ["AND" => ["fahrt_id"=>$config_current_fahrt_id, "backstepped" => NULL], "ORDER" => "forname ASC"]);
    $tabdata = [];
    foreach($people as $p){
        array_push($tabdata, [$p['forname']." ".$p['sirname'],"&nbsp;","&nbsp;","&nbsp;","&nbsp;"]);
    }
    // leerfelder (just in case)
    for($run = 0; $run < 8; $run++){
        array_push($tabdata, ["&nbsp;","&nbsp;","&nbsp;","&nbsp;","&nbsp;"]);
    }

    $tabconf = ["colwidth" => ["25%", "30%", "25%", "15%", "5%"],
        "cellheight" => "35pt",
        "class" => [3=>"graycell", 4=>"graycell"]];

    printTable(["Name", "Kontonummer/IBAN", "Bankleitzahl/BIC", "Betrag*", "Erl*"], $tabdata, $tabconf);

    $data = getFahrtInfo();

    $header = "
<h1>Kontodaten für Rücküberweisung</h1>
Diese Liste verbleibt bei dem/der Fahrtverantwortlichen <u>".$data['leiter']."</u> und wird benötigt um die Förderung und den Differenzbetrag nach der Fahrt zurück zu überweisen.<br />
<b>Graue/mit Sternchen gekennzeichnete Fehler freilassen</b> (Trolle bekommen kein Geld!!)";
    $footer = "Kontodaten (Rücküberweisung) - ".$data['titel'];
}

function genMord(){
    global $header, $footer, $admin_db, $config_current_fahrt_id, $text;

    $people = $admin_db->select('bachelor',["forname", "sirname"], ["AND" => ["fahrt_id"=>$config_current_fahrt_id, "backstepped" => NULL], "ORDER" => "forname ASC"]);
    $tabdata = [];
    foreach($people as $p){
        array_push($tabdata, [$p['forname']." ".$p['sirname'],"&nbsp;","&nbsp;","&nbsp;"]);
    }

    $tabconf = ["colwidth" => ["25%", "20%", "20%", "35%"],
        "cellheight" => "35pt"];

    printTable(["Opfer", "Zeitpunkt", "Mörder", "Tathergang"], $tabdata, $tabconf);

    $text .= '<div class="page-break"></div>';

    $people = array_map(function($piece) { return $piece[0]; }, $tabdata);
    shuffle($people);
    $text .= '<div style="page-break-inside: avoid;"><h1>Cheatsheet</h1><p>';
    $text .= implode('&nbsp;->&nbsp;', $people);
    $text .= '</p></div>';

    $instructions = ['Gib deinem Opfer in einem Moment des Verlusts der Aufmerksamkeit einen Gegenstand
     um es umzubringen und melde es dem Spielleiter. Das darf nicht erzwungen werden; keine Zeugen.',
    'Sammle die Wimper deines Opfers ein und händige sie über mit der Bitte sich etwas zu wünschen um dein Opfer zu ermorden.
    Wimper darf nicht ausgerissen werden. Andere pustbare Dinge auch erlaubt. Das darf nicht erzwungen werden; keine Zeugen.',
    'Hilf deinem Opfer bei einer Übungsaufgabe und gib den Stift zurück um es umzubringen. Das darf nicht erzwungen werden; keine Zeugen.'];

    for($i = 1; $i < count($people); $i++) {
        $text .= '<div class="killbox">
                    <span role="killer">'.$people[$i-1].'</span>
                    <span role="victim">'.$people[$i].'</span>
                    <p role="instruction">'.$instructions[array_rand($instructions)].'</p>
                  </div>';
    }


    $data = getFahrtInfo();

    $header = "
<h1>Mordaufträge und Übersicht</h1>
Fröhliches Morden! Bitte keine tödlichen Gegenstände benutzen.";
    $footer = "Mörderspiel - ".$data['titel'];
}

function genUnter(){
    global $header, $footer, $admin_db, $config_current_fahrt_id;

    $people = $admin_db->select('bachelor',["forname", "sirname"], ["AND" => ["fahrt_id"=>$config_current_fahrt_id, "backstepped" => NULL], "ORDER" => "forname ASC"]);
    $tabdata = [];
    foreach($people as $p){
        array_push($tabdata, [$p['forname']." ".$p['sirname'],"&nbsp;","&nbsp;"]);
    }
    // leerfelder (just in case)
    for($run = 0; $run < 8; $run++){
        array_push($tabdata, ["&nbsp;","&nbsp;","&nbsp;"]);
    }

    $tabconf = ["colwidth" => ["20%", "25%", "55%"],
        "cellheight" => "25pt"];

    printTable(["Name", "Unterschrift", "&nbsp;"], $tabdata, $tabconf);

    $data = getFahrtInfo();

    $header = "
<h1>TeilnehmerInnenliste</h1>
<h2>Fachschaftsfahrt</h2>
Fachschaft: <u>Informatik</u><br />
Datum der Fahrt: <u>".comm_from_mysqlDate($data['von'])." - ".comm_from_mysqlDate($data['bis'])."</u><br />
Verantwortlicher: <u>".$data['leiter']."</u><br />
Liste aller Teilnehmer der Fachschaftsfahrt in der Einrichtung <u>".$data['ziel']."</u>";
    $footer = "TeilnehmerInnenliste - ".$data['titel'];
}

function printTable($headers, $data, $tabconf = []){
    global $text;

    $text.="
    <table class='dattable'>
        <thead>
            <tr>";
                $cell = 0;
                foreach($headers as $h){
                    $text.="<th".cellStyle($tabconf, $cell).">".$h."</th>";
                    $cell++;
                }
    $text.="
            </tr>
        </thead>
        <tbody>";
            foreach($data as $dr){
                $text .= "<tr>";
                    $cell = 0;
                    foreach($dr as $dc){
                        $text .= "<td".cellStyle($tabconf,$cell).">".$dc."</td>";
                        $cell++;
                    }
                $text .= "</tr>";
            }
    $text .="
        </tbody>
    </table>";
}

function cellStyle($tabconf, $cell){
    $ret = "";
    if(isset($tabconf['cellheight']) || isset($tabconf['colwidth']) || isset($tabconf['class'])){
        $ret .= " style='";
        if(isset($tabconf['cellheight']))
            $ret .= "height:".$tabconf['cellheight'].";";
        if(isset($tabconf['colwidth']) && isset($tabconf['colwidth'][$cell]))
            $ret .= "width:".$tabconf['colwidth'][$cell].";";
        $ret .= "'";

        if(isset($tabconf['class']) && isset($tabconf['class'][$cell]))
            $ret .= " class='".$tabconf['class'][$cell]."'";
    }
    return $ret;
}

function getFahrtInfo(){
    global $config_current_fahrt_id, $admin_db;
    return $admin_db->get("fahrten", ["beschreibung", "titel", "von", "bis", "ziel", "map_pin", "leiter", "kontakt", "regopen", "max_bachelor"], array("fahrt_id"=>$config_current_fahrt_id));
}