<?php

class AdminNotesPage extends AdminPage {

    public function __construct($base) {
        parent::__construct($base);

        if (isset($_POST['note-content'])) {
            $cont = $_REQUEST['note-content'];
            if ($this->environment->database->has('notes', ['fahrt_id' => $this->fahrt->getID()]))
                $this->environment->database->update('notes', ['note' => $cont], ['fahrt_id' => $this->fahrt->getID()]);
            else
                $this->environment->database->insert('notes', ['note' => $cont, 'fahrt_id' => $this->fahrt->getID()]);
        }

        $this->content = $this->environment->database->get('notes', 'note', ['fahrt_id' => $this->fahrt->getID()]);
    }

    public function getHeaders() {
        return "<!-- wysihtml5 parser rules -->
            <script src=\"../view/js/wysihtml5-0.3.0_rc2.min.js\"></script>
            <!-- Library -->
            <script src=\"../view/js/wysihtml5-advanced.js\"></script>

            <link type='text/css' rel='stylesheet' href='../view/css/wysihtml5/editor.css' />
            <link type='text/css' rel='stylesheet' href='../view/css/wysihtml5/stylesheet.css' />
            <style type='text/css'>
                body {
                    /*width: 810px;*/
                    min-height: 100%;
                    /*margin: 0 auto;*/
                    padding-top: 40px !important;
                    padding-left: 10px !important;
                }
                #admin-content{
                    height: 100% !important;
                }
            </style>";
    }

    public function getHeader() {
        return '';
    }

    public function getFooter() {
        return '';
    }

    public function getText() {
        return '
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
                  <li data-wysihtml5-command-group="foreColor" class="fore-color command" title="Color the selected text">
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

        <section><form method="POST" style="height:100%">
            <input type="submit" name="submit" value="submit" class="submit-button" />
            <textarea name="note-content" id="wysihtml5-editor" spellcheck="false" wrap="off" placeholder="Enter your text ...">' .
                $this->content .
           '</textarea></form></section>

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
    }

    public function getAjax() {
        return '';
    }
}