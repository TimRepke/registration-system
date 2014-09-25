<?php

function captchaForCode($code)
{
	$img = imagecreatetruecolor(320,96);

	$bgcol = imagecolorallocate($img, 0, 0, rand(0, 40));
	$code_len = strlen($code);
	if ($code_len < 1) {
			die("Not OK");
	}
	imagefill($img, 0, 0, $bgcol);
	for($x = 0; $x < $code_len; $x++){
			$col = imagecolorallocate($img, rand(80, 255), rand(80, 255), rand(80, 255));
			imagettftext($img, 40, 0, 20 + (45 * $x), 64, $col, "km.ttf", substr($code, $x, 1));
	}

	header('Content-Type: image/png');
	header('Cache-control: no-cache, no-store');

	// Send image
	imagepng($img);
	imagedestroy($img);
}
function generateRandomString($length = 6) {
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // abcdefghijklmnopqrstuvwxyz
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

captchaForCode(generateRandomString());

?>
