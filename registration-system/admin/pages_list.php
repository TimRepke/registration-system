<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 9/3/14
 * Time: 7:09 PM
 */


global $text, $headers, $admin_db, $config_current_fahrt_id, $ajax;

if(isset($_REQUEST['ajax'])){

    if(isset($_REQUEST['update']) && isset($_REQUEST['hash']) && isset($_REQUEST['nstate'])){
        $col = $_REQUEST['update'];
        $id  = $_REQUEST['hash'];
        $val = ($_REQUEST['nstate'] == 1) ? time() : NULL;

        $admin_db->update("bachelor", array($col=>$val), array("bachelor_id"=> $id));
    }

} else {
$headers =<<<END
    <link rel="stylesheet" type="text/css" href="../view/css/DataTables/css/jquery.dataTables.min.css" />
    <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../view/js/jquery.dataTables.min.js"></script>
END;
$headers .= "
<style type='text/css'>
div.btn{
    width: 18px;
    height: 18px;
    padding: 3px 5px;
    background-image: url('../view/graphics/MyEbaySprite.png');
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
    height: 800px;
    overflow: auto;
    border: 1px solid #000000;
    background-color: #b0bed9;
}
</style>";

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
,"Anmeldung" => function($person) { return date("d.m.Y", $person['anm_time']); },
    "Name" => function($person) { return "<a href='mailto:".$person["mehl"]."?subject=FS-Fahrt'>".$person["forname"]." ".$person["sirname"]." (".$person["pseudo"].")</a>"; },
    "Anreisetyp" => function($person) { return $person["antyp"]; },
    "Abreisetyp" => function($person) { return $person["abtyp"]; },
    "Anreisetag" => function($person) { return  comm_from_mysqlDate( $person["anday"]); },
    "Abreisetag" => function($person) { return comm_from_mysqlDate( $person["abday"]); },
    "Kommentar" => function($person) { return $person["comment"]; },
    "StudiTyp" => function($person) { return $person["studityp"]; },
    "PaidReBack" => function($person) { return ($person["paid"] ? $person["paid"] : "0") .",". ($person["repaid"] ? $person["repaid"] : "0") .",". ($person["backstepped"] ? $person["backstepped"] : "0"); }
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
    $text .= "<tr>"; //".((explode(',',$columnFunctions['PaidReBack']($person))[2]==0) ? "" : "class='list-backstepped'")."
    foreach($columnFunctions as $key => $value)
    {
        $text .= "<td class='".$key.((explode(',',$columnFunctions['PaidReBack']($person))[2]==0) ? '' : ' list-backstepped')."'>".$value($person)."</td>";
    }
    $text .= "</tr>";
}

$text .=<<<END
        </tbody>
    </table>
    <div id="editForm"></div>
    <script type='text/javascript'>

        jQuery.extend( jQuery.fn.dataTableExt.oSort, {
            "link-pre": function ( a ) {
                return a.match(/<a [^>]+>([^<]+)<\/a>/)[1];
            }/*,

            "link-asc": function ( a, b ) {
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },

            "link-desc": function ( a, b ) {
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            }*/
            ,
            "prb-pre": function ( a ){
                var tmp = a.split(",");
                //alert();
                return ((tmp[0]==0) ? '0' : '1') + ((tmp[1]==0) ? '0' : '1') + ((tmp[2]==0) ? '0' : '1');
            }
        } );

        $(document).ready(function(){
            var ltab = $('#mlist').dataTable({
                "iDisplayLength": 70,
                "columnDefs": [
                    { type: 'link', targets: 2 },
                    { type: 'prb', targets: 9 }
                ],
                "aoColumnDefs": [
                    {
                        "aTargets": [ 9 ],
                        "mDataProp": function ( data, type, row ) {
                            if (type === 'set') {
                                data[9] = row;

                                var btns = "";
                                var classes = ["paid", "repaid", "backstepped"];
                                var txt = data[9].split(",");
                                for(var i = 0; i < txt.length; i++){
                                    var tmp = (txt[i]==0) ? 0 : 1;
                                    btns += "<div onclick=\"btnclick(this, '"+classes[i]+"','"+row[0]+"',"+tmp+");\" class='btn btn-"+classes[i]+"-"+tmp+"'>&nbsp;</div>";
                                }

                                // Store the computed display for speed
                                data.date_rendered = btns;
                                return;
                            }
                            else if (type === 'display' || type === 'filter') {
                                return data.date_rendered;
                            }
                            // 'sort' and 'type' both just use the raw data
                            return data[9];
                        }
                    }
                ],
                "order": [[ 2, "asc" ]]
            });
        });

        function btnclick(that, type, hash, state){
            var newstate = (((state-1)<0) ? 1 : 0);
            $.get("index.php?page=list&ajax=ajax&update="+type+"&hash="+hash+"&nstate="+newstate ,"",
                function(){
                    that.className="btn btn-"+type+"-"+newstate;
                    that.setAttribute("onclick", "btnclick(this, '"+type+"', '"+hash+"', "+newstate+")");
                });
        }
    </script>
END;
}