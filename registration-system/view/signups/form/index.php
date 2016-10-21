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
            "version" => '1.2',
            "date" => '20.09.2014',
            "contributors" => ['Tim Repke <tim@repke.eu>']
        ];
    }

    public static function getLogo() {
        return 'graphics/hej.svg';
    }

    public static function getScore($stats) {
        return rand(30, 99);
    }

    public static function getBadgeDetails($stats) {
        return 'superspecial <br /> Detials';
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

    public function showInlineHTML() {
        $soft_prot = new soft_protect();

        $bachelor = $this->environment->getBachelor(false, true, true);
        $bachelorData = $bachelor->getData();
        $fahrt = $this->environment->getTrip();

        $possible_dates = $fahrt->getPossibleDates();
        $waitlist_mode = $this->environment->isInWaitlistMode();

        $link_params = $this->getFormSubmitBaseParams();

        if ($waitlist_mode)
            echo '<h1 style="color: red;">Warteliste</h1>
                  <p>Eintragen und hoffen...</p>';
        else
            echo '<h1>Anmeldeformular</h1>
                  <p>Bitte hier verbindlich anmelden.</p>';


        echo '<div id="stylized" class="myform">
                <form id="form" name="form" method="post" action="index.php' . $link_params . '">';

        $this->show_formular_helper_hidden_input('signupstats', (isset($bachelorData['signupstats']) ? $bachelorData['signupstats'] : null));

        $this->show_formular_helper_input('Vorname', 'forname', $bachelorData['forname'], '');
        $this->show_formular_helper_input('Nachname', 'sirname', $bachelorData['sirname'], '');
        $this->show_formular_helper_input('Anzeigename', 'pseudo', $bachelorData['pseudo'], '');
        echo $soft_prot->add(array('forname', 'sirname', 'pseudo'), $this->environment->config['invalidChars'])->write();
        $this->show_formular_helper_input('E-Mail-Adresse', 'mehl', $bachelorData['mehl'], 'regelmäßig lesen!');
        $this->show_formular_helper_sel('Du bist', 'studityp', $this->environment->oconfig['studitypen'], $bachelorData['studityp'], '');
        $this->show_formular_helper_sel('Alter 18+?', 'virgin', ['UNSET' => '', 'JA' => 'Ja', 'NEIN' => 'Nein'],
            isset($bachelorData['virgin']) ? ($bachelorData['virgin'] == 0 ? 'JA' : 'NEIN') : 'UNSET', 'Bist du älter als 18 Jahre?');
        $this->show_formular_helper_sel('Essenswunsch', 'essen', $this->environment->oconfig['essen'], $bachelorData['essen'], 'Info für den Koch.');
        $this->show_formular_helper_sel2('Anreise', 'anday', array_slice($possible_dates, 0, -1), $bachelorData['anday'],
            'antyp', $this->environment->oconfig['reisearten'], $bachelorData['antyp'], '');
        $this->show_formular_helper_sel2('Abreise', 'abday', array_slice($possible_dates, 1), $bachelorData['abday'],
            'abtyp', $this->environment->oconfig['reisearten'], $bachelorData['abtyp'], '');

        $this->show_formular_helper_sel("Du bist", "studityp", $this->environment->oconfig['studitypen'], $bachelorData["studityp"],
            "", 'width:45%; float:left;');
        $this->show_formular_helper_sel("Alter 18+?", "virgin", ['UNSET'=>'', 'JA'=>'Ja', 'NEIN' => 'Nein'],
            isset($bachelorData['virgin']) ? ($bachelorData['virgin'] == 0 ? "JA" : "NEIN") : 'UNSET',
            "Bist du älter als 18 Jahre?", 'width:45%; float:right;margin-right:-6px;');
        echo '<div style="clear:both;"></div>';

        $this->show_formular_helper_sel("Essenswunsch", "essen", $this->environment->oconfig['essen'], $bachelorData["essen"],
            "Info für den Koch.", 'width:45%; float:left;');
        echo '<div style="clear:both;"></div>';

        $this->show_formular_helper_sel2("Anreise", "anday", array_slice($possible_dates, 0, -1), $bachelorData["anday"],
            "antyp", $this->environment->oconfig['reisearten'], $bachelorData["antyp"], "",'width:45%; float:left;');
        $this->show_formular_helper_sel2("Abreise", "abday", array_slice($possible_dates, 1), $bachelorData["abday"],
            "abtyp", $this->environment->oconfig['reisearten'], $bachelorData["abtyp"], "",'width:45%; float:right;margin-right:-6px;');
        echo '<div style="clear:both;"></div>

            <label>Anmerkung</label>
            <textarea id="comment" name ="comment" rows="3" cols="50">' . $bachelorData["comment"] . '</textarea>
            <input type="checkbox" name="public" value="public" style="width:40px"><span style="float:left">Anmeldung verstecken</span><br/>
            <input type="checkbox" name="disclaimer" value="disclaimer" style="width:40px">
			<span style="float:left"><a style="text-decoration:underline;" target="_blank" href="'.$fahrt->get('disclaimlink').'">Disclaimer</a> gelesen und akzeptiert</span><br/> akzeptieren</span><br/>

            <div style="clear:both"></div>';

        $this->show_formular_helper_input("Captcha eingeben", "captcha", "", "",'width:45%; float:left;');
        echo '<img src="view/captcha.php" style="float:right; width: 45%;" /><div style="clear:both"></div>
            <button type="submit" name="submit" id="submit" value="submit">Anmelden!</button>
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
     * @param $style
     */
    private function show_formular_helper_sel($name, $id, $values, $selected, $subtext, $style = null) {
        $style = (empty($style) ? '' : 'style="'.$style.'"');

        echo '<div class="fieldbox" '.$style.'><label>' . $name . '
        <span class="small">' . $subtext . '</span>
        </label>
        <select name="' . $id . '" id="' . $id . '">';
        foreach ($values as $val => $show) {
            echo '<option value="' . $val . '"';
            if ($val == $selected) echo ' selected';
            echo '>' . $show . '</option>';
        }
        echo '</select></div>';
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
    private function show_formular_helper_sel2($name, $id, $values, $selected, $id2, $values2, $selected2, $subtext, $style=null) {
        $style = (empty($style) ? '' : 'style="'.$style.'"');

        echo '<div class="fieldbox" '.$style.'><label style="text-align:left">' . $name . '
        <span class="small">' . $subtext . '</span>
        </label>
        <select style="margin-bottom:0" name="' . $id . '" id="' . $id . '">';
        foreach ($values as $val) {
            echo '<option value="' . $val . '"';
            if ($val == $selected) echo ' selected';
            echo '>' . $val . '</option>';
        }
        echo '</select><select name="' . $id2 . '" id="' . $id2 . '">';
        foreach ($values2 as $val => $show) {
            echo '<option value="' . $val . '"';
            if ($val == $selected2) echo ' selected';
            echo '>' . $show . '</option>';
        }
        echo '</select></div>';
    }

    private function show_formular_helper_input($name, $id, $value, $subtext, $style=null) {
        $style = (empty($style) ? '' : 'style="'.$style.'"');
        echo '<div class="fieldbox" '.$style.'><label for="'.$id.'">' . $name . '
        <span class="small">' . $subtext . '</span>
        </label><br />
        <input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" />';
    }

    private function show_formular_helper_hidden_input($id, $value) {
        echo '<textarea style="display:none;"  name="' . $id . '" id="' . $id . '">' . $value . '</textarea>';
    }
}

