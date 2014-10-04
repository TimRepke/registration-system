<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 10/4/14
 * Time: 9:30 PM
 */

$config_verbose_level = 0; // 0 = nothing, 1 = important, 2 = somewhat important, 3 = detailed verbose, 4 = with sql
$config_admin_verbose_level = 0;

// URL where site is hosted with trailing slash
$config_baseurl = "http://fsfahrt.repke.eu/anmeldung/registration-system/";

// absolute path to doc root withOUT trailing slash
$config_basepath = "/var/www/vhosts/fsfahrt.repke.eu/httpdocs/anmeldung/registration-system";

// database config
$config_db = array(
    "name" => "fsfahrt",    // name of DB
    "user" => "fsfahrt",    // username
    "pass" => "9Lug*96q",   // password
    "host" => "localhost",  // host
    "type" => "mysql"       // type of DB - only tested with mysql (so better not change)!!
);

