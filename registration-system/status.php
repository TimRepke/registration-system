<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 8/16/14
 * Time: 5:41 PM
 */


require 'config.inc.php';
require 'frameworks/medoo.php';
require 'frameworks/commons.php';
require 'lang.php';

$config_verbose_level = 0;

$status_db = new medoo(array(
    'database_type' => $config_db["type"],
    'database_name' => $config_db["name"],
    'server'        => $config_db["host"],
    'username'      => $config_db["user"],
    'password'      => $config_db["pass"]
));

require 'view/default_index.php';


function show_content(){
    global $status_db, $config_current_fahrt_id, $config_mailtag;
    if(!isset($_REQUEST['hash']))
        die("Kein Hash angegeben!");

    $data = $status_db->get("bachelor", "*", array("bachelor_id"=>substr($_REQUEST['hash'],0,15)));
    $wl = FALSE;
    if(!$data){
        $data = $status_db->get("waitlist", "*", array("waitlist_id"=>substr($_REQUEST['hash'],0,15)));
        $wl = TRUE;
        if(!$data)
            die("<h1>Kein gültiger Hash gegeben!</h1>");
    }

    $infolist = Array(
    	'Anmelde ID' => $data['bachelor_id'],
        'Anmeldetag' => date('d.m.Y', $data['anm_time']),
    	'Vor-/Nachname' => $data['forname'].' '.$data['sirname'].(strlen($data['pseudo']) > 0 ? ' ('.$data['pseudo'].')' : ""),
    	'eMail-Adresse' => $data['mehl'],
    	'Anreisetag &amp; Art' => comm_from_mysqlDate($data["anday"]).' ('.$data["antyp"].')',
    	'Abreisetag &amp; Art' => comm_from_mysqlDate($data["abday"]).' ('.$data["abtyp"].')',
    	'Essenswunsch' => $data["essen"],
    	'Zahlung erhalten' => ((is_null($data["paid"])) ? "nein" : date('d.m.Y',$data["paid"])),
    	'Rückzahlung gesendet' => ((is_null($data["repaid"])) ? "nein" : date('d.m.Y',$data["repaid"])),
    	//'Zurückgetreten' => (($data["backstepped"]==1) ? "ja" : "nein"),
    	'Kommentar' => $data["comment"]
    );

    echo '
    <div class="fahrt" style="background: #f9f9f9"><div class="fahrttitle">Anmeldedaten</div>';

    if($wl)
        echo '<div style="color: red; font-weight: bold; font-size: 14pt;">Achtung, dies ist nur ein Eintrag auf der Warteliste!<br /> Sofern keine weiteren Auskünfte folgen, kannst du leider NICHT mitfahren...</div>';

    echo'
    <div class="fahrttable">';

    foreach($infolist as $key => $value)
    {
    	echo '<div>'; // (invisible(magic(style))) table row
       	echo "<div style='display:table-cell; font-weight: bold; padding: 3px 40px 3px 0'>$key</div><div style='display:table-cell'>$value</div>";
        echo '</div>';
    }
    echo '</div></div>';

    $mailto = $status_db->get("fahrten", "kontakt", array("fahrt_id"=>$config_current_fahrt_id));
    $subject= $config_mailtag.'Änderung zu '.$data["forname"].' '.$data["sirname"].' ('.$data["pseudo"].')';

    echo '<a style="float:none;font-weight:bold" href="mailto:'.$mailto.'?subject='.str_replace(" ", "%20",$subject).'">Änderung melden</a>';

}

function echo_headers() {
    echo '<link rel="stylesheet" href="view/style.css" />';
}
