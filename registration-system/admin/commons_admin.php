<?php

require_once("../config.inc.php");

function generateNavigationItems($page, $menu)
{
    $text = '';
    foreach($menu as $name => $page)
    {
        $text .= "<a href='?page=$page'>$name</a>";
    }
    return $text;
}

function checkIfLogin()
{
    if(!isset($_POST['user']) || !isset($_POST['password']))
        return;

    $user = $_POST['user'];
    $password = $_POST['password'];

    if (isValidUser($user, $password))
        setLoggedIn($user);
}

function isValidUser($user, $password)
{
    global $config_admins;
    foreach($config_admins as $cfg_user => $cfg_password)
    {
        if ($cfg_user != $user)
            continue;

        if ($cfg_password[0] == '{')
        {
            if (strpos($cfg_password, "{SHA254}") >= 0)
            {
                $beginOfSalt = strpos($cfg_password, "$");
                $salt = substr($cfg_password, 9, strpos($cfg_password, "$") - 9);
                $hash = substr($cfg_password, $beginOfSalt + 1);

                if (hash('sha256', $password . $salt) == $hash)
                    return true;
            }
        }
        else
        {
            // TODO: ONLY sha256 yet, others not implemented
        }
    }
    return false;
}

function isLoggedIn()
{
    return isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] != '';
}

function setLoggedIn($user)
{
    if ($user != "")
        $_SESSION['loggedIn'] = $user;
    else
    {
        session_destroy();
        header("location: ..");
    }
}


