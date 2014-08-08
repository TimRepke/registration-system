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

$template = file_get_contents("../view/admin_template.html");
$navigation = "";
$text = "";

checkIfLogin();

if (isLoggedIn())
{
    $menu = array(
        "Übersicht" => "stuff",
        "Meldeliste" => "list",
        "Kosten" => "cost",
        "Rundmail" => "mail"
    );

    $page = isset($_GET['page']) ? $_GET['page'] : "";
    $navigation = generateNavigationItems($page, $menu);

    switch($page)
    {
        case "":
        case "stuff":
            page_stuff(); break;
        case "list":
            page_list(); break;
        //case "cost":
            //page_cost(); break;
        //case "mail":
            //page_mail(); break;
        default:
            page_404();
    }
}
else
{
    $text .= file_get_contents("../view/admin_login_form.html");
}

echo str_replace("{text}", $text, str_replace("{navigation}", $navigation, $template));