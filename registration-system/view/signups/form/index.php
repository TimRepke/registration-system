<?php

class FormSignupMethod extends SignupMethod {


    public static function getName() {
        return "Langweiliges Formular";
    }

    public static function getAltText() {
        return "Seite zu bunt? Kein JavaScript? Oder einfach nur Langweiler?";
    }

    public static function getMetaInfo() {
        return [
            "version"      => '1.1',
            "date"         => '20.09.2014',
            "contributors" => ['Tim Repke <tim@repke.eu>']
        ];
    }

    public function getJSDependencies() {
        return [];
    }

    public function getCSSDependencies() {
        return ['style.css'];
    }

    public function getAdditionalHeader() {
        return '';
    }

    public function getInlineHTML() {

        $environment = Environment::getEnv();

        // TODO: wartelisten note

        $possible_dates = comm_get_possible_dates($environment->database, $environment->getSelectedTrip());

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
}

