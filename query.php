<?php

// var_dump($_SERVER);

require_once(__DIR__ . '/lib/backend.php');
require_once(__DIR__ . '/lib/utils.php');
require_once(__DIR__ . '/lib/response.php');
require_once(__DIR__ . '/phpfastcache/phpfastcache.php');


if (!isset($_GET['x']) || !isset($_GET['y']) || !isset($_GET['z']) || !isset($_GET['provider'])) {
	Response::fail(400, 'Missing parameters!');
}

$x = intval($_GET['x']);
$y = intval($_GET['y']);
$z = intval($_GET['z']);

$provider = $_GET['provider'];
$request = $_GET['request'];

$configFile = realpath(__DIR__ . '/config.json');
if (($configFile === null) || !file_exists($configFile)) {
	Response::fail(500, 'No configuration file found!');
}

$config = Utils::json_decode(file_get_contents($configFile));
if ($config === null) {
	Response::fail(500, 'No proper configuration found!');
}


$backend_type = Utils::json_path($config, $provider . '.backend');
$backend_config = Utils::json_path($config, $provider . '.config');
if ($backend_type === null) {
	Response::fail(400, 'Invalid provider!');
}

// var_dump($backend_config);

// TODO: enable client-side caching
Utils::set_eTagHeaders($_SERVER['REQUEST_URI'], filemtime(__FILE__));

$backend = Backend::load($backend_type, $backend_config);
if ($backend === null) {
	Response::fail(500, 'Unable to load backend "' . $backend_type .'"');
}

$backend->initialize($z, $x, $y);


$data = null;
$caching = $backend->caching($request);

if ($caching > 0) {
	$cache = phpFastCache();

	$keyword = $_SERVER['REQUEST_URI'];
	$data = $cache->get($keyword);
}

if ($data == null) {

	switch($request) {
		case 'model':
			$result = $backend->getModel();
			break;
		case 'asset':
			$result = $backend->getAssetData();
			break;
		case 'texture':
			$image = isset($_GET['image']) ? $_GET['image'] : '';
			$format = isset($_GET['format']) ? $_GET['format'] : 'png';
			$result = $backend->getTexture($image, $format);
			break;
		default:
			$result = null;
	}

	if ($caching > 0)
		$cache->set($keyword, $result, $caching);
	
} else {
	$result = $data;
}

if ($result !== null) {

	if ($request == 'texture') {
		Response::image($result, $format);
	} else {
		Response::xml($result);
	}

} else {

	header('HTTP/1.1 404 Not Found');

}

?>
