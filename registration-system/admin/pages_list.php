<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 9/3/14
 * Time: 7:09 PM
 */


global $text, $headers, $admin_db, $config_current_fahrt_id, $ajax, $config_studitypen, $config_essen, $config_reisearten, $config_essen_o, $config_reisearten_o;

$ecols = [
    "forname" => function($d){ return $d; },
    "sirname" => function($d){ return $d; },
    "mehl"    => function($d){ return $d; },
    "pseudo"  => function($d){ return $d; },
    "antyp"   => function($d){ return $d; },
    "abtyp"   => function($d){ return $d; },
    "anday"   => function($d){ return date('Y-m-d', DateTime::createFromFormat('d.m.Y',$d)->getTimestamp()); },
    "abday"   => function($d){ return date('Y-m-d', DateTime::createFromFormat('d.m.Y',$d)->getTimestamp()); },
    "comment" => function($d){ return $d; },
    "studityp"=> function($d){ return $d; },
    "virgin"  => function($d){ return (($d=="Nein") ? 1 : 0); }, // nein zu 18+ heißt ja zu virgin => 1
    "public"  => function($d){ return $d; },
    "essen"   => function($d){ return $d; }
];

if(isset($_REQUEST['change'])){
    $update = [];
    foreach($ecols as $k=>$e){
        $update[$k] = $e($_REQUEST[$k]);
    }
    $admin_db->update("bachelor", $update, ["bachelor_id"=> $_REQUEST['change']]);
}

if(isset($_REQUEST['delete'])){
    $admin_db->delete("bachelor", ["bachelor_id"=> $_REQUEST['delete']]);
}

if(isset($_REQUEST['ajax'])){

    if(isset($_REQUEST['update']) && isset($_REQUEST['hash']) && isset($_REQUEST['nstate'])){
        $col = $_REQUEST['update'];
        $id  = $_REQUEST['hash'];
        $val = ($_REQUEST['nstate'] == 1) ? time() : NULL;
        $admin_db->update("bachelor", array($col=>$val), array("bachelor_id"=> $id));
    }


    elseif(isset($_REQUEST['form'])){
        $bid = $_REQUEST['hash'];


        $rcols = [
            "bachelor_id" => ["Hash",   function( $b ){ return $b; }],
            "fahrt_id" => ["Fahrt",     function( $b ){ return "ID ".$b; }],
            "anm_time" => ["Anmeldung", function( $b ){ return date("d.m.Y",$b); }],
            "paid"     => ["Bezahlt",   function( $b ){ return ($b==0) ? "Nein" : date("d.m.Y", $b); }],
            "repaid"   => ["Rückgezahlt", function($b){ return ($b==0) ? "Nein" : date("d.m.Y", $b); }],
            "backstepped" => ["Zurückgetreten", function( $b ){ return ($b==0) ? "Nein" : date("d.m.Y", $b); }]
        ];


        $bachelor = $admin_db->get('bachelor', array_merge(array_keys($ecols), array_keys($rcols)), array('bachelor_id'=>$bid));
        $possible_dates = comm_get_possible_dates($admin_db, $bachelor['fahrt_id']);

        foreach($rcols as $k=>$r){
            $ajax .= "<b>".$r[0].":</b> ".$r[1]($bachelor[$k])."<br />";
        }

        $ajax .= '<br />
        <div id="stylized" class="myform">
        <form id="form" name="form" method="post" action="?page=list">';
        $ajax .= '<input type="hidden" value="'.$bid.'" name="change" id="change" />';

        $ajax .= admin_show_formular_helper_input("Vorname", "forname", $bachelor["forname"], "");
        $ajax .= admin_show_formular_helper_input("Nachname","sirname",$bachelor["sirname"],"");
        $ajax .= admin_show_formular_helper_input("Anzeigename","pseudo",$bachelor["pseudo"],"");
        $ajax .= admin_show_formular_helper_input("E-Mail-Adresse","mehl",$bachelor["mehl"],"regelmäßig lesen!");
        $ajax .= admin_show_formular_helper_sel("Er/Sie/Es ist","studityp",$config_studitypen, $bachelor["studityp"],"");
        $ajax .= admin_show_formular_helper_sel("Alter 18+?","virgin",array("Nein", "Ja"), (($bachelor["virgin"]==1) ? "Nein" : "Ja"), "Älter als 18?");
        $ajax .= admin_show_formular_helper_sel("Essenswunsch","essen",$config_essen, $bachelor["essen"],"Info für den Koch.");
        $ajax .= "<div style='clear: both;'></div>";
        $ajax .= admin_show_formular_helper_sel2("Anreise","anday", array_slice($possible_dates,0, -1), $bachelor["anday"]
            ,"antyp",$config_reisearten, $bachelor["antyp"],"");
        $ajax .= admin_show_formular_helper_sel2("Abreise","abday", array_slice($possible_dates,1), $bachelor["abday"]
            ,"abtyp",$config_reisearten,$bachelor["abtyp"],"");
        $ajax .= '
        <label>Anmerkung</label>
        <textarea id="comment" name="comment" rows="3" cols="40">'.$bachelor["comment"].'</textarea>
        <input type="checkbox" name="public" value="public" style="width:40px" '.(($bachelor['public']==0 ? " checked" : "")).'><span style="float:left">Anmeldung verstecken</span><br/>
        <div style="clear:both"></div>
        Note: No check for validity of data here!
        <button type="submit" name="submit" id="submit" value="submit">Ändern!</button>
        <div class="spacer"></div>';


        $ajax .= '</form>
        </div>
        ';

        $ajax .= '<br /><hr /> <br/>
            <form method="POST" >
                Note: Keine Nachfrage, löscht direkt und unwiederruflich!!<br />
                <input type="submit" name="submit_del" value="DELETE" />
                <input type="hidden" name="delete" value="'.$bid.'" />
            </form>
        ';
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

</style>";

$text .= "<h1>Meldeliste</h1>";

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
    "backstepped",
    "virgin",
    "essen"
);

$columnFunctions = array(
    "Anmelde-ID" => function($person) { return "<a href='#' class='edit_bachelor'>".$person["bachelor_id"]."</a>"; }
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
    "PaidReBack" => function($person) { return ($person["paid"] ? $person["paid"] : "0") .",". ($person["repaid"] ? $person["repaid"] : "0") .",". ($person["backstepped"] ? $person["backstepped"] : "0"); }
);

$text .= "Toggle Column: ";
    $tcnt = 0;
foreach($columnFunctions as $key => $value){
    $text .= '<a class="toggle-vis" data-column="'.$tcnt.'">'.$key.'</a> - ';
    $tcnt++;
}
$text .= "<br />";

$text .=<<<END
    <table id="mlist" class="compact hover">
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

    $buttoncol = 11;
$text .=<<<END
        </tbody>
    </table>
    <div id="editForm">
        <div id="editFormTopbar"><p>X</p></div>
        <p></p>
    </div>
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
        var ltab;
        $(document).ready(function(){
             ltab = $('#mlist').DataTable({
                "columnDefs": [
                    { type: 'link', targets: 2 },
                    { type: 'link', targets: 0 },
                    { type: 'prb', targets: $buttoncol }
                ],
                "aoColumnDefs": [
                    {
                        "aTargets": [ $buttoncol ],
                        "mDataProp": function ( data, type, row ) {
                            if (type === 'set') {
                                data[$buttoncol] = row;

                                var btns = "";
                                var classes = ["paid", "repaid", "backstepped"];
                                var txt = data[$buttoncol].split(",");
                                for(var i = 0; i < txt.length; i++){
                                    var tmp = (txt[i]==0) ? 0 : 1;
                                    btns += "<div onclick=\"btnclick(this, '"+classes[i]+"','"+data[0].match(/<a [^>]+>([^<]+)<\/a>/)[1]+"',"+tmp+");\" class='btn btn-"+classes[i]+"-"+tmp+"'>&nbsp;</div>";
                                }

                                // Store the computed display for speed
                                data.date_rendered = btns;
                                return;
                            }
                            else if (type === 'display' || type === 'filter') {
                                return data.date_rendered;
                            }
                            // 'sort' and 'type' both just use the raw data
                            return data[$buttoncol];
                        }
                    }
                ],
                "order": [[ 2, "asc" ]],
                "paging": false
            });

            $('a.toggle-vis').click( function (e) {
                e.preventDefault();

                // Get the column API object
                var column = ltab.column( $(this).attr('data-column') );

                // Toggle the visibility
                column.visible( ! column.visible() );
            } );
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
                    that.className="btn btn-"+type+"-"+newstate;
                    that.setAttribute("onclick", "btnclick(this, '"+type+"', '"+hash+"', "+newstate+")");
                });
        }
    </script>
END;
}
