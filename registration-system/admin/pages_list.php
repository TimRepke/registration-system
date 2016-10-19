<?php

class AdminListPage extends AdminPage {

    public function __construct($base) {
        parent::__construct($base);

        if (isset($_REQUEST['change'])) {
            try {
                $b = Bachelor::makeFromForm(false, $this->fahrt, true, true);
                $b->set(['bachelor_id' => $_REQUEST['change']]);
                $saveResult = $b->save();
                if ($saveResult !== Bachelor::SAVE_SUCCESS)
                    throw new Exception('Fehler beim Speichern mit code ' . $saveResult.'<br />'.implode('<br />', $b->getValidationErrors()));
                else
                    $this->message_succ = 'Bachelor mit ID '.$_REQUEST['change'].' Erfolgreich gespeichert';
            } catch (Exception $e) {
                $this->message_err = $e->getMessage();
            }
        }

        if (isset($_REQUEST['delete'])) {
            $this->environment->database->delete('bachelor', ['AND' => ['bachelor_id' => $_REQUEST['delete'], 'fahrt_id'=>$_REQUEST['fahrt_id']]]);
        }
    }

    public function getHeaders() {
        return '<link rel="stylesheet" type="text/css" href="../view/css/DataTables/css/jquery.dataTables.min.css" />
                <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
                <script type="text/javascript" src="../view/js/jquery.dataTables.1.10.12.min.js"></script>
                <style type="text/css">
                    div.btn{
                        width: 18px;
                        height: 18px;
                        padding: 3px 5px;
                        background-image: url("../view/graphics/MyEbaySprite.png");
                        background-repeat: no-repeat;
                        float: left;
                        cursor: pointer;
                    }
                    .btn-paid-0{
                        background-position: -23px -90px;
                    }
                    .btn-paid-1{
                        background-position: -70px -90px;
                    }
                    .btn-repaid-0{
                        background-position: -148px -89px;
                    }
                    .btn-repaid-1{
                        background-position:-194px -89px;
                    }
                    .btn-backstepped-0{
                        background-position: -51px -169px;
                    }
                    .btn-backstepped-1{
                        background-position: -23px -169px;
                    }

                    #editForm{
                        display: none;
                        position: fixed;
                        top:100px;
                        left: 200px;
                        width: 700px;
                        height: 80%;
                        border: 1px solid #000000;
                        background-color: beige;
                        padding: 20px 10px 10px 10px;
                    }

                    #editForm>p{
                        display: block;
                        position:absolute;
                        height:auto;
                        bottom:0;
                        top:0;
                        left:0;
                        right:0;
                        overflow: auto;
                        padding: 10px;
                        margin: 20px 0 0 0;
                    }
                    #editFormTopbar{
                        background-color: #b0bed9;
                        height: 20px;
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        padding: 0;
                    }
                    #editFormTopbar p{
                        position: absolute;
                        float: right;
                        top: 0;
                        padding: 0;
                        margin: 0;
                        right: 5px;
                        cursor: none;
                        height: 20px;
                        display: block;
                    }
                </style>';
    }

    public function getHeader() {
        return '';
    }

    public function getFooter() {
        return '';
    }

    public function getText() {
        $cols = ['Anmelde-ID','Anmeldung','Name','Anreisetyp','Abreisetyp', 'Anreisetag','Abreisetag',
            'Kommentar','Studityp','Essen', '18+','PaidReBack'];
        $buttoncol = count($cols)-1;

        $thead = '';
        $toggle = 'Toggle Column:';
        foreach ($cols as $tcnt => $col) {
            $thead .= '<th>'.$col.'</th>';
            $toggle .= '<a class="toggle-vis" data-column="' . $tcnt . '">' . $col . '</a> - ';
        }

        $people = $this->fahrt->getBachelors(['waiting'=>false]);
        $tbody = '';
        foreach ($people as $b) {
            $tbody .= '
                <tr>
                    <td><a href="#" class="edit_bachelor">'.$b['bachelor_id'].'</a></td>
                    <td>'.$this->mysql2german($b['anm_time']).'</td>
                    <td><a href="mailto:'.$b['mehl'].'?subject=FS-Fahrt">' . $b['forname'] . ' ' . $b['sirname'] . ' (' . $b['pseudo'] . ')</a></td>
                    <td>'.$b['antyp'].'</td>
                    <td>'.$b['abtyp'].'</td>
                    <td>'.$this->mysql2german($b['anday']).'</td>
                    <td>'.$this->mysql2german($b['abday']).'</td>
                    <td>'. htmlspecialchars($b['comment'], ENT_QUOTES).'</td>
                    <td>'.$b['studityp'].'</td>
                    <td>'.$b['essen'].'</td>
                    <td>'.($b['virgin']==0 ? 'Ja' : 'Nein').'</td>
                    <td>'.($b['paid'] ? $b['paid'] : '0') . ',' . ($b['repaid'] ? $b['repaid'] : '0') . ',' . ($b['backstepped'] ? $b['backstepped'] : '0').'</td>
                    <td>'.($b['backstepped'] ? 1 : '0').'</td>
                </tr>';
        }
        
        return '<h1>Meldeliste</h1>' .
        $this->getMessage().'<br />' .
        $toggle.'<br />
        <br />
        <table id="mlist" class="compact hover">
            <thead>
                <tr>'.$thead.'<th></th></tr>
            </thead>
            <tbody>'.$tbody.'</tbody>
        </table>
        <div id="editForm">
            <div id="editFormTopbar"><p>X</p></div>
            <p></p>
        </div>
        <script type="text/javascript">
            jQuery.extend( jQuery.fn.dataTableExt.oSort, {
                "link-pre": function ( a ) {
                    return a.match(/<a [^>]+>([^<]+)<\/a>/)[1];
                },
                "prb-pre": function ( a ) {
                    var tmp = a.split(",");
                    return ((tmp[0]==0) ? "0" : "1") + ((tmp[1]==0) ? "0" : "1") + ((tmp[2]==0) ? "0" : "1");
                },
                "dedate-pre": function( a ) {
                    var tmp = a.split(".");
                    if(tmp.length>2)
                        return (tmp[2]+tmp[1]+tmp[0]);
                    return a;
                }
            });
            var ltab;
            $(document).ready(function(){
                ltab = $("#mlist").DataTable({
                    "rowCallback": function (row, data, index) {
                        if (data['.$buttoncol.'].split(",")[2] != 0) {
                            $("td", row).addClass("list-backstepped");
                        }
                    },
                    "columnDefs": [
                        {
                            "targets": ['.$buttoncol.'],
                            "render": function(data, type, row, meta) {
                                if (type === "display"){
                                    var bid = row[0].match(/<a [^>]+>([^<]+)<\/a>/)[1];
                                    var classes = ["paid", "repaid", "backstepped"];
                                    var btns = "";
                                    var parts = data.split(",");
                                    for (var i = 0; i < parts.length; i++) {
                                        var tmp = (parts[i] ==0) ? 0 : 1;
                                        btns +="<div onclick=\\"btnclick(this, \'"+classes[i]+"\',\'"+bid+"\',"+tmp+");\\" class=\\"btn btn-"+classes[i]+"-"+tmp+"\\">&nbsp;</div>";
                                    }
                                    return btns;
                                }
                                return data;
                            }
                        },
                        { type: "dedate", targets: [1,5,6]},
                        { type: "link", targets: [0, 2] },
                        { type: "prb", targets: ' . $buttoncol . ' },
                        { targets: 12, visible: false, searchable: false }
                    ],

                    "order": [[ 2, "asc" ]],
                    "paging": false,
                    "orderFixed": [ 12, "asc" ]
                });

                $("a.toggle-vis").click( function (e) {
                    e.preventDefault();

                    // Get the column API object
                    var column = ltab.column( $(this).attr("data-column") );

                    // Toggle the visibility
                    column.visible( ! column.visible() );
                });
                $(".edit_bachelor").click( function(){
                    var bid = $(this).text();
                    $.get( "?page=list&ajax=ajax&form=form&hash="+bid, function( data ) {
                        $("#editForm > p").html(data);
                    });

                    $("#editForm").show();
                });

                $("#editFormTopbar > p").click( function(){
                    $(this).parent().parent().hide();
                });
            });

            function btnclick(that, type, hash, state){
                var newstate = (((state-1)<0) ? 1 : 0);
                $.get("index.php?page=list&ajax=ajax&update="+type+"&hash="+hash+"&nstate="+newstate ,"",
                    function(){
                        if(newstate === 1 && type === "backstepped") {
                            $("td", $(that).parent().parent()).addClass("list-backstepped");
                        } else {
                            $("td", $(that).parent().parent()).removeClass("list-backstepped");
                        }
                        that.className="btn btn-"+type+"-"+newstate;
                        that.setAttribute("onclick", "btnclick(this, \'"+type+"\', \'"+hash+"\', "+newstate+")");
                    });
            }
        </script>';
    }

    public function getAjax() {
        if (isset($_REQUEST['update']) && isset($_REQUEST['hash']) && isset($_REQUEST['nstate'])) {
            $b = Bachelor::makeFromDB($this->fahrt, $_REQUEST['hash']);
            $b->set([$_REQUEST['update'] => ($_REQUEST['nstate'] == 1) ? time() : null]);
            return $b->save();
        } elseif (isset($_REQUEST['form'])) {
            return $this->getEditForm(Bachelor::makeFromDB($this->fahrt, $_REQUEST['hash']));
        }
    }

    private function nulltime2german($time) {
        if (empty($time)) return 'Nein';
        return $this->mysql2german($time);
    }

    /**
     * @param $bachelor Bachelor
     * @return string
     */
    private function getEditForm($bachelor) {
        $data = $bachelor->getData();
        $bid = $data['bachelor_id'];
        $fid = $data['fahrt_id'];
        $possibleDates = $this->fahrt->getPossibleDates();

        return '
            <b>Hash:</b> ' . $bid . '<br/>
            <b>Fahrt:</b> ID ' . $fid . '<br/>
            <b>Anmeldung:</b> ' . $this->mysql2german($data['anm_time']) . '<br/>
            <b>Bezahlt:</b> ' . $this->nulltime2german($data['paid']) . '<br/>
            <b>Rückgezahlt:</b> ' . $this->nulltime2german($data['repaid']) . '<br/>
            <b>Zurückgetreten:</b> ' . $this->nulltime2german($data['backstepped']) . '<br/>
            <br />
            <div id="stylized" class="myform">
                <form id="form" name="form" method="post" action="?page=list">
                    <input type="hidden" value="' . $bid . '" name="change" id="change" />
                    <input type="hidden" value="' . $fid . '" name="fahrt_id" id="fahrt_id" />' .
        $this->getFormInput('Vorname', 'forname', $data['forname'], '') .
        $this->getFormInput('Nachname', 'sirname', $data['sirname'], '') .
        $this->getFormInput('Anzeigename', 'pseudo', $data['pseudo'], '') .
        $this->getFormInput('E-Mail', 'mehl', $data['mehl'], 'regelmäßig lesen!') .
        $this->getFormSel('Er/Sie/Es ist', 'studityp', array_keys($this->environment->oconfig['studitypen']), $data['studityp'], '') .
        $this->getFormSel('Alter 18+?', 'virgin', ['Ja', 'Nein'], ($data['virgin'] == 1) ? 'Nein' : 'Ja', 'Älter als 18?') .
        $this->getFormSel('Essenswunsch', 'essen', array_keys($this->environment->oconfig['essen']), $data['essen'], 'Info für den Koch') .
        '<div style="clear:both;"></div>' .
        $this->getFormSel2('Anreise', 'anday', array_slice($possibleDates, 0, -1), $this->mysql2german($data['anday']),
            'antyp', array_keys($this->environment->oconfig['reisearten']), $data['antyp'], '') .
        $this->getFormSel2('Abreise', 'abday', array_slice($possibleDates, 1), $this->mysql2german($data['abday']),
            'abtyp', array_keys($this->environment->oconfig['reisearten']), $data['abtyp'], '') .
        '<label>Anmerkung</label>
                    <textarea id="comment" name="comment" rows="3" cols="40">' . $data['comment'] . '</textarea>
                    <input type="checkbox" name="public" value="public" style="width:40px" ' . (($data['public'] == 0 ? " checked" : "")) . '>
                    <span style="float:left">Anmeldung verstecken</span><br/>
                    <div style="clear:both"></div>
                    Note: No check for validity of data here!
                    <button type="submit" name="submit" id="submit" value="submit">Ändern!</button>
                    <div class="spacer"></div>
                </form>
            </div>
            <br /><hr /> <br/>
            <form method="POST" >
                Note: Keine Nachfrage, löscht direkt und unwiederruflich!!<br />
                <input type="submit" name="submit_del" value="DELETE" />
                <input type="hidden" name="delete" value="' . $bid . '" />
                <input type="hidden" name="bachelor_id" value="' . $bid . '" />
                <input type="hidden" name="fahrt_id" value="' . $fid . '" />
            </form>';
    }
}
