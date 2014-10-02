<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 10/2/14
 * Time: 7:53 PM
 */

global $text, $headers, $admin_db, $config_current_fahrt_id, $ajax, $config_studitypen, $config_essen, $config_reisearten, $config_essen_o, $config_reisearten_o;

// deletes the entry completely
if(isset($_REQUEST['delete'])){
    $admin_db->delete("bachelor", ["bachelor_id"=> $_REQUEST['delete']]);
}

// moves entry to final list
if(isset($_REQUEST['move'])){

}





$headers =<<<END
    <link rel="stylesheet" type="text/css" href="../view/css/DataTables/css/jquery.dataTables.min.css" />
    <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../view/js/jquery.dataTables.min.js"></script>
END;

    $text .= "<h1>Warteliste</h1>";

    $columns = array(
        "waitlist_id",
        "fahrt_id",
        "anm_time",
        "forname",
        "sirname",
        "mehl",
        "pseudo",
        "antyp",
        "abtyp",
        "anday",
        "abday",
        "comment",
        "studityp",
        "virgin",
        "essen",
        "transferred"
    );

    $columnFunctions = array(
        "Anmelde-ID" => function($person) { return $person["bachelor_id"]; }
        //,"FahrtID" => function($person) { return $person["fahrt_id"]; }
        ,"Anmeldung" => function($person) { return date("d.m.Y", $person['anm_time']); },
        "Name" => function($person) { return "<a href='mailto:".$person["mehl"]."?subject=FS-Fahrt'>".$person["forname"]." ".$person["sirname"]." (".$person["pseudo"].")</a>"; },
        "Anreisetyp" => function($person) { global $config_reisearten_o; return array_search($person["antyp"], $config_reisearten_o); },
        "Abreisetyp" => function($person) { global $config_reisearten_o; return array_search($person["abtyp"], $config_reisearten_o); },
        "Anreisetag" => function($person) { return  comm_from_mysqlDate( $person["anday"]); },
        "Abreisetag" => function($person) { return comm_from_mysqlDate( $person["abday"]); },
        "Kommentar" => function($person) { return $person["comment"]; },
        "StudiTyp" => function($person) { return $person["studityp"]; },
        "Essen" => function($person) { global $config_essen_o; return array_search($person["essen"], $config_essen_o); },
        "18+" => function($person) { return (($person["virgin"]==0) ? "Ja" : "Nein"); },
        "Uebertragen" => function($person) {
                if(!is_numeric($person["transferred"]))
                    return "<a href='?page=wl&move=".$person["bachelor_id"]."'>&#8614; übertragen</a>";
                else
                    return date("d.m.Y", $person['transferred']);
            }
    );

    $text .= "Toggle Column: ";
    $tcnt = 0;
    foreach($columnFunctions as $key => $value){
        $text .= '<a class="toggle-vis" data-column="'.$tcnt.'">'.$key.'</a> - ';
        $tcnt++;
    }
    $text .= "<br />";

    $text .= '
    <table id="mlist" class="compact hover">
        <thead>
            <tr>';

    foreach($columnFunctions as $key => $value)
    {
        $text .= "<th>".$key."</th>";
    }
    $text .= '
            </tr>
        </thead>
        <tbody>';

    $people = $admin_db->select('waitlist',$columns, array("fahrt_id"=>$config_current_fahrt_id));

    foreach($people as $person) {
        $text .= "<tr>";
        foreach($columnFunctions as $key => $value){
            $text .= "<td class='".$key.((!strpos($columnFunctions['Uebertragen']($person), "<")) ? '' : ' list-backstepped')."'>".$value($person)."</td>";
        }
        $text .= "</tr>";
    }

    $buttoncol = 11;
    $text .=<<<END
        </tbody>
    </table>
    <script type='text/javascript'>
        jQuery.extend( jQuery.fn.dataTableExt.oSort, {
            "link-pre": function ( a ) {
                return a.match(/<a [^>]+>([^<]+)<\/a>/)[1];
            }
        } );
        var ltab;
        $(document).ready(function(){
             ltab = $('#mlist').DataTable({
                "columnDefs": [
                    { type: 'link', targets: 2 },
                    { type: 'link', targets: 11 }
                ],
                "order": [[ 11, "asc" ], [1,"asc" ]],
                "paging": false
            });

            $('a.toggle-vis').click( function (e) {
                e.preventDefault();

                // Get the column API object
                var column = ltab.column( $(this).attr('data-column') );

                // Toggle the visibility
                column.visible( ! column.visible() );
            } );

        });

    </script>
END;

