<?php

/**
 * converts mail into safe for web format
 * @param $mail - mail to convert
 * @return mixed - converted mail
 */
function comm_convert_mail($mail){
    return str_replace(array("@","."),array("&Oslash;", "&middot;"), $mail);
}

function comm_verbose($level, $text){
    global $config_verbose_level;
    if($config_verbose_level >= $level) echo $text.'<br />';
}

function comm_format_date($date){
    return date('d.m.Y', strtotime($date));
}

function comm_get_possible_dates($fid){
    return array("12.03.2014","13.03.2014","14.03.2014");
}