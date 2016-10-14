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

/*
 * sends mail
 *
 * first line of $cont is used as subject iff terminated by double backslash (\\)
 * note that there should be no "\\" anywhere else in the string!!!
 *
 * returns true/false depending on success
 */
function comm_send_mail($addr, $cont, $from = NULL, $bcc = NULL){
    global $config_mailtag;

    $subj = "Wichtige Information";
    $mess = $cont;
    $tmp = explode("\\\\", $cont);
    if(count($tmp)>1){
        $subj = $tmp[0];
        $mess = $tmp[1];
    }
    $subj = $config_mailtag.$subj;

    $headers = 'From: ' . $from . "\r\n" .
        'Reply-To: ' . $from. "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    if (!is_null($bcc)) $headers .= "\r\nBcc: " . $bcc;

    comm_verbose(3, "sending mail... from: ".$from."<br/>to:".$addr."<br />subject: ".$subj."<br/>content:".$mess);

    return mail($addr, $subj, $mess, $headers);
}

function comm_get_lang($lang, $replace){
    global $config_basepath;
    //require_once($config_basepath."/lang.php");
    //echo $config_basepath."/lang.php";
    global $$lang;

    comm_verbose(3,"found lang variable: <br />".$$lang);
    return str_replace(array_keys($replace), array_values($replace), $$lang);
}
