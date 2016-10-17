<?php

if (true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
$config_databse_debug = false;

// URL where site is hosted with trailing slash
$config_baseurl = "http://localhost:8080/";

// absolute path to doc root withOUT trailing slash
$config_basepath = __DIR__;

$config_impressum = 'https://fachschaft.informatik.hu-berlin.de/index.php/Fachschaft_Informatik:Impressum';

// database config
$config_db = [
    "name" => "fsfahrt",    // name of DB
    "user" => "fsfahrt",    // username
    "pass" => "9Lug*96q",   // password
    "host" => "db",  // host
    "type" => "mysql"       // type of DB - only tested with mysql (so better not change)!!
];
