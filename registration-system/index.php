<?php
error_reporting(E_ALL || E_STRICT);

require 'config.inc.php';
require 'frameworks/medoo.php';
require 'frameworks/commons.php';


$index_db = new medoo(array(
    'database_type' => $config_db["type"],
    'database_name' => $config_db["name"],
    'server'        => $config_db["host"],
    'username'      => $config_db["user"],
    'password'      => $config_db["pass"]
));


require 'view/default_index.php';


// ================================================================
// functions

/**
 * main function
 * gets called within the template
 */
function index_show_content(){
    global $index_db;
    // Zeige Details einer FS Fahrt
    if(isset($_REQUEST['fid'])){
        $fid = $_REQUEST['fid'];

        // wenn die fahrt-id falsch, liste alle fahrten
        if(!$index_db->has('fahrten',array('fahrt_id'=>$fid))){
            comm_verbose(1,"FID nicht vorhanden!");
            goto allefahrten;
        }

        // --- Fahrtinfos
        index_show_fahrtHeader($fid);

        // --- Formular
        if(isset($_REQUEST['submit'])){ // Formular auswerten
            comm_verbose(1,"Formular bekommen");
            index_check_form();
        } elseif(isset($_REQUEST['bid'])){ // Änderungsformular anzeigen TODO: Anmeldung noch offen?
            index_show_formular($fid, $_REQUEST['bid']);
        } else {                       // leeres Formular anzeigen
            index_show_formular($fid);
        }

        // --- Liste der Anmeldungen
        index_show_signupTable($fid);
    }
    // Zeige Übersicht aller Fahrten
    else {
        allefahrten:
        index_show_alleFahrten();
    }

}

/**
 * validates the sent form
 * on failure: repost form with prefilled data and errors
 * on success: put data into DB and post success messagage
 *
 */
function index_check_form(){
    global $config_studitypen, $config_essen, $config_reisearten;
    $errors = array();
    $fid  = $_REQUEST['fid'];
    $data = array();
    $possible_dates = comm_get_possible_dates($fid);

    index_check_field('forname', '/^[a-zA-Z]{2,50}$/', $data, $errors, "Fehlerhafter oder fehlender Vorname!");
    index_check_field('sirname', '/^[a-zA-Z]{2,50}$/', $data, $errors, "Fehlerhafter oder fehlender Nachname!");
    index_check_field('pseudo', '/^\w{2,50}$/', $data, $errors, "Fehlerhafter oder fehlender Anzeigename!");
    index_check_field('mehl', 'mail', $data, $errors, "Fehlerhafte oder fehlende E-Mail-Adresse!");
    index_check_field('anday', array_slice($possible_dates,0, -1), $data, $errors, 'Hilfe beim Ausfüllen: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
    index_check_field('antyp', $config_reisearten, $data, $errors, 'Trolle hier lang: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
    index_check_field('abday', array_slice($possible_dates,1), $data, $errors, 'Ruth hat mitgedacht: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
    index_check_field('abtyp', $config_reisearten, $data, $errors, 'Entwickler Bier geben und: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
    index_check_field('essen', $config_essen, $data, $errors, 'Hat das wirklich nicht gereicht??'); // ggf trollable machen mit /^[a-zA-Z]{2,50}$/
    index_check_field('studityp', $config_studitypen, $data, $errors, 'Neue Chance, diesmal FS-Ini wählen!');
    index_check_field('public', "public", $data, $errors, 'Trollololol');
    index_check_field('virgin', array("Ja","Nein"), $data, $errors, 'Bitte Altersbereich wählen!');
    index_check_field('comment', "comment", $data, $errors, 'Trollololol');

    if(count($errors)>0){
        index_show_errors($errors);
        index_show_formular($fid, NULL, $data);
    } else {

        // put in DB

    }

}

/**
 * puts out a list of all errors
 * @param $errors
 */
function index_show_errors($errors){
    echo '<div class="message error"><ul>';
    foreach($errors as $e){
        echo '<li>'.$e.'</li>';
    }
    echo'</ul></div>';
}

/**
 * checks for correctness of a given field ($index) by trying $check.
 * pushes $errmess into $errarr, if $check fails
 * pushes empty data on fail or correct data on success into $data
 *
 * check can be regex or array or special (public, mail, comment).
 * if array, than check only succeeds if sent data is inside that array
 *
 * @param $index
 * @param $check
 * @param $datarr
 * @param $errarr
 * @param $errmess
 */
function index_check_field($index, $check, &$datarr, &$errarr, $errmess){
    $pushdat = "";
    comm_verbose(3,"checking ".$index);

    if($check == "public"){
        if(isset($_REQUEST[$index])) $datarr[$index] = 0;
        else  $datarr[$index] = 1;
    } elseif(!isset($_REQUEST[$index])){
        array_push($errarr, $errmess);
        $datarr[$index] = "";
    } else {
        $tmp = trim($_REQUEST[$index]);
        if(is_array($check)){
            if(!in_array($tmp,$check)){
                array_push($errarr, $errmess);
                $tmp = "";
            }
        } else {
            if($check == "mail"){
                if (!filter_var($tmp, FILTER_VALIDATE_EMAIL)) {
                    array_push($errarr, $errmess);
                    $tmp = "";
                }
            } elseif($check == "comment"){
                // do nothing? maybe some graphical joke, is somebody tries to drop DB
            } elseif(!(preg_match($check, $tmp)==1)){
                array_push($errarr, $errmess);
                $tmp = "";
            }
        }
        $datarr[$index] = $tmp;
    }
}

/**
 * Generates a registration form for a given event ($fid)
 *
 * @param $fid
 * @param null $bid - if not null: prefill form with data from DB
 * @param null $bachelor - if not null: prefill form with these data (take care, keys have to exist!)
 */
function index_show_formular($fid, $bid = NULL, $bachelor = NULL){
    global $index_db, $config_studitypen, $config_essen, $config_reisearten;

    $possible_dates = comm_get_possible_dates($fid);

    if(is_null($bachelor))
        $bachelor = array('forname' => "", 'sirname' => "", 'anday' => $possible_dates[0], 'abday' => $possible_dates[count($possible_dates)-1], 'antyp' => "", 'abtyp' => "", 'pseudo' => "", 'mehl' => "", 'essen' => "", 'public' => "", 'virgin' => "", 'studityp' => "", 'comment'=>"");
    if(!is_null($bid)){
        if($index_db->has('bachelor',array('bachelor_id' => $bid))){
            $bachelor = $index_db->select('bachelor', array('forname','sirname','anday','abday','antyp','abtyp','pseudo','mehl','essen','public','virgin','studityp','comment'), array('bachelor_id'=>$bid));
            $bachelor = $bachelor[0];
        }
    }
    $fidd = is_null($bid) ? $fid : $fid."&bid=".$bid;
    echo '<div id="stylized" class="myform">
        <form id="form" name="form" method="post" action="index.php?fid='.$fidd.'">
        <h1>Anmeldeformular</h1>
        <p>Bitte hier verbindlich anmelden.</p>';

    index_show_formular_helper_input("Vorname", "forname", $bachelor["forname"], "");
    index_show_formular_helper_input("Nachname","sirname",$bachelor["sirname"],"");
    index_show_formular_helper_input("Anzeigename","pseudo",$bachelor["pseudo"],"");
    index_show_formular_helper_input("E-Mail-Adresse","mehl",$bachelor["mehl"],"regelmäßig lesen!");
    index_show_formular_helper_sel("Du bist","studityp",$config_studitypen, $bachelor["studityp"],"");
    index_show_formular_helper_sel("Alter 18+?","virgin",array("", "Nein", "Ja"), $bachelor["virgin"], "Bist du älter als 18 Jahre?");
    index_show_formular_helper_sel("Essenswunsch","essen",$config_essen, $bachelor["essen"],"Info für den Koch.");
    index_show_formular_helper_sel2("Anreise","anday", array_slice($possible_dates,0, -1), $bachelor["anday"]
                                             ,"antyp",$config_reisearten, $bachelor["antyp"],"");
    index_show_formular_helper_sel2("Abreise","abday", array_slice($possible_dates,1), $bachelor["abday"]
                                             ,"abtyp",$config_reisearten,$bachelor["abtyp"],"");

    echo'
        <label>Anmerkung</label>
        <textarea id="comment" name ="comment" rows="3" cols="50">'.$bachelor["comment"].'</textarea>
        <input type="checkbox" name="public" value="public" style="width:40px"><span style="float:left">Anmeldung verstecken</span>
        <button type="submit" name="submit" id="submit" value="submit">Anmelden!</button>
        <div class="spacer"></div>
        </form>
        </div>';
}

/**
 * Puts out Label and Selection box
 *
 * @param $name
 * @param $id
 * @param $values
 * @param $selected
 * @param $subtext
 */
function index_show_formular_helper_sel($name, $id, $values, $selected, $subtext){
    echo '<label>'.$name.'
        <span class="small">'.$subtext.'</span>
        </label>
        <select name="'.$id.'" id="'.$id.'">';
    foreach($values as $val){
        echo '<option value="'.$val.'"';
        if($val == $selected) echo ' selected';
        echo'>'.$val.'</option>';
    }
    echo '</select>';
}

/**
 * Puts out Label and two selection boxes side by side right below
 *
 * @param $name
 * @param $id
 * @param $values
 * @param $selected
 * @param $id2
 * @param $values2
 * @param $selected2
 * @param $subtext
 */
function index_show_formular_helper_sel2($name, $id, $values, $selected, $id2, $values2, $selected2, $subtext){
    echo '<label style="text-align:left">'.$name.'
        <span class="small">'.$subtext.'</span>
        </label><table><tr><td>
        <select name="'.$id.'" id="'.$id.'" style="width:90px">';
    foreach($values as $val){
        echo '<option value="'.$val.'"';
        if($val == $selected) echo ' selected';
        echo'>'.$val.'</option>';
    }
    echo '</select></td><td><select name="'.$id2.'" id="'.$id2.'">';
    foreach($values2 as $val){
        echo '<option value="'.$val.'"';
        if($val == $selected2) echo ' selected';
        echo'>'.$val.'</option>';
    }
    echo '</select></td></tr></table>';
}

function index_show_formular_helper_input($name, $id, $value, $subtext){
    echo '<label>'.$name.'
        <span class="small">'.$subtext.'</span>
        </label>
        <input type="text" name="'.$id.'" id="'.$id.'" value="'.$value.'" />';
}

/**
 * show list of all fahrten
 */
function index_show_alleFahrten(){
    global $index_db;
    comm_verbose(2,"Liste aller Fahrten (Jahr, Ziel, Zeitraum, Anz. Mitfahrer)");
    $foos = $index_db->select("fahrten",array('fahrt_id','titel','ziel','von','bis','beschreibung','leiter','kontakt'));
    foreach($foos as $foo){
        index_show_fahrtHeader($foo);
    }
}

/**
 * @param $fahrt wenn array, dann Datenbankrow; wenn zahl, dann wird das selektiert
 */
function index_show_fahrtHeader($fahrt){
    global $index_db;
    if(!is_array($fahrt)){
        // select fahrt by ID
        $fahrt = $index_db->select('fahrten', array('fahrt_id','titel','ziel', 'von', 'bis', 'leiter', 'kontakt', 'beschreibung'), array('fahrt_id'=> $fahrt));
        if(!$fahrt){ index_show_alleFahrten(); return;}
        else  $fahrt = $fahrt[0];
    }

    echo '<div class="fahrt"><a href="index.php?fid='.$fahrt['fahrt_id'].'">'.$fahrt['titel'].'</a>';
    echo 'Ziel: <i>'.$fahrt['ziel'].'</i><br />';
    echo 'Datum: <i>'.comm_format_date($fahrt['von'])." - ".comm_format_date($fahrt['bis']).'</i><br />';
    echo "Ansprechpartner: <i>".$fahrt['leiter']." (".comm_convert_mail($fahrt['kontakt']).")</i>";
    echo '<p>'.$fahrt['beschreibung'].'</p>
    </div>';
}

/**
 * show table of public registrations
 */
function index_show_signupTable($fid){
    global $index_db;
    $data = $index_db->select('bachelor',array('pseudo','antyp','abtyp','antag','abtag','comment','studityp'),
        array("AND" => array(
            "fahrt_id" => $fid,
            "public"   => 1,
            "valid_version" => 1
        )));

    if(!$data) echo'<div class="signups">Noch keine (sichtbaren) Anmeldungen!</div>';
    else {
        echo '
            <table class="signups">
                <thead>
                    <tr>
                        <th></th>
                        <th>Anzeigename</th>
                        <th>Anreiseart</th>
                        <th>Anreisetag</th>
                        <th>Abreiseart</th>
                        <th>Abreisetag</th>
                        <th>Kommentar</th>
                    </tr>
                </thead>';
        foreach($data as $d){
            echo '<tr>
                <td>'.$d["studityp"].'</td>
                <td>'.$d["pseudo"].'</td>
                <td>'.$d["antag"].'</td>
                <td>'.$d["antyp"].'</td>
                <td>'.$d["abtag"].'</td>
                <td>'.$d["abtyp"].'</td>
                <td>'.$d["comment"].'</td>
            </tr>';
        }
        echo '</table>';
    }
}
