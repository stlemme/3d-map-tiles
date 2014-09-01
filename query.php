<?php

// var_dump($_SERVER);

require_once(__DIR__ . '/lib/backend.php');
require_once(__DIR__ . '/lib/utils.php');
require_once(__DIR__ . '/lib/response.php');

if (!isset($_GET['x']) || !isset($_GET['y']) || !isset($_GET['z']) || !isset($_GET['provider'])) {
	Response::fail(400, 'Missing parameters!');
}

$x = intval($_GET['x']);
$y = intval($_GET['y']);
$z = intval($_GET['z']);

$provider = $_GET['provider'];
$request = $_GET['request'];

$configFile = realpath(__DIR__ . '/config.json');
if ($configFile === null) {
	Response::fail(500, 'No configuration file found!');
}

$config = Utils::json_decode(file_get_contents($configFile));
if ($config === null) {
	Response::fail(500, 'No proper configuration found!');
}


$backend_type = Utils::json_path($config, $provider . '.backend');
$backend_config = Utils::json_path($config, $provider . '.config');

// var_dump($backend_config);

// Utils::set_eTagHeaders(__FILE__, filemtime(__FILE__));

$backend = Backend::load($backend_type, $backend_config);

$backend->initialize($z, $x, $y);


switch($request) {
	case 'model':
		$result = $backend->getModel();
		break;
	case 'asset':
		$result = $backend->getAssetData();
		break;
	case 'texture':
		$result = $backend->getTexture();
		break;
	default:
		$result = null;
}

if ($result !== null) {

	if ($request == 'texture') {
		Response::image($result, "image/png");
	} else {
		Response::xml($result);
	}

} else {

	header('HTTP/1.1 404 Not Found');

}

?>