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
        case "unter": genUnter(); break;
        default:
            break;
    }
}

function genRefRa(){
    global $header, $footer, $admin_db, $config_current_fahrt_id;

    $people = $admin_db->select('bachelor',["forname", "sirname"], ["fahrt_id"=>$config_current_fahrt_id]);
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

}

function genKonto(){
    global $header, $footer, $admin_db, $config_current_fahrt_id;

    $people = $admin_db->select('bachelor',["forname", "sirname"], ["fahrt_id"=>$config_current_fahrt_id]);
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

function genUnter(){

}

function printTable($headers, $data, $tabconf = []){
    global $text;

    $text.="
    <table class='dattable'>
        <thead>
            <tr>";
                foreach($headers as $h)
                    $text.="<th>".$h."</th>";
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