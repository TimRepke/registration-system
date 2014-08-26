<?php

$invalidCharsRegEx = "/^[^0-9<>!?.::,#*@^_$\\\"'%;()&+]{2,50}$/"; // d©_©b

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
    if($config_verbose_level >= $level) {
        if(is_array($text)){
            echo "<pre>"; print_r($text); echo "</pre>";
        } else
            echo $text.'<br />';
    }
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

/*
 * sends mail
 *
 * first line of $cont is used as subject iff terminated by double backslash (\\)
 * note that there should be no "\\" anywhere else in the string!!!
 *
 * returns true/false depending on success
 */
function comm_send_mail($db_handle, $addr, $cont, $from = NULL){
    global $config_current_fahrt_id, $config_mailtag;
    if(is_null($from))
        $from = $db_handle->get("fahrten", "kontakt", array("fahrt_id"=>$config_current_fahrt_id));

    $subj = "Wichtige Information";
    $mess = $cont;
    $tmp = explode("\\\\", $cont);
    if(count($tmp)>1){
        $subj = $tmp[0];
        $mess = $tmp[1];
    }

    $headers = 'From: ' . $from . "\r\n" .
        'Reply-To: ' . $from. "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    return mail($addr, $config_mailtag.$subj, $mess, $headers);
}

function comm_get_lang($lang, $replace){
    global $$lang;
    return str_replace(array_keys($replace), array_values($replace), $$lang);
}
