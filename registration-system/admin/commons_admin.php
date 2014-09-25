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

    if(isset($_GET['logout']))
        setLoggedIn("");

    if(!isset($_POST['user']) || !isset($_POST['password']))
        return;

    $user = $_POST['user'];
    $password = $_POST['password'];


    if (isValidUser($user, $password))
        setLoggedIn($user);
}

function isValidUser($user, $password)
{
    $config_admins = readUserFile();
    foreach($config_admins as $cfg_user => $cfg_password)
    {
        if ($cfg_user != $user)
            continue;
        $cfg_password = $cfg_password["pw"];
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

function readUserFile(){
    global $config_userfile;
    $ret = [];

    $handle = fopen($config_userfile, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $tmp = explode(" ", $line);
            if(count($tmp)>=3){
                $ret[$tmp[1]] = ["pw" => $tmp[2], "sa" => $tmp[0]];
            }
        }
    } else { }
    fclose($handle);
    return $ret;
}

function isSuperAdmin(){
    $config_admins = readUserFile();
    return isset($_SESSION['loggedIn']) && isset($config_admins[$_SESSION['loggedIn']]) && $config_admins[$_SESSION['loggedIn']]['sa'] === "S";
}

function isLoggedIn()
{
    return isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] != '';
}

function setLoggedIn($user)
{
    if ($user != ""){
        comm_admin_verbose(2,"login");
        $_SESSION['loggedIn'] = $user;
    }else
    {
        comm_admin_verbose(2,"logout");
        session_destroy();
        header("location: ..");
    }
}

function comm_admin_verbose($level, $text){
    global $config_admin_verbose_level;
    if($config_admin_verbose_level >= $level)  {
        if(is_array($text)){
            echo "<pre>"; print_r($text); echo "</pre>";
        } else
            echo $text.'<br />';
    }
}