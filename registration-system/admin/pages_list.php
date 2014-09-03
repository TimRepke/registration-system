<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 9/3/14
 * Time: 7:09 PM
 */

global $text, $headers, $admin_db, $config_current_fahrt_id;
$headers =<<<END
    <link rel="stylesheet" type="text/css" href="../view/css/DataTables/css/jquery.dataTables.min.css" />
    <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../view/js/jquery.dataTables.min.js"></script>
END;
$text .= "Meldeliste";

$columns = array(
    "bachelor_id",
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
    "paid",
    "repaid",
    "backstepped"
);

$columnFunctions = array(
    "Anmelde-ID" => function($person) { return $person["bachelor_id"]; }
    //,"FahrtID" => function($person) { return $person["fahrt_id"]; }
,"Anmeldung" => function($person) { return date("m.d.Y", $person['anm_time']); },
    "Name" => function($person) { return "<a href='mailto:".$person["mehl"]."?subject=FS-Fahrt'>".$person["forname"]." ".$person["sirname"]." (".$person["pseudo"].")</a>"; },
    "Anreisetyp" => function($person) { return $person["antyp"]; },
    "Abreisetyp" => function($person) { return $person["abtyp"]; },
    "Anreisetag" => function($person) { return  date("m.d.Y", $person["anday"]); },
    "Abreisetag" => function($person) { return date("m.d.Y", $person["abday"]); },
    "Kommentar" => function($person) { return $person["comment"]; },
    "StudiTyp" => function($person) { return $person["studityp"]; },
    "PaidReBack" => function($person) { return ($person["paid"] ? "1" : "0") . ($person["repaid"] ? "1" : "0") . ($person["backstepped"] ? "1" : "0"); }
);

$text .=<<<END
    <table id="mlist">
        <thead>
            <tr>
END;
foreach($columnFunctions as $key => $value)
{
    $text .= "<th>".$key."</th>";
}
$text .=<<<END
            </tr>
        </thead>
        <tbody>
END;

$people = $admin_db->select('bachelor',$columns, array("fahrt_id"=>$config_current_fahrt_id));
foreach($people as $person) {
    $text .= "<tr>";
    foreach($columnFunctions as $key => $value)
    {
        $text .= "<td class='".$key."'>".$value($person)."</td>";
    }
    $text .= "</tr>";
}

$text .=<<<END
        </tbody>
    </table>
    <script type='text/javascript'>
        $(document).ready(function(){
            $('#mlist').dataTable({});

            $('.PaidReBack').each(function(){
                $(this).text(function(){
                    var btns = "";
                    for(var i = 0, txt = $(this).text(); i < txt.length; i++){
                        btns += txt[i]+",";
                    }
                    return btns;
                });
            });
        });
    </script>
END;
