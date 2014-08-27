<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 8/21/14
 * Time: 10:50 PM
 */

global $config_studitypen, $config_reisearten, $config_essen, $admin_db, $config_current_fahrt_id, $config_admin_verbose_level, $config_verbose_level, $text, $headers, $ajax;


$headers .= '<script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
             <script src="../view/js/jquery.tabletojson.js"></script>
             <script src="../view/js/jquery.jeditable.js"></script>';


$text .= "<h3>Kalkulation</h3>";
$h1 = array("Position", "Anzahl (normal)", "Satz", "Summe");
$s1[3] = array(1,2);
$t1[2] = " €";
$t1[3] = " €";
$d1 = array(array("Reisekosten", 2, 1.9),
           array("Übernachtung (HP)", 2, 17.8),
           array("Bettwäsche", 1, 4),
           array("Grillen", 1, 0.3),
           array("Kurzreisezuschlag", 1, 2));
$text .= html_table($h1, $d1, $s1, $t1, "test");
$text .= '<span id="anc_add">add</span> - <span id="anc_rem">rem</span>';


$text .= "<h2>Einkaufen</h2>";
$h2 = array("Position", "Anzahl", "Satz", "Summe");
$s2[3] = array(1,2);
$t2[2] = " €";
$t2[3] = " €";
$d2 = array(array("Club Mate", 120, 0.69),
    array("Chips", 15,0.5),
    array("Flips", 15, 0.5),
    array("Fanta", 24, 0.39),
    array("Wasser", 42, 0.3));
$text .= html_table($h2, $d2, $s2, $t2);

$text .= "<h3>Rechnung</h3>";
$h3 = array("Position", "Menge", "Anzahl", "Satz", "Summe");
$s3[4] = array(1,2,3);
$t3[3] = " €";
$t3[4] = " €";
$d3 = array(
    array("Übernachtung", 2, 69, 10.5),
    array("Bettwäsche", 1, 75, 4),
    array("Grillnutzung", 1, 69, 0.3),
    array("Kurzreisezuschlag", 1, 69, 2),
    array("Halbpension", 2, 69, 7.3));
$text .= html_table($h3, $d3, $s3, $t3);

$text .= "<h2>Money In/Out</h2>";
$text .= '<div style="float:left">';
$h4 = array("Position", "Summe");
$s4 = array();
$t4[1] = " €";
$d4_out = array(
    array("Frauensee", 2815.1),
    array("Einkauf", 590.13),
    array("Busfahrt", 216),
    array("Bäcker", 22.4),
    array("Kaution", 100)
);
$d4_in = array(
    array("Pfand1", 82.17),
    array("Pfand2", 10),
    array("Pfand3", 15),
    array("Fachschaft (Reste)", 76),
    array("Kollekte", 4620),
    array("Förderung", 2200),
    array("Kaution", 100)
);
$text .= html_table($h4, $d4_out, $s4, $t4);
$text .= '</div><div style="float:left">';
$text .= html_table($h4, $d4_in, $s4, $t4);
$text .= '</div><div style="clear:both"></div>';

$text .="<script type='text/javascript'>
$('#testy').click( function() {
  var table = $('#test').tableToJSON(); // Convert the table into a javascript object
  console.log(table);
  alert(JSON.stringify(table));
});

function ref_editable(){
    $('.edita').editable(function(value, settings){return(value);},
        {
            indicator : '<img src=\'img/indicator.gif\'>',
            tooltip   : 'Click to edit...',
            style  : 'inherit',
            callback : function(value, settings){
                            var table = $('#test').tableToJSON(); // Convert the table into a javascript object
                            console.log(JSON.stringify(table));
                        }
        }
    );
}

$(function(){
    ref_editable();
    
    var cnt = 2;
    $('#anc_add').click(function(){
        $('#test>tbody tr').last().after('<tr><td class=\'edita\'>Static Content ['+cnt+']</td><td class=\'edita\'></td><td class=\'edita\'></td><td class=\'edita\'></td></tr>');
        cnt++;
        ref_editable();
    });
    
    $('#anc_rem').click(function(){
        if($('#test>tbody tr').size()>1){
            $('#test>tbody tr:last-child').remove();
        }else{
            alert('One row should be present in table');
        }
    });
});
</script>";

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
function html_table($header, $data, $sum = array(), $type = array(), $id=""){
	$summy = array();

	$ret = "<table class=\"cost-table\" id=\"".$id."\">
			<thead>
				<tr>\n";
					foreach($header as $h)
						$ret.= "<th>".$h."</th>\n";
	$ret.="		</tr>
			</thead>
			<tbody>\n";
                $cnt = 0;
				foreach($data as $row){
					$ret.="<tr>";
					for($i = 0; $i < count($header); $i++){
                        $cnt++;
						$ret.= "<td".numeric_class($row, $sum, $i)." id='cell".$id.$cnt."'>";
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
	$d = ' class="cost-table-numeric edita"';
	if(isset($a[$c])){
		if(is_numeric($a[$c]))
			return $d;
	}
	if(isset($b[$c]))
		return $d;
    return ' class="edita"';
}
