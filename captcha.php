<?php
session_start();

$code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
$_SESSION['captcha_code'] = $code;

$image = imagecreatetruecolor(160, 50);

$bg = imagecolorallocate($image, 227, 242, 253);
$text = imagecolorallocate($image, 13, 71, 161);
$noise = imagecolorallocate($image, 21, 101, 192);

imagefilledrectangle($image, 0, 0, 160, 50, $bg);

for ($i = 0; $i < 8; $i++) {
    imageline($image, rand(0, 160), rand(0, 50), rand(0, 160), rand(0, 50), $noise);
}

imagestring($image, 5, 45, 16, $code, $text);

header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>