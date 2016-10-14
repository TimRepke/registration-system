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
 * @return string
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
 * @return string
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
