<?php

require_once("../frameworks/medoo.php");
require_once("../config.inc.php");

function page_stuff()
{
    //global $text;
    //$text .= "Übersichtsseite";
    require_page("pages_overview.php");
}

function page_list(){
    require_page("pages_list.php");
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
function page_export(){
    require_page("pages_export.php");
}

function require_page($page){
    if(!@file_exists($page) ) {
        page_404($page);
    } else {
        require_once $page;
    }
}

?>