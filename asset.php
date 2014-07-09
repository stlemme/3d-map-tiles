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

$baseurl = dirname($_SERVER['SCRIPT_NAME']);

//$texture = $y . '-texture.png';
$texture = 'http://' . $_SERVER['HTTP_HOST'] . '/api/tiles/filab/' . $z . '/' . $x . '/' . $y . '.png';
$mesh = 'http://' . $_SERVER['HTTP_HOST'] . $baseurl . '/basic.xml';

?>
<?xml version="1.0" encoding="UTF-8"?>
<xml3d xmlns="http://www.xml3d.org/2009/xml3d">
	<defs>
		<transform id="tf" translation="<?php echo $x; ?> 0 <?php echo $y; ?>"/>
	</defs>
	<asset id="asset">
		<assetmesh meshtype="triangles" shader="<?php echo $mesh; ?>#shader_surface">
			<data src="<?php echo $mesh; ?>#mesh_ground"/>
			<texture name="diffuseTexture">
				<img src="<?php echo $texture; ?>"/>
			</texture>
		</assetmesh>
	</asset>
</xml3d>
