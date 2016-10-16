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

        $bachelor = $this->environment->getBachelor(false, true);
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

        $this->show_formular_helper_input("Vorname", "forname", $bachelor["forname"], "");
        $this->show_formular_helper_input("Nachname", "sirname", $bachelor["sirname"], "");
        $this->show_formular_helper_input("Anzeigename", "pseudo", $bachelor["pseudo"], "");
        echo $soft_prot->add(array('forname', 'sirname', 'pseudo'), $this->environment->config['invalidChars'])->write();

        $this->show_formular_helper_input("E-Mail-Adresse", "mehl", $bachelor["mehl"], "regelmäßig lesen!");

        $this->show_formular_helper_sel("Du bist", "studityp", $this->environment->oconfig['studitypen'], $bachelor["studityp"], "");
        $this->show_formular_helper_sel("Alter 18+?", "virgin", array("", "Nein", "Ja"),
            isset($bachelor['virgin']) ? ($bachelor['virgin'] == 0 ? "Ja" : "Nein") : '', "Bist du älter als 18 Jahre?");
        $this->show_formular_helper_sel("Essenswunsch", "essen", $this->environment->oconfig['essen'], $bachelor["essen"], "Info für den Koch.");
        $this->show_formular_helper_sel2("Anreise", "anday", array_slice($possible_dates, 0, -1), $bachelor["anday"],
            "antyp", $this->environment->oconfig['reisearten'], $bachelor["antyp"], "");
        $this->show_formular_helper_sel2("Abreise", "abday", array_slice($possible_dates, 1), $bachelor["abday"],
            "abtyp", $this->environment->oconfig['reisearten'], $bachelor["abtyp"], "");

        echo '<label>Anmerkung</label>
            <textarea id="comment" name ="comment" rows="3" cols="50">' . $bachelor["comment"] . '</textarea>
            <input type="checkbox" name="public" value="public" style="width:40px"><span style="float:left">Anmeldung verstecken</span><br/>
            <div style="clear:both"></div>';

        $this->show_formular_helper_input("Captcha eingeben", "captcha", "", "");
        echo '<img src="view/captcha.php" /><br/>
            <button type="submit" name="submit" id="submit" value="submit">Anmelden!</button>
            <div class="spacer"></div>';

        echo '</form>
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
    private function show_formular_helper_sel($name, $id, $values, $selected, $subtext) {
        echo '<label>' . $name . '
        <span class="small">' . $subtext . '</span>
        </label>
        <select name="' . $id . '" id="' . $id . '">';
        foreach ($values as $val => $show) {
            echo '<option value="' . $val . '"';
            if ($val == $selected) echo ' selected';
            echo '>' . $show . '</option>';
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
    private function show_formular_helper_sel2($name, $id, $values, $selected, $id2, $values2, $selected2, $subtext) {
        echo '<label style="text-align:left">' . $name . '
        <span class="small">' . $subtext . '</span>
        </label><table style="float:left"><tr><td>
        <select name="' . $id . '" id="' . $id . '" style="width:110px; text-align: center">';
        foreach ($values as $val) {
            echo '<option value="' . $val . '"';
            if ($val == $selected) echo ' selected';
            echo '>' . $val . '</option>';
        }
        echo '</select></td><td><select name="' . $id2 . '" id="' . $id2 . '">';
        foreach ($values2 as $val => $show) {
            echo '<option value="' . $val . '"';
            if ($val == $selected2) echo ' selected';
            echo '>' . $show . '</option>';
        }
        echo '</select></td></tr></table>';
    }

    private function show_formular_helper_input($name, $id, $value, $subtext) {
        echo '<label>' . $name . '
        <span class="small">' . $subtext . '</span>
        </label>
        <input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" />';
    }
}

