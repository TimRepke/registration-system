<?php
session_start();
// error_reporting(E_ALL | E_STRICT);

require 'config.inc.php';
require 'frameworks/medoo.php';
require 'frameworks/commons.php';
require 'lang.php';
require 'frameworks/soft_protect.php';


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
        // storySubmit wg JQuery .submit() auf forms geht sonst nicht
        if(isset($_REQUEST['success'])) {
            echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Die Anmeldung war erfolgreich.</div>';
        } elseif(isset($_REQUEST['full'])) {
			echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Anmeldung leider fehlgeschlagen.</div>';
			echo '<div style="text-align:center; font-size: 16pt; font-weight: bold">Die Anmeldegrenze wurde leider erreicht.</div>';
			echo '<div style="text-align:center; font-size: 14pt; ">Es besteht die Möglichkeit sich auf der Warteliste einzutragen.<br /> Wenn du das möchtest, klicke hier: <a class="normallink" href="?fid='.$fid.'&waitlist">&#8694; Warteliste</a></div>';
		} elseif(isset($_REQUEST['submit']) || isset($_REQUEST['storySubmit'])){ // Formular auswerten
            comm_verbose(1,"Formular bekommen");
            $data = index_check_form();
            if(!is_null($data))
            {
                if (index_form_to_db($data))
					header("Location: ?fid=".$fid."&success");
				else
					header("Location: ?fid=".$fid."&full");
                die();
			}
        } /*elseif(isset($_REQUEST['bid'])){ // Änderungsformular anzeigen, Anmeldung noch offen?
            index_show_formular($fid, $_REQUEST['bid']);
        } */ else {                       // leeres Formular anzeigen
            $openstatus = comm_isopen_fid_helper($index_db, $fid);
			if ($openstatus == 0 || isset($_REQUEST['waitlist']))
				index_show_formular($fid);
			else {
                echo '<div style="text-align:center; font-size: 20pt; font-weight: bold">Die Anmeldung wurde geschlossen.</div>';
                if( $openstatus != 2 ){
                    echo '<div style="text-align:center; font-size: 16pt; font-weight: bold">Ein Auge offen halten, ob Plätze frei werden.</div>';
                    echo '<div style="text-align:center; font-size: 14pt;">Es gibt aber auch eine Warteliste.<br /> Wenn du da rauf möchtest, klicke hier: <a class="normallink" href="?fid='.$fid.'&waitlist">&#8694; Warteliste</a></div>';
                }
			}	
        }

        // --- Liste der Anmeldungen
        index_show_signupTable($fid);

        index_show_fahrtHeader_js($fid);
    }
    // Zeige Übersicht aller Fahrten
    else {
        allefahrten:
        index_show_alleFahrten();
    }

}
function show_content(){
    index_show_content();
}

/**
 * puts the dataarray into DB
 * adds version = 1 and generates a unique hash for entry
 * @param $data
 */
function index_form_to_db($data){
    global $index_db, $config_baseurl;

	// === prepare data to insert ===
    $data['anm_time'] = time();
    $data['anday'] = date('Y-m-d', DateTime::createFromFormat('d.m.Y',$data['anday'])->getTimestamp());
    $data['abday'] = date('Y-m-d', DateTime::createFromFormat('d.m.Y',$data['abday'])->getTimestamp());

    if(isset($_REQUEST['waitlist'])){
        if(comm_isopen_fid_helper($index_db, $data['fahrt_id'])==1){
            // === prepare data to insert ===
            $data['waitlist_id'] = comm_generate_key($index_db, ["bachelor" => "bachelor_id", "waitlist" => "waitlist_id"], ['fahrt_id'=>$data['fahrt_id']]);

            // === insert data ===
            $index_db->insert("waitlist", $data);

            // === notify success ===
            $from = $index_db->get("fahrten", array("kontakt","leiter"), array("fahrt_id"=>$data['fahrt_id']));
            $mail = comm_get_lang("lang_waitmail", array( "{{url}}"         => $config_baseurl."status.php?hash=".$data['waitlist_id'],
                                                          "{{organisator}}" => $from['leiter']));
            comm_send_mail($index_db, $data['mehl'], $mail, $from['kontakt']);
        }
        else return false;
    } else {
        // === prepare data to insert ===
        $data['version'] = 1;
        $data['bachelor_id'] = comm_generate_key($index_db, ["bachelor" => "bachelor_id"], ['fahrt_id'=>$data['fahrt_id']]);
        // === check regstration full ===
        $res = $index_db->get("fahrten", ["regopen", "max_bachelor"], ["fahrt_id" => $data['fahrt_id']]);
        if (!$res || $res['regopen'] != "1")
            return false;

        $index_db->exec("LOCK TABLES fahrten, bachelor WRITE"); // count should not be calculated in two scripts at once

        $insertOk = comm_isopen_fid($index_db, $data['fahrt_id']);

        /*if ($cnt+1 >= $res['max_bachelor']) // registration is full already or after the following insert
            $index_db->update("fahrten", ["regopen" => 0], ["fahrt_id" => $config_current_fahrt_id]); */

        if ($insertOk)
            $index_db->insert("bachelor", $data);
        $index_db->exec("UNLOCK TABLES"); // insert is done now, count may be recalculated in other threads
        if (!$insertOk)
            return false; // full

        // === notify success ===
        $from = $index_db->get("fahrten", array("kontakt","leiter"), array("fahrt_id"=>$data['fahrt_id']));
        $mail = comm_get_lang("lang_regmail", array( "{{url}}"         => $config_baseurl."status.php?hash=".$data['bachelor_id'],
                                                     "{{organisator}}" => $from['leiter']));
        comm_send_mail($index_db, $data['mehl'], $mail, $from['kontakt']);
    }
    
    return true;
}

/**
 * validates the sent form
 * on failure: repost form with prefilled data and errors
 * on success: put data into DB and post success messagage
 *
 */
function index_check_form(){
    global $config_studitypen, $config_essen, $config_reisearten, $index_db, $invalidCharsRegEx;
    $errors = array();
    $data   = array();

    $fid  = $_REQUEST['fid'];
    $data['fahrt_id'] = $fid;
    if(comm_isopen_fid_helper($index_db, $fid)>1){
        $errors = array("Ungültige Fahrt!");
        goto index_check_form_skip;
    }

    $possible_dates = comm_get_possible_dates($index_db, $fid);

    index_check_field('forname', $invalidCharsRegEx, $data, $errors, "Fehlerhafter oder fehlender Vorname!");
    index_check_field('sirname', $invalidCharsRegEx, $data, $errors, "Fehlerhafter oder fehlender Nachname!");
    index_check_field('pseudo', $invalidCharsRegEx, $data, $errors, "Fehlerhafter oder fehlender Anzeigename!");
    index_check_field('mehl', 'mail', $data, $errors, "Fehlerhafte oder fehlende E-Mail-Adresse!");
    index_check_field('anday', array_slice($possible_dates,0, -1), $data, $errors, 'Hilfe beim Ausfüllen: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
    index_check_field('antyp', $config_reisearten, $data, $errors, 'Trolle hier lang: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
    index_check_field('abday', array_slice($possible_dates,1), $data, $errors, 'Ruth hat mitgedacht: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
    index_check_field('abtyp', $config_reisearten, $data, $errors, 'Entwickler Bier geben und: <a href="https://www.hu-berlin.de/studium/bewerbung/imma/exma">hier klicken!</a>');
    index_check_field('essen', $config_essen, $data, $errors, 'Hat das wirklich nicht gereicht??'); // ggf trollable machen mit /^[a-zA-Z]{2,50}$/
    index_check_field('studityp', $config_studitypen, $data, $errors, 'Neue Chance, diesmal FS-Ini wählen!');
    index_check_field('public', 'public', $data, $errors, 'Trollololol');
    index_check_field('virgin', array("Ja","Nein"), $data, $errors, 'Bitte Altersbereich wählen!');
    index_check_field('comment', 'comment', $data, $errors, 'Trollololol');
    index_check_field('captcha', 'captcha', $data, $errors, 'Captcha falsch eingegeben.');

    if($data['anday'] == $data['abday'])
        array_push($errors, "Anreisetag = Abreisetag -> Bitte prüfen!");

    index_check_form_skip:
    if(count($errors)>0){
        index_show_errors($errors);
        index_show_formular($fid, NULL, $data);
        return NULL;
    } else {
        return $data;

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

    // check that first because if unchecked it doesnt exist
    if($check == "public"){
        if(isset($_REQUEST[$index])) $datarr[$index] = 0;
        else  $datarr[$index] = 1;
    }

    // if index is missing -> error!
    elseif(!isset($_REQUEST[$index])){
        array_push($errarr, $errmess);

        // set it in every case so corrections are possible
        $datarr[$index] = "";
    }

    // index is there -> check if value is allowed
    else {
        $tmp = trim($_REQUEST[$index]);

        // do specific check if a set of variables is allowed
        if(is_array($check)){
            if(!in_array($tmp,$check))
                array_push($errarr, $errmess);
            else
                $datarr[$index] = $tmp;
        }

        // check captcha
        elseif($check == "captcha"){
            if(isset($_SESSION['captcha']) && strtolower($tmp) == strtolower($_SESSION['captcha'])){
                unset($_SESSION['captcha']);
            } else{
                array_push($errarr, $errmess);
                $datarr[$index] = "";
            }
        }

        // check mail address
        elseif($check == "mail"){
            if(!filter_var($tmp, FILTER_VALIDATE_EMAIL))
                array_push($errarr, $errmess);

            // set it in every case so corrections are possible
            $datarr[$index] = $tmp;
        }

        // check comment field
        elseif($check == "comment"){
            $datarr[$index] = htmlspecialchars($tmp, ENT_QUOTES);
        }

        // check virgin field
        elseif($index == "virgin"){
            if($_REQUEST[$index]=="Ja") $datarr[$index] = 0; // NOTE: for consistency: virgin = 0 means > 18
            else  $datarr[$index] = 1;
        }

        //everything else
        else {
            // check with regex
            if(!(preg_match($check, $tmp)==1))
                array_push($errarr, $errmess);

            // set it in every case so corrections are possible
            $datarr[$index] = $tmp;
        }

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
    global $index_db, $config_studitypen, $config_essen, $config_reisearten, $invalidCharsRegEx;

	$withStoryMode = !isset($_GET['noscript']) && !isset($_REQUEST['submit']) && !isset($_REQUEST['storySubmit']);
	if ($withStoryMode){
        if(isset($_REQUEST['waitlist']))
            echo '<h1 style="color: red; text-align: center;">Nur Warteliste!</h1>';
		echo '<noscript>';
    }

    $possible_dates = comm_get_possible_dates($index_db, $fid);

    if(is_null($bachelor))
        $bachelor = array('forname' => "", 'sirname' => "", 'anday' => $possible_dates[0], 'abday' => $possible_dates[count($possible_dates)-1], 'antyp' => "", 'abtyp' => "", 'pseudo' => "", 'mehl' => "", 'essen' => "", 'public' => "", 'studityp' => "", 'comment'=>"");
    if(!is_null($bid)){
        if($index_db->has('bachelor',array('bachelor_id' => $bid))){
            $bachelor = $index_db->select('bachelor', array('forname','sirname','anday','abday','antyp','abtyp','pseudo','mehl','essen','public','virgin','studityp','comment'), array('bachelor_id'=>$bid));
            $bachelor = $bachelor[0];
        }
    }
    $fidd = is_null($bid) ? $fid : $fid."&bid=".$bid;
    echo '<div id="stylized" class="myform">
        <form id="form" name="form" method="post" action="index.php?fid='.$fidd.(isset($_REQUEST['waitlist']) ? '&waitlist' : '').'">';
    if(isset($_REQUEST['waitlist'])){
        echo '<h1 style="color: red;">Warteliste</h1>
        <p>Bitte in die Warteliste eintragen.</p>';
    } else {
        echo '<h1>Anmeldeformular</h1>
        <p>Bitte hier verbindlich anmelden.</p>';
    }

    $tmp_vir = "";
    if(isset($bachelor['virgin']))
        $tmp_vir = $bachelor['virgin']==0 ? "Ja" : "Nein";

    index_show_formular_helper_input("Vorname", "forname", $bachelor["forname"], "");
    index_show_formular_helper_input("Nachname","sirname",$bachelor["sirname"],"");
    index_show_formular_helper_input("Anzeigename","pseudo",$bachelor["pseudo"],"");
    index_show_formular_helper_input("E-Mail-Adresse","mehl",$bachelor["mehl"],"regelmäßig lesen!");
    index_show_formular_helper_sel("Du bist","studityp",$config_studitypen, $bachelor["studityp"],"");
    index_show_formular_helper_sel("Alter 18+?","virgin",array("", "Nein", "Ja"), $tmp_vir, "Bist du älter als 18 Jahre?");
    index_show_formular_helper_sel("Essenswunsch","essen",$config_essen, $bachelor["essen"],"Info für den Koch.");
    index_show_formular_helper_sel2("Anreise","anday", array_slice($possible_dates,0, -1), $bachelor["anday"]
                                             ,"antyp",$config_reisearten, $bachelor["antyp"],"");
    index_show_formular_helper_sel2("Abreise","abday", array_slice($possible_dates,1), $bachelor["abday"]
                                             ,"abtyp",$config_reisearten,$bachelor["abtyp"],"");

    $soft_prot = new soft_protect();
    echo $soft_prot->add(array('forname', 'sirname', 'pseudo'), $invalidCharsRegEx)->write();

    echo'
        <label>Anmerkung</label>
        <textarea id="comment" name ="comment" rows="3" cols="50">'.$bachelor["comment"].'</textarea>
        <input type="checkbox" name="public" value="public" style="width:40px"><span style="float:left">Anmeldung verstecken</span><br/>
        <div style="clear:both"></div>';
    index_show_formular_helper_input("Captcha eingeben","captcha","","");
    echo '
        <img src="view/captcha.php" /><br/>
        <button type="submit" name="submit" id="submit" value="submit">Anmelden!</button>
        <div class="spacer"></div>
        </form>
        </div>';
	if ($withStoryMode)
	{
		function putTypesInObject($obj)
		{
			$text = '{ ';
			$first = true;
			foreach($obj as $key => $value)
			{
				if ($first)
					$first = false;
				else
					$text .= ', ';
				$text .= '"'.$key.'":"'.$value.'"';
			}
			$text .= ' }';
			return $text;
		}
		
		echo '</noscript>';
		echo '<h2>Anmeldeformular</h2>';
		echo<<<END
		<div id="storyhead"></div>
		<div id="storycanvas">
			<div id="storybox"></div>
			<div id="story_umleitung" style="position:absolute; left:0px; bottom:-70px; background:#f0f; cursor:pointer; background:url(view/graphics/story/umleitung.png); width:120px; height: 70px" onclick="story.next(true)">&nbsp;</div>
			<script type="text/javascript">
				function comm_get_possible_dates()
				{
					return [ 
END;
					$dates = comm_get_possible_dates($index_db, $fid);
					foreach($dates as &$date)
						$date = '"'.$date.'"';
					echo implode(', ', $dates);
		echo<<<END
 ];
				}
				function comm_get_food_types()
				{
					return [ 
END;
					$dates = comm_get_possible_dates($index_db, $fid);
					foreach($dates as &$date)
						$date = '"'.$date.'"';
					echo implode(', ', $dates);
		echo<<<END
 ];
				}
				function config_get_travel_types()
				{
					return 
END;
					global $config_reisearten_o;
					echo putTypesInObject($config_reisearten_o);
		echo<<<END
;
				}
				function config_get_food_types()
				{
					return 
END;
					global $config_essen_o;
					echo putTypesInObject($config_essen_o);
		echo<<<END
;
				}
			</script>
		</div>
		<div style="text-align:center;font-weight:bold"><a style="float:none;margin:0 auto;"
END;
		echo ' href="'.$_SERVER['REQUEST_URI'].'&noscript">Seite funktioniert nicht / zu bunt?</a></div>';
	}
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
        <select name="'.$id.'" id="'.$id.'" style="width:110px; text-align: center">';
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
    echo '<h2>Anmeldung zur Fachschaftsfahrt</h2>';
    $foos = $index_db->select("fahrten",array('fahrt_id','titel','ziel','von','bis','beschreibung','leiter','kontakt', 'max_bachelor'), "ORDER BY fahrt_id DESC");
    $fids = [];
    foreach($foos as $foo){
        index_show_fahrtHeader($foo);
        array_push($fids, $foo['fahrt_id']);
    }
    index_show_fahrtHeader_js($fids);
}

/**
 * @param $fahrt wenn array, dann Datenbankrow; wenn zahl, dann wird das selektiert
 */
function index_show_fahrtHeader($fahrt){
    global $index_db, $config_googleAPI;

    if(!is_array($fahrt)){
        // select fahrt by ID
        $fahrt = $index_db->select('fahrten', array('fahrt_id','titel','ziel', 'von', 'bis', 'leiter', 'kontakt', 'beschreibung', 'max_bachelor'), array('fahrt_id'=> $fahrt));
        if(!$fahrt){ index_show_alleFahrten(); return;}
        else  $fahrt = $fahrt[0];
    }
    $cnt = $index_db->count("bachelor", ["AND"=>
        ["backstepped" => NULL,
            "fahrt_id"    => $fahrt['fahrt_id']]]);

    echo '<div class="fahrt">
            <div class="fahrt-left">
            <a  class="fahrthead" href="index.php?fid='.$fahrt['fahrt_id'].'">'.$fahrt['titel'].'</a>';
        echo 'Ziel: <i>'.$fahrt['ziel'].'</i><br />';
        echo 'Datum: <i>'.comm_from_mysqlDate($fahrt['von'])." - ".comm_from_mysqlDate($fahrt['bis']).'</i><br />';
        echo "Ansprechpartner: <i>".$fahrt['leiter']." (".comm_convert_mail($fahrt['kontakt']).")</i><br />";
        echo "Anmeldungen: <i>".$cnt." / ".$fahrt['max_bachelor']."</i>";
        echo '<p>'.$fahrt['beschreibung'].'</p></div>
            <div class="map-canvas" id="map-canvas-'.$fahrt['fahrt_id'].'"></div>
            <div style="clear:both"></div>
    </div>';


}
function index_show_fahrtHeader_js($fahrten){
    global $index_db;

    echo '
        <script type="text/javascript">
            //$(document).ready(function(){
            window.onload = function() {
                $("div.fahrt-left i").click(function(){
                    });

               $("div.fahrt-left i").hover(
                    function(){
                        $(this).html($(this).html().replace(/·/g, "&#128045;"));
                        $(this).html($(this).html().replace("Ø", "&#128053;"));
                    },
                    function(){
                        $(this).html($(this).html().replace(/🐭/g, "&middot;"));
                        $(this).html($(this).html().replace("🐵", "&Oslash;"));
                    }
                );
            };

        </script>';

    $pins = $index_db->select("fahrten", ["fahrt_id", "map_pin"], ["fahrt_id" => $fahrten]);

    echo '<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
        <script type="text/javascript">';

    foreach($pins as $p){
        echo'
            var ziel_'.$p['fahrt_id'].' = new google.maps.LatLng('.str_replace(" ", ", ", $p["map_pin"] ).');
            var marker_'.$p['fahrt_id'].';
            var map_'.$p['fahrt_id'].';
            ';
    }
    echo'
            function initialize() {
            ';

    foreach($pins as $p){
        echo'
                var mapOptions_'.$p['fahrt_id'].' = {
                    zoom: 8,
                    center: ziel_'.$p['fahrt_id'].',
                    panControl: false,
                    zoomControl: false,
                    scaleControl: true,
                    mapTypeControl: false,
                    streetViewControl: false,
                    overviewMapControl: false
                };

                map_'.$p['fahrt_id'].' = new google.maps.Map(document.getElementById(\'map-canvas-'.$p['fahrt_id'].'\'), mapOptions_'.$p['fahrt_id'].');

                marker_'.$p['fahrt_id'].' = new google.maps.Marker({
                    map:map_'.$p['fahrt_id'].',
                    draggable:true,
                    animation: google.maps.Animation.DROP,
                    position: ziel_'.$p['fahrt_id'].'
                });

                marker_'.$p['fahrt_id'].'.setAnimation(google.maps.Animation.BOUNCE);';
    }
    echo'
            }

            google.maps.event.addDomListener(window, \'load\', initialize);

        </script>';

}
/**
 * @param $fahrt wenn array, dann Datenbankrow; wenn zahl, dann wird das selektiert
 */
function index_show_fahrtHeader_old($fahrt){
    global $index_db;
    if(!is_array($fahrt)){
        // select fahrt by ID
        $fahrt = $index_db->select('fahrten', array('fahrt_id','titel','ziel', 'von', 'bis', 'leiter', 'kontakt', 'beschreibung'), array('fahrt_id'=> $fahrt));
        if(!$fahrt){ index_show_alleFahrten(); return;}
        else  $fahrt = $fahrt[0];
    }
    $cnt = $index_db->count("bachelor", ["AND"=>
                                            ["backstepped" => NULL,
                                             "fahrt_id"    => $fahrt['fahrt_id']]]);
    echo '<div class="fahrt"><a  class="fahrthead" href="index.php?fid='.$fahrt['fahrt_id'].'">'.$fahrt['titel'].'</a>';
    echo 'Ziel: <i>'.$fahrt['ziel'].'</i><br />';
    echo 'Datum: <i>'.comm_from_mysqlDate($fahrt['von'])." - ".comm_from_mysqlDate($fahrt['bis']).'</i><br />';
    echo "Ansprechpartner: <i>".$fahrt['leiter']." (".comm_convert_mail($fahrt['kontakt']).")</i><br />";
    echo "Anmeldungen: <i>".$cnt."</i>";
    echo '<p>'.$fahrt['beschreibung'].'</p>
    </div>';
}

/**
 * show table of public registrations
 */
function index_show_signupTable($fid){
    global $index_db, $config_studitypen;

echo '<h2>Angemeldet</h2>';

    $data = $index_db->select('bachelor',array("pseudo","antyp","abtyp","anday","abday","comment","studityp"),
        array("AND" => array(
            'fahrt_id' => (int) $fid,
            'public'   => 1
        )));

    if(!$data) echo'<div class="signups">Noch keine (sichtbaren) Anmeldungen!</div>';
    else {
        echo '
            <table class="signups">
                <thead>
                    <tr>
                        <!--th></th-->
                        <th>Anzeigename</th>
                        <th>Anreisetag</th>
                        <th>Anreiseart</th>
                        <th>Abreisetag</th>
                        <th>Abreiseart</th>
                        <th>Kommentar</th>
                    </tr>
                </thead>';
        foreach($data as $d){
            echo '<tr>
                <!--td>'.$d["studityp"].'</td-->
                <td>'.$d["pseudo"].'</td>
                <td>'.comm_from_mysqlDate($d["anday"]).'</td>
                <td>'.index_show_signupTable_destroyTypes($d["antyp"]).'</td>
                <td>'.comm_from_mysqlDate($d["abday"]).'</td>
                <td>'.index_show_signupTable_destroyTypes($d["abtyp"]).'</td>
                <td>'.$d["comment"].'</td>
            </tr>';
        }
        echo '</table>';
    }
}

function index_show_signupTable_destroyTypes($anabtyp){
    global $config_reisearten, $config_reisearten_destroyed;
    if(array_search($anabtyp, $config_reisearten)>=2)
        return $config_reisearten_destroyed[array_rand($config_reisearten_destroyed)];
    return $anabtyp;
}