<?php
// https://repke.eu:8443
// passwort manu:kuzerPenis666!

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
date_default_timezone_set("Europe/Berlin");

$config_verbose_level = 3; // 0 = nothing, 1 = important, 2 = somewhat important, 3 = detailed verbose, 4 = with sql
$config_admin_verbose_level = 3;

$config_db = array(
    "name" => "fsfahrt",
    "user" => "fsfahrt",
    "pass" => "9Lug*96q",
    "host" => "localhost",
    "type" => "mysql"
);

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


/* // deprecated:
$config_admins = array(
    // username => password
    "tim"    => '{SHA-256}8013a101f26fd8dcc8c40d0eb1dcb513$c3a97d44e67564ed79a60fa0de6ea4193bb18932a8d08b5e8d664bd14b32a4f5', // broetchen
    "manu"   => '{SHA-256}12c9b021c42741545f9f01e2afd67aa2$7112be28c0c11f987de4401798a2ba041e518bb3f22bcb8cf4f3bf3f590b65b9' // mepmepmep
    ,"nosa"  => "{SHA-256}10be99b14c2627e8691134db9bacf456$0a7ce2973159cb2f3c971a62944fb865e5110780503513da6f6f1e16326f18a1" // murmeltier
);

$config_superadmins = array(
    "tim", "manu"
);
*/
$config_userfile = __DIR__."/passwd/users.txt"; // relative to configfile
$config_current_fahrt_file = __DIR__."/config_current_fahrt_id";

$config_mailtag = "[FS-Fahrt] - ";
$config_baseurl = "http://fsfahrt.repke.eu/anmeldung/registration-system/";

$config_current_fahrt_id = getCFID();

function getCFID(){
    global $config_current_fahrt_file;
    if(file_exists( $config_current_fahrt_file ))
        $tmp = file_get_contents($config_current_fahrt_file);
    if(is_numeric($tmp))
        return $tmp;
    return 1;
}
