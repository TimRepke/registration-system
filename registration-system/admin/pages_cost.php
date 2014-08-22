<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 8/21/14
 * Time: 10:50 PM
 */

global $config_studitypen, $config_reisearten, $config_essen, $admin_db, $config_current_fahrt_id, $config_admin_verbose_level, $config_verbose_level, $text, $headers, $ajax;

$text .= "<h2>Unterkunft</h2>";
$h = array("Position", "Anzahl", "Satz", "Summe");
$s[3] = array(1,2);
$t[2] = " €"; 
$t[3] = " €";

$text .= "<h3>Kalkulation</h3>";
$d = array(array("Übernachtung", 2, 10.50),
           array("Zwiebel", 300, 5),
           array("Brötchen", 200, 0.2));


$text .= html_table($h, $d, $s, $t);
$text .= "<h3>Rechnung</h3>";
$text .= html_table($h, $d, $s, $t);

$text .= "<h2>Einkaufen</h2>";
$text .= html_table($h, $d, $s, $t);

$text .= "<h2>Money In/Out</h2>";
$text .= html_table($h, $d, $s, $t);


/**
 * $headers 
 *    is an array of the headers
 * $data
 *    is an array with the data to output
 * $sum 
 *    is an array declaring which cols should be summed up and put below the table, 
 *    second dimension declares which cols need to be multiplied (if no second dimension, just sum at the end)
 * $type 
 *    is an array declaring type of data to put behind the value (i.e. €), not all cols need to be declared
 *
 * return value: variable containing the html code for echo
 */
function html_table($header, $data, $sum = array(), $type = array()){
	$summy = array();
	
	$ret = "<table class=\"cost-table\">
			<thead>
				<tr>\n";
					foreach($header as $h)
						$ret.= "<th>".$h."</th>\n";
	$ret.="		</tr>
			</thead>
			<tbody>\n";
				foreach($data as $row){
					$ret.="<tr>";
					for($i = 0; $i < count($header); $i++){
						$ret.= "<td".numeric_class($row, $sum, $i).">";
						if(isset($row[$i])){
							$ret .= prepval($row[$i],(isset($type[$i]) ? $type[$i] : ""));
							if(isset($sum[$i])){
								if(!isset($summy[$i]))
									$summy[$i] = $row[$i];
								else
									$summy[$i] += $row[$i];
							}
						} elseif(isset($sum[$i])) {
							if(count($sum[$i])>1){
								$tmp = NULL;
								foreach($sum[$i] as $s){
									$tmp = (is_null($tmp)) ? $row[$s] : $tmp*$row[$s];
								}
								$ret .= prepval($tmp,(isset($type[$i]) ? $type[$i] : ""));
								
								if(!isset($summy[$i]))
									$summy[$i] = $tmp;
								else
									$summy[$i] += $tmp;
							} else {
								// do nothing, sum at the end
							}
						}
						$ret.="</td>";
					}
					$ret.="</tr>";
				}
	$ret.= "</tbody>\n";
	if(count($sum)>0){
		$ret.= "<tfoot>
					<tr>\n";
			for($i = 0; $i < count($header); $i++){
				if(isset($sum[$i])){
					$ret.='<td>'.prepval($summy[$i],(isset($type[$i]) ? $type[$i] : "")).'</td>';
				} else {
					$ret.='<td class="cost-table-invisible"></td>';
				}
			}
		$ret.= "	</tr>
				</tfoot>\n";
	}
	$ret.= "</table>";
	
	return $ret;
}

function prepval($val, $post){
	if(strpos($post, "€")!==false)
		return number_format($val, 2, ',', ' ').$post;
	return $val.$post;
}

function numeric_class($a,$b,$c){
	$d = ' class="cost-table-numeric"';
	if(isset($a[$c])){
		if(is_numeric($a[$c]))
			return $d;
	}
	if(isset($b[$c]))
		return $d;
}
