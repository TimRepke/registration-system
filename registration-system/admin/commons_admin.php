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


/**
 * Puts out Label and Selection box
 *
 * @param $name
 * @param $id
 * @param $values
 * @param $selected
 * @param $subtext
 */
function admin_show_formular_helper_sel($name, $id, $values, $selected, $subtext){
    $r = '<label>'.$name.'
        <span class="small">'.$subtext.'</span>
        </label>
        <select name="'.$id.'" id="'.$id.'">';
    foreach($values as $val){
        $r .= '<option value="'.$val.'"';
        if($val == $selected) $r .= ' selected';
        $r .= '>'.$val.'</option>';
    }
    $r .= '</select>';

    return $r;
}

/**
 * Puts out Label and two selection boxes side by side right below
 *
 * @param $name
 * @param $id
 * @param $values
 * @param $selected
 * @param $id2
 * @param $values2
 * @param $selected2
 * @param $subtext
 */
function admin_show_formular_helper_sel2($name, $id, $values, $selected, $id2, $values2, $selected2, $subtext){
    $r = '<label style="text-align:left">'.$name.'
        <span class="small">'.$subtext.'</span>
        </label><table><tr><td>
        <select name="'.$id.'" id="'.$id.'" style="width:110px; text-align: center">';
    foreach($values as $val){
        $r .= '<option value="'.$val.'"';
        if($val == $selected) $r .= ' selected';
        $r .='>'.$val.'</option>';
    }
    $r .= '</select></td><td><select name="'.$id2.'" id="'.$id2.'">';
    foreach($values2 as $val){
        $r .= '<option value="'.$val.'"';
        if($val == $selected2) $r .= ' selected';
        $r .='>'.$val.'</option>';
    }
    $r .= '</select></td></tr></table>';
    return $r;
}

function admin_show_formular_helper_input($name, $id, $value, $subtext){
    $r = '<label>'.$name.'
        <span class="small">'.$subtext.'</span>
        </label>
        <input type="text" name="'.$id.'" id="'.$id.'" value="'.$value.'" />';
    return $r;
}
