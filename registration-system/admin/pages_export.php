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
    global $text, $header, $footer;
    printTable(["col 1", "vol2"], [["a","b"],["c","d"]]);
    $header = "Testhead";
    $footer = "Testfoot";
}

function genTreff(){

}

function genKonto(){

}

function genUnter(){

}

function printTable($headers, $data){
    global $text;

    $text.="
    <table>
        <thead>
            <tr>";
                foreach($headers as $h)
                    $text.="<th>".$h."</th>";
    $text.="
            </tr>
        </thead>
        <tbody>";for($i=0; $i<300; $i++){
            foreach($data as $dr){
                $text .= "<tr>";
                    foreach($dr as $dc)
                        $text .= "<td>".$dc."</td>";
                $text .= "</tr>";
            }}
    $text .="
        </tbody>
    </table>";
}