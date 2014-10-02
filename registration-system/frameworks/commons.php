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

function comm_from_mysqlDate($date){
    return date('d.m.Y', strtotime($date));
}

function comm_get_possible_dates($db, $fid){
    $dates = $db->get("fahrten", ["von", "bis"], ["fahrt_id" => $fid]);
    $end = new DateTime($dates['bis']);
    $period = new DatePeriod(
        new DateTime($dates['von']),
        new DateInterval('P1D'),
        $end->modify( '+1 day' )
    );
    $ret = [];
    foreach($period as $d){
        array_push($ret, $d->format("d.m.Y"));
    }
    return $ret;

}

/*
 * returns TRUE iff registration is allowed
 */
function comm_isopen_fid($db_handle, $fid){
    $ret = comm_isopen_fid_helper($db_handle, $fid);
    return $ret == 0;
}

/*
 * returns value depending on registration status
 * 0 = registration open (slots available)
 * 1 = all slots taken -> waitlist open
 * 2 = registration closed!
 */
function comm_isopen_fid_helper($db_handle, $fid){
    comm_verbose(3,"checking if fid ". $fid . " is open");
    $open = $db_handle->has('fahrten', ['AND' => ['fahrt_id'=>$fid, 'regopen'=>1]]);
    if(!$open)
        return 2;

    $cnt = $db_handle->count("bachelor", ["AND"=>
        ["backstepped" => NULL,
            "fahrt_id"    => $fid]]);
    $max = $db_handle->get("fahrten", "max_bachelor", ["fahrt_id" => $fid]);
    $wl = $db_handle->count('waitlist', ['AND' =>
        ["transferred" => NULL,
         "fahrt_id"    => $fid]]);

    comm_verbose(3,"cnt: ".$cnt.", max: ".$max.", open: ".($open ? "yes" : "no"));

    if ( $cnt < $max && $wl == 0 )
        return 0;

    return 1;
}

function comm_generate_key($db_handle, $check, $conditions){
    again:
    $bytes = openssl_random_pseudo_bytes(8);
    $hex   = bin2hex($bytes);
    comm_verbose(3,"generated hex for test: ".$hex);

    foreach($check as $table => $col){
        if($db_handle->has($table, array("AND"=>[$col => $hex]))) goto again;
    }

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
