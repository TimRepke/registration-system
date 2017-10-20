<?php

class AdminMailPage extends AdminPage {

    private $mails;

    public function __construct($base) {
        parent::__construct($base);
        $this->mails = $this->environment->database->select('bachelor',
            ['mehl', 'forname', 'sirname'],
            $this->buildQueryWhere());
    }

    public function getHeaders() {
        return '<script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
             <script type="text/javascript" src="../view/js/jquery-ui.min.js"></script>';
    }

    public function getHeader() {
        return '';
    }

    public function getFooter() {
        return '';
    }

    public function getText() {
        $studitypen = '';
        foreach ($this->environment->oconfig['studitypen'] as $key => $typ) {
            $studitypen .= '<option value="' . $key . '">' . $typ . '</option>';
        }
        $reisearten = '';
        foreach ($this->environment->oconfig['reisearten'] as $key => $typ) {
            $reisearten .= '<option value="' . $key . '">' . $typ . '</option>';
        }
        $essen = '';
        foreach ($this->environment->oconfig['essen'] as $key => $typ) {
            $essen .= '<option value="' . $key . '">' . $typ . '</option>';
        }

        $fahrt_bereich = $this->fahrt->getPossibleDates();
        $tage = '';
        for ($i = 0; $i < sizeof($fahrt_bereich)-1; $i += 1)
            $tage .= '<option value="' . date('Y-m-d', strtotime($fahrt_bereich[$i])) . '">' . $fahrt_bereich[$i] . '</option>';

        return '
            <script type="text/javascript">
            $(function(){
                $("#mform").submit(function(event){
                    event.preventDefault();
                    var str = $("#mform").serialize();
                    str += "&submit=submit&ajax=ajax";
                    $.post(document.url, str, function(data){
                            $("#mails").html(data);
                        }, "text");
                    $("#mails").fadeOut().delay(50).fadeIn();
                });
            });
            </script>
            <form method="POST" id="mform">
                <table>
                    <tr>
                        <td>Studityp</td>
                        <td>Anreise</td>
                        <td>Abreise</td>
                        <td>Nächte</td>
                        <td>Essen</td>
                        <td>Gezahlt</td>
                        <td>Rückgezahlt</td>
                        <td>18+</td>
                        <td>Zurückgetreten</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="check_studityp" /></td>
                        <td><input type="checkbox" name="check_antyp" /></td>
                        <td><input type="checkbox" name="check_abtyp" /></td>
                        <td><input type="checkbox" name="check_nights" /></td>
                        <td><input type="checkbox" name="check_essen" /></td>
                        <td><input type="checkbox" name="check_paid" /></td>
                        <td><input type="checkbox" name="check_repaid" /></td>
                        <td><input type="checkbox" name="check_virgin" /></td>
                        <td><input type="checkbox" name="check_backstepped" /></td>
                    </tr>
                    <tr>
                        <td>
                            <select multiple name="val_studityp[]">' . $studitypen . '</select>
                        </td>
                        <td>
                            <select multiple name="val_antyp[]">' . $reisearten . '</select>
                        </td>
                        <td>
                            <select multiple name="val_abtyp[]">' . $reisearten . '</select>
                        </td>
                        <td>
                            <select multiple name="val_nights[]">' . $tage . '</select>
                        </td>
                        <td>
                            <select multiple name="val_essen[]">' . $essen . '</select>
                        </td>
                        <td>
                            <select name="val_paid">
                                <option value="1">Ja</option>
                                <option value="0">Nein</option>
                            </select>
                        </td>
                        <td>
                            <select name="val_repaid">
                                <option value="1">Ja</option>
                                <option value="0">Nein</option>
                            </select>
                        </td>
                        <td>
                            <select name="val_virgin">
                                <option value="1">Ja</option>
                                <option value="0">Nein</option>
                            </select>
                        </td>
                        <td>
                            <select name="val_backstepped">
                                <option value="1">Ja</option>
                                <option value="0">Nein</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <input type="submit" name="submit">
            </form>
            <textarea style="height:300px; width:800px" id="mails">'.$this->transformContacts().'</textarea>';
    }

    public function getAjax() {
        return $this->transformContacts();
    }

    private function transformContacts() {
        return join('', array_map(function($mehl) {
            return $mehl['forname'] . " " . $mehl['sirname'] . " <" . $mehl['mehl'] . ">; ";
        }, $this->mails));
    }

    private function buildQueryWhere() {
        $where = ['fahrt_id' => $this->fahrt->getID(), 'OR #waitlist' => ['on_waitlist' => 0,
            'AND' => [
                'transferred[!]' => null,
                'on_waitlist' => 1
            ]]];
        if (isset($_REQUEST['submit'])) {
            if (isset($_REQUEST['check_studityp'])) {
                $where['studityp'] = $_REQUEST['val_studityp'];
            }
            if (isset($_REQUEST['check_antyp'])) {
                $where['antyp'] = $_REQUEST['val_antyp'];
            }
            if (isset($_REQUEST['check_abtyp'])) {
                $where['abtyp'] = $_REQUEST['val_abtyp'];
            }
            if (isset($_REQUEST['check_nights'])) {
                $nights = $_REQUEST['val_nights'];
                $conditions = [];
                foreach ($nights as $night)
                    $conditions['AND #'.$night] = ['anday[<=]' => $night, 'abday[>]' => $night];
                if (sizeof($conditions) > 0)
                    $where['OR #nights'] = $conditions;
            }
            if (isset($_REQUEST['check_essen'])) {
                $where['essen'] = $_REQUEST['val_essen'];
            }
            if (isset($_REQUEST['check_paid'])) {
                $where['paid' . ($_REQUEST['val_paid'] == 1 ? '[!]' : '')] = null;
            }
            if (isset($_REQUEST['check_repaid'])) {
                $where['repaid' . ($_REQUEST['val_repaid'] == 1 ? '[!]' : '')] = null;
            }
            if (isset($_REQUEST['check_virgin'])) {
                $where['virgin'] = $_REQUEST['val_virgin'];
            }
            if (isset($_REQUEST['check_backstepped'])) {
                $where['backstepped' . ($_REQUEST['val_backstepped'] == 1 ? '[!]' : '')] = null;
            }
        }
        return ['AND' => $where];
    }
}
