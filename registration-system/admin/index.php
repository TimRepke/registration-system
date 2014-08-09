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
$headers = "";
$text = "";

checkIfLogin();

if (isLoggedIn())
{
    $menu = array(
        "Übersicht" => "stuff",
        "Meldeliste" => "list",
        "Kosten" => "cost",
        "Rundmail" => "mail",
        "Notitzen" => "notes"
    );

    $page = isset($_GET['page']) ? $_GET['page'] : "";
    $navigation = generateNavigationItems($page, $menu);
    $headers =<<<END
    <link rel="stylesheet" type="text/css" href="../view/css/DataTables/css/jquery.dataTables.min.css" />
    <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../view/js/jquery.dataTables.min.js"></script>
END;

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
        case "notes":
            page_notes(); break;
        default:
            page_404($page);
    }
}
else
{
    $text .= file_get_contents("../view/admin_login_form.html");
}

echo str_replace("{headers}", $headers, str_replace("{text}", $text, str_replace("{navigation}", $navigation, $template)));