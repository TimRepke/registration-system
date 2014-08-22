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
    if(!$data)
        die("Kein gültiger Hash gegeben!");

    echo '
    <table>
        <tr>
            <td>Melde-ID</td>
            <td>'.$data["bachelor_id"].'</td>
        </tr>
        <tr>
            <td>Name</td>
            <td>'.$data["forname"].' '.$data["sirname"].' ('.$data["pseudo"].')</td>
        </tr>
        <tr>
            <td>E-Mail-Adresse</td>
            <td>'.$data["mehl"].'</td>
        </tr>
        <tr>
            <td>Anreisetag + Art</td>
            <td>'.$data["anday"].' ('.$data["antyp"].')</td>
        </tr>
        <tr>
            <td>Abreisetag + Art</td>
            <td>'.$data["abday"].' ('.$data["abtyp"].')</td>
        </tr>
        <tr>
            <td>Essenswunsch</td>
            <td>'.$data["essen"].'</td>
        </tr>
        <tr>
            <td>Zahlung erhalten</td>
            <td>'.((is_null($data["paid"])) ? "nein" : date('d.m.Y',$data["paid"])).'</td>
        </tr>
        <tr>
            <td>Rückzahlung gesendet</td>
            <td>'.((is_null($data["repaid"])) ? "nein" : date('d.m.Y',$data["repaid"])).'</td>
        </tr>
        <!--tr>
            <td>Zurückgetreten</td>
            <td>'.(($data["backstepped"]==1) ? "ja" : "nein").'</td>
        </tr-->
        <tr>
            <td>Kommentar</td>
            <td>'.$data["comment"].'</td>
        </tr>
    </table>';

    $mailto = $status_db->get("fahrten", "kontakt", array("fahrt_id"=>$config_current_fahrt_id));
    $subject= $config_mailtag.'Änderung zu '.$data["forname"].' '.$data["sirname"].' ('.$data["pseudo"].')';

    echo '<a style="float:none" href="mailto:'.$mailto.'?subject='.str_replace(" ", "%20",$subject).'">Änderung melden</a>';

}

?>