<?php
$path = base64_decode(str_rot13($_GET['q']));
$check = $_GET['c'];
$mime = base64_decode(str_rot13($_GET['m']));
if ($check != md5($path))
	return;

if (file_exists($path)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($path));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: '.filesize($path));
	readfile($path);
}
?>