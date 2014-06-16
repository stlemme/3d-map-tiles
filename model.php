<?php


require_once(__DIR__ . '/utils.php');


if (!isset($_GET['x']) || !isset($_GET['y']) || !isset($_GET['z']) || !isset($_GET['provider'])) {
	header('HTTP/1.1 404 Not Found');
	die('Missing parameters!');
}

$x = intval($_GET['x']);
$y = intval($_GET['y']);
$z = intval($_GET['z']);

$provider = $_GET['provider'];

set_eTagHeaders(__FILE__, filemtime(__FILE__));

header('HTTP/1.1 200 Found');
header('Content-type: application/xml');

?>
<?xml version="1.0" encoding="UTF-8"?>
<xml3d xmlns="http://www.xml3d.org/2009/xml3d">
	<model src="<?php echo $y; ?>-asset.xml#asset" transform="<?php echo $y; ?>-asset.xml#tf"/>
</xml3d>
