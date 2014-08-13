<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 8/9/14
 * Time: 4:07 PM
 */
global $headers, $text, $admin_db, $config_current_fahrt_id, $config_admin_verbose_level, $config_verbose_level;
$config_admin_verbose_level = 0;
$config_verbose_level = 0;

if(isset($_POST['note-content'])){
    comm_admin_verbose(2,"received submit");
    $cont = $_REQUEST['note-content'];
    $admin_db->update("notes",array("note"=>$cont),array("fahrt_id"=>$config_current_fahrt_id));
}

$content = $admin_db->get("notes", "note", array("fahrt_id"=>$config_current_fahrt_id));

$headers .="<!-- wysihtml5 parser rules -->
<script src=\"../view/js/wysihtml5-0.3.0_rc2.min.js\"></script>
<!-- Library -->
<script src=\"../view/js/wysihtml5-advanced.js\"></script>

<link type='text/css' rel='stylesheet' href='../view/css/wysihtml5/editor.css' />
<link type='text/css' rel='stylesheet' href='../view/css/wysihtml5/stylesheet.css' />";

$text .= '
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

<section><form method="POST" style="height:100%"> <input type="submit" name="submit" value="submit" /><textarea name="note-content" id="wysihtml5-editor" spellcheck="false" wrap="off" placeholder="Enter your text ...">'.$content.'</textarea></form></section>

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

