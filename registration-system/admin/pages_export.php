<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 8/26/14
 * Time: 10:05 PM
 */

global $config_studitypen, $config_reisearten, $config_essen, $admin_db, $config_current_fahrt_id, $config_admin_verbose_level, $config_verbose_level, $text, $headers, $ajax;

$text .= '
<ul>
    <li><a href="?page=export&ex=refra">RefRat-Liste</a> (<a href="http://www.refrat.hu-berlin.de/finanzen/TeilnehmerInnenlisteMitEinverstaendniserklaerung.pdf">orig</a>)</li>
    <li><a href="?page=export&ex=treff">Treffpunktanwesenheitsliste</a></li>
    <li><a href="?page=export&ex=konto">Kontodatenliste</a></li>
    <li><a href="?page=export&ex=unter">Anwesenheitsunterschriftsliste für Unterkunft</a></li>
    <li><a href="http://www.refrat.de/docs/finanzen/FormularFSErstattung_sepa_form_2.pdf">Erstattungsformular</a></li>
</ul>
';