<?php

class AdminWlPage extends AdminPage {

    public function __construct($base) {
        parent::__construct($base);

        $db = $this->environment->database;
        $fid = $this->fahrt->getID();

        // deletes the entry completely
        if(isset($_REQUEST['delete'])){
            $delResult = $db->delete('bachelor', ['AND' => ['bachelor_id' => $_REQUEST['delete'], 'fahrt_id' => $fid]]);
            if (empty($delResult)) $this->message_err = 'Löschversuch versagt!';
            else $this->message_succ = 'Löschung geglückt.';
        }

        // moves entry to final list
        if(isset($_REQUEST['move'])){
            try {
                $bachelor = Bachelor::makeFromDB($this->fahrt, $_REQUEST['move']);
                $transferResult = $bachelor->waitlistToRegistration();
                if ($transferResult == Bachelor::SAVE_SUCCESS) {
                    $this->message_succ = 'Person erfolgreich von Warteliste auf Anmeldeliste übertragen.';
                } else {
                    throw new Exception('Hat nicht geklappt. Fehlercode '.$transferResult);
                }
            } catch (Exception $e) {
                $this->message_err = $e->getMessage();
            }
        }

    }

    public function getHeaders() {
        return '
            <link rel="stylesheet" type="text/css" href="../view/css/DataTables/css/jquery.dataTables.min.css" />
            <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
            <script type="text/javascript" src="../view/js/jquery.dataTables.1.10.9.min.js"></script>';
    }

    public function getHeader() {
        return '';
    }

    public function getFooter() {
        return '';
    }

    public function getText() {
        $people = $this->environment->database->select('bachelor',
            Bachelor::$ALLOWED_FIELDS,
            ['AND'=> ['on_waitlist'=> 1, 'fahrt_id'=>$this->fahrt->getID()]]);

        $columnFunctions = [
            'Anmelde-ID' => function($person) { return $person['bachelor_id']; },
            'Anmeldung' => function($person) { return date('d.m.Y', $person['anm_time']); },
            'Name' => function($person) { return '<a href="mailto:'.$person['mehl'].'?subject=FS-Fahrt">'. $person['forname'].' '.$person['sirname'].' ('.$person['pseudo'].')</a>'; },
            'Anreisetyp' => function($person) { return $person['antyp']; },
            'Abreisetyp' => function($person) { return $person['abtyp']; },
            'Anreisetag' => function($person) { return  $this->base->mysql2german( $person['anday']); },
            'Abreisetag' => function($person) { return $this->base->mysql2german( $person['abday']); },
            'Kommentar' => function($person) { return $person['comment']; },
            'StudiTyp' => function($person) { return $person['studityp']; },
            'Essen' => function($person) { global $config_essen_o; return array_search($person['essen'], $config_essen_o); },
            '18+' => function($person) { return (($person['virgin']==0) ? 'Ja' : 'Nein'); },
            'Uebertragen' => function($person) {
                if(!is_numeric($person['transferred']))
                    return '<a href="?page=wl&move='.$person['bachelor_id'].'">&#8614; übertragen</a>';
                else
                    return date('d.m.Y', $person['transferred']);
            }
        ];

        $toggles = 'Toggle Column: ';
        $thead = '';
        foreach(array_keys($columnFunctions) as $tcnt => $key){
            $toggles .= '<a class="toggle-vis" data-column="'.$tcnt.'">'.$key.'</a> - ';
            $thead .= '<th>'.$key.'</th>';
        }

        $tbody = '';
        foreach($people as $person) {
            $tbody .= '<tr>';
            foreach($columnFunctions as $key => $value){
                $tbody .= '<td class="'.$key.((strpos($columnFunctions['Uebertragen']($person), "href")>0) ? '' : ' list-backstepped').'">'.$value($person).'</td>';
            }
            $tbody .= '</tr>';
        }

        return '<h1>Warteliste</h1>'.
            $toggles.'<br />
            <table id="mlist" class="compact hover">
                <thead>
                    <tr>'.$thead.'</tr>
                </thead>
                <tbody>' .
                    $tbody .
                '</tbody>
            </table>

            <script type="text/javascript">
                jQuery.extend( jQuery.fn.dataTableExt.oSort, {
                    "link-pre": function ( a ) {
                        var tmp = a.match(/<a [^>]+>([^<]+)<\/a>/);
                        if(tmp) return a.match(/<a [^>]+>([^<]+)<\/a>/)[1];
                        else return a;
                    },
                    "dedate-pre": function(a){
                        var tmp = a.split(".");
                        console.log(tmp[2]+tmp[1]+tmp[0]);
                        if(tmp.length>2) return (tmp[2]+tmp[1]+tmp[0]);
                        return a;
                    }
                });
                var ltab;
                $(document).ready(function(){
                    ltab = $("#mlist").DataTable({
                        "columnDefs": [
                            { type: "dedate", targets: [1,5,6]},
                            { type: "link", targets: [2, 11] }
                        ],
                        "order": [[ 11, "desc" ], [1,"asc" ]],
                        "paging": false
                    });
                $("a.toggle-vis").click( function (e) {
                    e.preventDefault();
                    // Get the column API object
                    var column = ltab.column( $(this).attr("data-column") );
                    // Toggle the visibility
                    column.visible( ! column.visible() );
                } );
            });
        </script>';
    }

    public function getAjax() {
        return '';
    }
}

