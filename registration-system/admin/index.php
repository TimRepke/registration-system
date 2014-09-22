<?php
/**
 * Created by PhpStorm.
 * User: it
 * Date: 8/8/14
 * Time: 4:19 PM
 */

session_start();

require_once("commons_admin.php");
require_once("pages.php");
require_once("../config.inc.php");
require_once("../frameworks/medoo.php");

$template = file_get_contents("../view/admin_template.html");
$navigation = "";
$headers = "";
$text = "";
$ajax = "";

checkIfLogin();

if (isLoggedIn())
{
    $menu = array(
        "Übersicht" => "stuff",
        "Meldeliste" => "list",
        "Kosten" => "cost",
        "Rundmail" => "mail",
        "Notizen" => "notes",
        "Listenexport" => "export",
        "Deadlink" => "dead"
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
        case "":
        case "stuff":
            page_stuff(); break;
        case "list":
            page_list(); break;
        case "cost":
            page_cost(); break;
        case "mail":
            page_mail(); break;
        case "notes":
            page_notes(); break;
        case "export":
            page_export(); break;
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
else
    echo str_replace("{headers}", $headers, str_replace("{text}", $text, str_replace("{navigation}", $navigation, $template)));
