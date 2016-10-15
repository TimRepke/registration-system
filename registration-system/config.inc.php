<?php

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
date_default_timezone_set("Europe/Berlin");

// Adjust variables here ===============================================================================================

// actually adjust the stuff in there...
require("config.local.php");




// ========= DONT TOUCH ANYTHING DOWN HERE!!! ==========================================================================

$config_studitypen_o = array(
    "ERSTI" => "Ersti",       // 0
    //"Wechsli",     // 1 - woanders/was anderes studiert, jetzt hier
    //"MasterErsti", // 2
    "HOERS" => "Hoersti",      // 3 - länger an der HU
    "TUTTI" => "Tutor"       // 4 - Tutor
    //"Fachi"        // 5 - FS Ini
);
$config_studitypen = array_values($config_studitypen_o);

$config_essen_o = array(
    "ALLES" => "Alles",
    "VEGE" => "Vegetarisch",
    "VEGA" => "Vegan"
    //,"Frutarisch",
    //"Grießbrei",
    //"Carnivore",
    //"Extrawurst"
);
$config_essen = array_values($config_essen_o);

$config_reisearten_o = array(
    "BUSBAHN"=>"gemeinsam mit Bus/Bahn",
    "RAD"=>"gemeinsam mit Rad",
	"AUTO"=>"selbst mit Auto",
    "INDIVIDUELL"=>"Kamel/Individuell"
    //,"mit Kamel"
);
$config_reisearten = array_values($config_reisearten_o);

$config_reisearten_destroyed = array(
    "mit Kamel",
    "mit Esel",
    "mit Schlauchboot"
);

$config_userfile = $config_basepath."/passwd/users.txt"; // relative to configfile
$config_current_fahrt_file = $config_basepath."/config_current_fahrt_id";

$config_mailtag = "[FS-Fahrt] - ";

