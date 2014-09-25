<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 9/25/14
 * Time: 9:24 PM
 */

global $text, $headers, $admin_db, $config_userfile, $config_current_fahrt_file, $config_current_fahrt_id, $ajax, $config_reisearten, $config_reisearten_0, $config_studitypen_o, $config_admin_verbose_level, $config_verbose_level, $config_essen;
//$config_admin_verbose_level = 4;
//$config_verbose_level = 4;

$text .= "<h1>SuperAdmin Panel</h1>";

// FORM submit stuff

if(isset($_REQUEST['us_submit'])){
    if(!isset($_REQUEST['users'])) $text.= "something wrong... wanted to submit users";
    else {
        $tmp = file_put_contents($config_userfile, $_REQUEST['users']);
        $text .= "updated userfile!<br />
         written to file '".$config_userfile."' with exit code: ". $tmp ."<br />
         if no code shown, then false -> check if file has chmod rw-rw-rw-!";
    }
}

if(isset($_REQUEST['nf_submit'])){
    if(isset($_REQUEST['resubmit'])){}
    else{
        $admin_db->insert("fahrten", ["titel"=>"neu", "map_pin" => "52.42951196033782 13.530490995971718", "von"=>date('Y-m-d'), "bis" => date('Y-m-d')]);
        $tmp = $admin_db->max("fahrten", "fahrt_id");
        $text .= "neue Fahrt angelegt mit ID: ".$tmp. "<br/>
            Zum Bearbeiten ID ändern!";
    }
}

if(isset($_REQUEST['id_submit'])){
    $tmp = file_put_contents($config_current_fahrt_file, $_REQUEST['fid']);
    $text .= "changed \$config_current_fahrt_id to ".$_REQUEST['fid'] . "<br />
     written to file '".$config_current_fahrt_file."' with exit code: ". $tmp ."<br />
     if no code shown, then false -> check if file has chmod rw-rw-rw-!";
}







// VIEW stuff:

if(file_exists($config_userfile))
    $usas = file_get_contents($config_userfile);
$text .= '<h2>Nutzer bearbeiten</h2>
ACHTUNG: Tippfehler können Systemfunktionalität beeinträchtigen! <i>Format: {N|S}⎵USERNAME⎵PASSWORD⎵RANDOMSTUFF</i><br />
<a href="../passwd/index.html">Passwort-gen tool</a> (an Organisator weiterleiten, der schickt dann Passwort hash zurück)<br />
<form method="POST">
    <textarea rows="8" cols="130" name="users" id="users">'.$usas.'</textarea><br />
    <input type="submit" name="us_submit" id="us_submit" value="us_submit" />
</form> ';


$text .= '<h2>Neue Fahrt anlegen</h2>
<form method="POST" target="?resubmit=not">
    <input type="submit" name="nf_submit" value="nf_submit" id="nf_submit" />
</form> ';


$text .= '<!--h2>Fahrt löschen</h2>
ACHTUNG: löscht ohne Nachfrage ALLE mit dieser Fahrt verbunden Daten!<br /-->';


$fids = $admin_db->select("fahrten","fahrt_id");
$config_current_fahrt_id = getCFID();
$text .= '<h2>Aktuelle Fahrt ID</h2>
    Wählt die Fahrt, die über das Adminpanel bearbeitet/verwaltet werden kann.<br />
    <form method="POST" >
        <label>Neue ID wählen (aktiv: '.$config_current_fahrt_id.'):</label>
        <select name="fid" id="fid">';
            foreach($fids as $fid)
                $text .= '<option value="'.$fid.'">'.$fid.'</option>';
$text .= '
        </select>
        <input type="submit" name="id_submit" value="id_submit" id="id_submit" />
    </form>';
