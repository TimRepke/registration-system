<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 10/2/14
 * Time: 7:53 PM
 */
error_reporting(E_ALL | E_STRICT);
ini_set("display_errors",1);
global $text, $headers, $admin_db, $config_current_fahrt_id, $ajax, $config_studitypen, $config_essen, $config_reisearten, $config_essen_o, $config_reisearten_o, $config_baseurl;

require_once("../frameworks/commons.php");

// deletes the entry completely
if(isset($_REQUEST['delete'])){
    $admin_db->delete("bachelor", ["bachelor_id"=> $_REQUEST['delete']]);
}

// moves entry to final list
if(isset($_REQUEST['move'])){
    $transdata = $admin_db->get("waitlist", [
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
        "essen"],
        ["AND" => [
            "waitlist_id" => $_REQUEST['move'],
            "fahrt_id"    => $config_current_fahrt_id
        ]]
    );
    $tinsert = $tupdate = NULL;
    if($transdata){
        $transdata['bachelor_id'] = $_REQUEST['move'];

        $duplicate = FALSE;
        if($admin_db->has("bachelor", ["AND" =>[
                                         "bachelor_id" => $_REQUEST['move'],
                                         "fahrt_id"    => $config_current_fahrt_id]]))
            $duplicate = TRUE;
        else{
            $tinsert = $admin_db->insert("bachelor", $transdata);
            $tupdate = $admin_db->update("waitlist", ["transferred" => time()], ["AND" => [
                                                                                    "waitlist_id" => $_REQUEST['move'],
                                                                                    "fahrt_id"    => $config_current_fahrt_id
                                                                                ]]);
        }
    }

    if(!$transdata || is_null($tinsert) || is_null($tupdate) || $duplicate)
        $text .= '<div style="color:red;">Some error at transfer...</div>';
    else{
        $text .= '<div style="color:green;">Transfer seems successfull, sending automatic mail now to '.$transdata['mehl'].'</div>';

        // === notify success ===
        $from = $admin_db->get("fahrten", array("kontakt","leiter"), array("fahrt_id"=>$transdata['fahrt_id']));
        $mail = comm_get_lang("lang_waittoregmail", array( "{{url}}" => $config_baseurl."status.php?hash=".$_REQUEST['move'],
                                                           "{{organisator}}" => $from['leiter']));
        comm_send_mail($admin_db, $transdata['mehl'], $mail, $from['kontakt']);
    }

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
        "Anmelde-ID" => function($person) { return $person["waitlist_id"]; }
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
                    return "<a href='?page=wl&move=".$person["waitlist_id"]."'>&#8614; übertragen</a>";
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
            $text .= "<td class='".$key.((strpos($columnFunctions['Uebertragen']($person), "href")>0) ? '' : ' list-backstepped')."'>".$value($person)."</td>";
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
                var tmp = a.match(/<a [^>]+>([^<]+)<\/a>/);
                if(tmp)
                    return a.match(/<a [^>]+>([^<]+)<\/a>/)[1];
                else
                    return a;
            }
            ,
            "dedate-pre": function(a){
                var tmp = a.split(".");
                console.log(tmp[2]+tmp[1]+tmp[0]);
                if(tmp.length>2)
                    return (tmp[2]+tmp[1]+tmp[0]);
                return a;
            }
        } );
        var ltab;
        $(document).ready(function(){
             ltab = $('#mlist').DataTable({
                "columnDefs": [
                    { type: 'dedate', targets: [1,5,6]},
                    { type: 'link', targets: [2, 11] }
                ],
                "order": [[ 11, "desc" ], [1,"asc" ]],
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

