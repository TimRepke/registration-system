<?php

require_once("../frameworks/medoo.php");
require_once("../config.inc.php");

function page_stuff()
{
    global $text;
    $text .= "Übersichtsseite";
}

function page_list()
{
    global $text, $headers, $admin_db;
    $headers =<<<END
    <link rel="stylesheet" type="text/css" href="../view/css/DataTables/css/jquery.dataTables.min.css" />
    <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../view/js/jquery.dataTables.min.js"></script>
END;
    $text .= "Meldeliste";

    $columns = array(
        "bachelor_id",
        "fahrt_id",
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
        "Anmelde-ID" => function($person) { return $person["bachelor_id"]; },
        "FahrtID" => function($person) { return $person["fahrt_id"]; },
        "Name" => function($person) { return "<a href='mailto:".$person["mehl"]."?subject=FS-Fahrt'>".$person["forname"]." ".$person["sirname"]." (".$person["pseudo"].")</a>"; },
        "Anreisetyp" => function($person) { return $person["antyp"]; },
        "Abreisetyp" => function($person) { return $person["abtyp"]; },
        "Anreisetag" => function($person) { return $person["anday"]; },
        "Abreisetag" => function($person) { return $person["abday"]; },
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
    // TODO: generate table content

    $people = $admin_db->select('bachelor',$columns);
    foreach($people as $person) {
    	$text .= "<tr>";
    	foreach($columnFunctions as $key => $value)
        {
            $text .= "<td>".$value($person)."</td>";
        }
    	$text .= "</tr>";
    }

    $text .=<<<END
        </tbody>
    </table>
    <script type='text/javascript'>
        $(document).ready(function(){
            $('#mlist').dataTable({});
        });
    </script>
END;

}

function page_404($pag)
{
    global $text;
    $text .='
        <div style="background-color:black; color:antiquewhite; font-family: \'Courier New\', Courier, monospace;height: 100%; width: 100%;position:fixed; top:0; padding-top:40px;">
            $ get-page '.$pag.'<br />
            404 - page not found ('.$pag.')<br />
            $ <blink>&#9611;</blink>
        </div>';

}

function page_notes(){
    require_page("pages_notes.php");
}

function page_mail(){
    require_page("pages_mail.php");
}

function page_cost(){
    require_page("pages_cost.php");
}

function require_page($page){
    if(!@file_exists($page) ) {
        page_404($page);
    } else {
        require_once $page;
    }
}

?>
