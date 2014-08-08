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

function comm_isopen_fid($db_handle, $fid){
    comm_verbose(3,"checking if fid ". $fid . " is open");
    return $db_handle->has("fahrten", array(
                                            "AND" => array(
                                                "fahrt_id"=>$fid,
                                                "regopen"=>1)));
}

function comm_generate_key($db_handle, $table, $col, $conditions){
    again:
    $bytes = openssl_random_pseudo_bytes(8);
    $hex   = bin2hex($bytes);
    comm_verbose(3,"generated hex for test: ".$hex);
    $conditions[$col] = $hex;

    if($db_handle->has($table, array("AND"=>$conditions))) goto again;
    comm_verbose(2,"generated hex: ".$hex);
    return $hex;
}