<?php
/**
 * Created by PhpStorm.
 * User: it
 * Date: 8/8/14
 * Time: 4:19 PM
 */
error_reporting(E_ALL);
ini_set("display_errors",1);

session_start();

require_once("commons_admin.php");
require_once("../frameworks/commons.php");
require_once("pages.php");
require_once("../config.inc.php");
require_once("../frameworks/medoo.php");
require '../lang.php';

$template = file_get_contents("../view/admin_template.html");
$title = "FSFahrt - Admin Panel";
$navigation = "";
$headers = "";
$header  = "";
$footer  = "";
$text = "";
$ajax = "";

checkIfLogin();

if (isLoggedIn())
{
    $menu = array(
        "Anmeldung" => "front",
        "Übersicht" => "stuff",
        "Meldeliste" => "list",
        "Warteliste" => "wl",
        "Kosten" => "cost",
        "Rundmail" => "mail",
        "Notizen" => "notes",
        "Listenexport" => "export",
        "Infos" => "infos",
        "SA*"    => "admin"
    );

    $admin_db = new medoo(array(
        'database_type' => $config_db["type"],
        'database_name' => $config_db["name"],
        'server'        => $config_db["host"],
        'username'      => $config_db["user"],
        'password'      => $config_db["pass"]
    ));

    $page = isset($_GET['page']) ? $_GET['page'] : "";
    $navigation = generateNavigationItems($page, $menu);

    switch($page)
    {
        case "front":
            page_front(); break;
        case "":
        case "stuff":
            page_stuff(); break;
        case "list":
            page_list(); break;
        case "wl":
            page_wl(); break;
        case "cost":
            page_cost(); break;
        case "mail":
            page_mail(); break;
        case "notes":
            page_notes(); break;
        case "export":
            page_export(); break;
        case "infos":
            page_infos(); break;
        case "admin":
            if(isSuperAdmin()) page_sa();
            else page_404($page);
            break;
        default:
            page_404($page);
    }
}
else
{
    $text .= file_get_contents("../view/admin_login_form.html");
}

if(isset($_REQUEST['ajax']))
    echo $ajax;
else{
    $rep = ["{headers}" => $headers,
            "{text}"    => $text,
            "{navigation}" => $navigation,
            "{title}"   => $title,
            "{header}"  => $header,
            "{footer}"  => $footer];
    echo str_replace(array_keys($rep), array_values($rep), $template);
}