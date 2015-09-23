<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 9/23/14
 * Time: 11:14 AM
 */

global $text, $headers, $admin_db, $config_current_fahrt_id, $ajax, $config_reisearten, $config_reisearten_0, $config_studitypen_o, $config_admin_verbose_level, $config_verbose_level, $config_essen;
$config_admin_verbose_level = 0;
$config_verbose_level = 0;
$text .= "<h1>Informationen</h1>";

if(isset($_POST['note-content'])){
    comm_admin_verbose(2,"received submit");
    $admin_db->update("fahrten",["beschreibung" => $_REQUEST['note-content'],
                                 "titel"        => $_REQUEST['titel'],
                                 "von"          => $_REQUEST['von'],
                                 "bis"          => $_REQUEST['bis'],
                                 "ziel"         => $_REQUEST['ziel'],
                                 "map_pin"      => $_REQUEST['us2-lat']." ".$_REQUEST['us2-lon'],
                                 "leiter"       => $_REQUEST['leiter'],
                                 "kontakt"      => $_REQUEST['kontakt'],
                                 "max_bachelor" => $_REQUEST['max_bachelor'],
                                 "regopen"      => isset($_REQUEST['regopen']) ? 1 : 0,
                                 "wikilink"     => $_REQUEST['wikilink'],
                                 "paydeadline"  => $_REQUEST['paydeadline'],
                                 "payinfo"      => $_REQUEST['payinfo'],
                                 "opentime"     => $_REQUEST['opentime']],
                        array("fahrt_id"=>$config_current_fahrt_id));
}

$data = $admin_db->get("fahrten", ["beschreibung", "titel", "von", "bis", "ziel", "map_pin", "leiter", "kontakt", "regopen", "max_bachelor", "wikilink", "paydeadline", "payinfo", "opentime"], array("fahrt_id"=>$config_current_fahrt_id));

if(!preg_match('/\d{2}\.\d+ \d{2}\.\d+/', $data['map_pin'])){
    $data['map_pin'] = '52.4263218 13.5223815';
}
if($data['opentime'] == 0) $data['opentime'] = time();

$headers .="<!-- wysihtml5 parser rules -->
<script src=\"../view/js/wysihtml5-0.3.0_rc2.min.js\"></script>
<!-- Library -->
<script src=\"../view/js/wysihtml5-advanced.js\"></script>
<script src=\"../view/js/jquery-1.11.1.min.js\"></script>
<script src=\"../view/js/jquery-ui.min.js\"></script>
<script src=\"../view/js/jquery.datetimepicker.js\"></script>
<script type=\"text/javascript\" src='http://maps.google.com/maps/api/js?sensor=false&libraries=places'></script>
<script src=\"../view/js/locationpicker.jquery.js\"></script>
<link type='text/css' rel='stylesheet' href='../view/jquery-ui/jquery-ui.min.css' />
<link type='text/css' rel='stylesheet' href='../view/css/jquery.datetimepicker.css' />

<!--link type='text/css' rel='stylesheet' href='../view/css/wysihtml5/editor.css' /-->
<link type='text/css' rel='stylesheet' href='../view/css/wysihtml5/stylesheet.css' />
<style type='text/css'>
body {
    /*width: 810px;*/
    min-height: 100%;
    /*margin: 0 auto;*/
    padding-top: 40px !important;
    padding-left: 10px !important;
}
section{
    position: relative;
    top: inherit;
    bottom: inherit;
    width: inherit;
}
.formlist li{
    margin: 8px 10px;
    clear: both;
    height: 30px;
}
.formlist input, .formlist textarea {
    float: right;
    width: 300px;
}

.formlist label{
    float:left;
}
</style> ";

$text .= '
<section>
    <form method="POST" style="height:300px;">
        <div style="float:left">
            <input type="submit" name="submit" value="submit" class="submit-button" />
            <p></p>
            <ul class="formlist">
                <li><label>Titel</label>
                    <input type="text" name="titel" id="titel" value="'.$data["titel"].'" /></li>
                <li><label>Ziel</label>
                    <input type="text" name="ziel" id="ziel" value="'.$data["ziel"].'" /></li>
                <li><label>Von</label>
                    <input type="text" name="von" id="von" value="'.$data["von"].'" /></li>
                <li><label>Bis</label>
                    <input type="text" name="bis" id="bis" value="'.$data["bis"].'" /></li>
                <li><label>Anmeldung ab</label>
                    <input type="text" name="opentime" id="opentime" value="'.$data["opentime"].'" /></li>
                <li><label>Anm. offen</label>
                    <input type="checkbox" name="regopen" id="regopen" value="penis" '.(($data["regopen"]==1) ? "checked" : "").' /></li>
                <li><label>Max TN</label>
                    <input type="number" name="max_bachelor" id="max_bachelor" value="'.$data["max_bachelor"].'" /></li>
                <li><label>Leiter</label>
                    <input type="text" name="leiter" id="leiter" value="'.$data["leiter"].'" /></li>
                <li><label>E-Mail</label>
                    <input type="text" name="kontakt" id="kontakt" value="'.$data["kontakt"].'" /></li>
                <li><label>Wiki-Link</label>
                    <input type="text" name="wikilink" id="wikilink" value="'.$data["wikilink"].'" /></li>
                <li><label>Zahlung bis</label>
                    <input type="text" name="paydeadline" id="paydeadline" value="'.$data["paydeadline"].'" /></li>
                <li><label>Zahlungsdetails</label>
                    <textarea style="border:1px dotted grey;height: 6em; padding: 0 0 0 0.4em" rows="4" name="payinfo" id="payinfo">'.$data["payinfo"].'</textarea></li>
            </ul>
        </div>
        <div style="float:left">
            <label>Map Pin</label>
                Location: <input type="text" id="us2-address" style="width: 200px"/>
                <div id="us2" style="width: 500px; height: 353px;"></div>
                <input type="hidden" id="us2-lat" name="us2-lat" value="'.explode(" ",$data["map_pin"])[0].'" />
                <input type="hidden" id="us2-lon" name="us2-lon" value="'.explode(" ",$data["map_pin"])[1].'" />
                <script>
                    $(\'#us2\').locationpicker({
                        location: {latitude: '.explode(" ",$data["map_pin"])[0].', longitude: '.explode(" ",$data["map_pin"])[1].'},
                        radius:0,
                        inputBinding: {
                            latitudeInput: $(\'#us2-lat\'),
                            longitudeInput: $(\'#us2-lon\'),
                            locationNameInput: $(\'#us2-address\')
                        }
                    });
                    $(function() {
                        $( "#von" ).datepicker( { dateFormat: "yy-mm-dd"} );
                        $( "#bis" ).datepicker( { dateFormat: "yy-mm-dd"} );
                        $( "#opentime" ).datetimepicker( { format: "unixtime" } );
                        $( "#paydeadline" ).datepicker( { dateFormat: "yy-mm-dd"} );
                    });
                </script>
        </div>
        <div style="clear:both"></div>
<br />
    <div id="wysihtml5-editor-toolbar">
      <header>
        <ul class="commands">
          <li data-wysihtml5-command="bold" title="Make text bold (CTRL + B)" class="command"></li>
          <li data-wysihtml5-command="italic" title="Make text italic (CTRL + I)" class="command"></li>
          <li data-wysihtml5-command="insertUnorderedList" title="Insert an unordered list" class="command"></li>
          <li data-wysihtml5-command="insertOrderedList" title="Insert an ordered list" class="command"></li>
          <li data-wysihtml5-command="createLink" title="Insert a link" class="command"></li>
          <li data-wysihtml5-command="insertImage" title="Insert an image" class="command"></li>
          <li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h1" title="Insert headline 1" class="command"></li>
          <li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h2" title="Insert headline 2" class="command"></li>
          <li data-wysihtml5-command-group="foreColor" class="fore-color" title="Color the selected text" class="command">
            <ul>
              <li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="silver"></li>
              <li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="gray"></li>
              <li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="maroon"></li>
              <li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="red"></li>
              <li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="purple"></li>
              <li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="green"></li>
              <li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="olive"></li>
              <li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="navy"></li>
              <li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="blue"></li>
            </ul>
          </li>
          <li data-wysihtml5-command="insertSpeech" title="Insert speech" class="command"></li>
          <li data-wysihtml5-action="change_view" title="Show HTML" class="action"></li>
        </ul>
      </header>
      <div data-wysihtml5-dialog="createLink" style="display: none;">
        <label>
          Link:
          <input data-wysihtml5-dialog-field="href" value="http://">
        </label>
        <a data-wysihtml5-dialog-action="save">OK</a>&nbsp;<a data-wysihtml5-dialog-action="cancel">Cancel</a>
      </div>

      <div data-wysihtml5-dialog="insertImage" style="display: none;">
        <label>
          Image:
          <input data-wysihtml5-dialog-field="src" value="http://">
        </label>
        <a data-wysihtml5-dialog-action="save">OK</a>&nbsp;<a data-wysihtml5-dialog-action="cancel">Cancel</a>
      </div>
    </div>
        <textarea name="note-content" id="wysihtml5-editor" spellcheck="false" wrap="off" placeholder="Enter your text ...">'.$data["beschreibung"].'</textarea>
    </form>
</section>

<script>
var editor = new wysihtml5.Editor("wysihtml5-editor", {
        toolbar:     "wysihtml5-editor-toolbar",
        stylesheets: ["../view/css/wysihtml5/editor.css"],
        parserRules: wysihtml5ParserRules
      });

      editor.on("load", function() {
        var composer = editor.composer,
            h1 = editor.composer.element.querySelector("h1");
        if (h1) {
          composer.selection.selectNode(h1);
        }
      });
</script>';