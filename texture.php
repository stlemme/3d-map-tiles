<?php

if (!isset($_GET['x']) || !isset($_GET['y']) || !isset($_GET['z']) || !isset($_GET['provider'])) {
	header('HTTP/1.1 404 Not Found');
	die('Missing parameters!');
}

$x = intval($_GET['x']);
$y = intval($_GET['y']);
$z = intval($_GET['z']);

$provider = $_GET['provider'];

header('HTTP/1.1 200 Found');
header('Content-type: image/png');

// TODO: output image data

?>