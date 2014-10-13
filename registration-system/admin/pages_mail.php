<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 8/17/14
 * Time: 11:05 PM
 */

// ENHANCEMENT: simplified presets (Alle,	gezahlt,	nicht gezahlt,	Anreise individuell)

global $config_studitypen, $config_reisearten, $config_essen, $admin_db, $config_current_fahrt_id, $config_admin_verbose_level, $config_verbose_level, $text, $headers, $ajax;
$config_verbose_level = 0;
$config_admin_verbose_level = 0;

$headers .= '<script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
             <script type="text/javascript" src="../view/js/jquery-ui.min.js"></script>';
$text .= '
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
                <select multiple name="val_studityp[]">';
                    foreach($config_studitypen as $typ)
                        $text .= '<option value="'.$typ.'">'.$typ.'</option>';
                    $text .= '
                </select>
            </td>
            <td>
                <select multiple name="val_antyp[]">';
                    foreach($config_reisearten as $typ)
                        $text .= '<option value="'.$typ.'">'.$typ.'</option>';
                    $text .= '
                </select>
            </td>
            <td>
                <select multiple name="val_abtyp[]">';
                    foreach($config_reisearten as $typ)
                        $text .= '<option value="'.$typ.'">'.$typ.'</option>';
                    $text .= '
                </select>
            </td>
            <td>
                <select multiple name="val_nights[]">';
                    $tage = $admin_db->query("SELECT DATEDIFF(bis, von) AS diff FROM fahrten WHERE fahrt_id=".$config_current_fahrt_id)->fetch(0);
                        for($cnt = $tage['diff']; $cnt>=0; $cnt--)
                            $text .=  '<option value="'.$cnt.'">'.$cnt.'</option>';
                    $text .= '
                </select>
            </td>
            <td>
                <select multiple name="val_essen[]">';
                    foreach($config_essen as $typ)
                        $text .= '<option value="'.$typ.'">'.$typ.'</option>';
                    $text .= '
                </select>
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
</form>';

$query = "SELECT mehl, forname, sirname FROM bachelor";
$where = array("fahrt_id = ".$config_current_fahrt_id);
$dsa = "";
if(!isset($_REQUEST['submit'])){
    // not submitted
    //$dsa = "nosubmit";
} else {
    //$dsa = "submit";
    if(isset($_REQUEST['check_studityp'])){
        $tmp = "";
        foreach($_REQUEST['val_studityp'] as $st){
            $tmp.= "studityp = '".$st."' OR ";
        }
        array_push($where,substr($tmp,0,-3));
    }
    if(isset($_REQUEST['check_antyp'])){
        $tmp = "";
        foreach($_REQUEST['val_antyp'] AS $st){
            $tmp.= "antyp = '".$st."' OR ";
        }
        array_push($where,substr($tmp,0,-3));
    }
    if(isset($_REQUEST['check_abtyp'])){
        $tmp = "";
        foreach($_REQUEST['val_abtyp'] AS $st){
            $tmp.= "abtyp = '".$st."' OR ";
        }
        array_push($where,substr($tmp,0,-3));
    }
    if(isset($_REQUEST['check_nights'])){
        // TODO
    }
    if(isset($_REQUEST['check_essen'])){
        $tmp = "";
        foreach($_REQUEST['val_essen'] AS $st){
            $tmp.= "essen = '".$st."' OR ";
        }
        array_push($where,substr($tmp,0,-3));
    }
    if(isset($_REQUEST['check_paid'])){
        if($_REQUEST['val_paid'] == 1)
            array_push($where,"paid IS NOT NULL");
        else
            array_push($where,"paid IS NULL");
    }
    if(isset($_REQUEST['check_repaid'])){
        if($_REQUEST['val_repaid'] == 1)
            array_push($where,"repaid IS NOT NULL");
        else
            array_push($where,"repaid IS NULL");
    }
    if(isset($_REQUEST['check_virgin'])){
        array_push($where,"virgin = ".$_REQUEST['val_virgin']);
    }
    if(isset($_REQUEST['check_backstepped'])){
        if($_REQUEST['val_backstepped'] == 1)
            array_push($where,"backstepped IS NOT NULL");
        else
            array_push($where,"backstepped IS NULL");
    }

}

if(count($where)>0){
    $query .= " WHERE ";

    foreach($where AS $w)
        $query .= "(".$w.") AND ";

    $query = substr($query,0,-4); // cut last AND
}

//$config_verbose_level = 4;
$tmp = $admin_db->query($query.";");

if($tmp)
    $mails = $tmp->fetchAll(PDO::FETCH_ASSOC);
else{
    comm_admin_verbose(3,$admin_db->error());
    $mails = array();
}

$text .=  '<textarea style="height:300px; width:800px" id="mails">'.$dsa;
foreach($mails as $mehl){
    $text .=  "<".$mehl['forname']." ".$mehl['sirname']."> ".$mehl['mehl']."; ";
    $ajax .=  "<".$mehl['forname']." ".$mehl['sirname']."> ".$mehl['mehl']."; ";
}
$text .=  '</textarea>';


comm_admin_verbose(3,$_REQUEST);
?>
