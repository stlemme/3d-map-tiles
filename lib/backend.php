<?php


require_once(__DIR__ . '/utils.php');


abstract class Backend
{
	protected $z, $x, $y;
	private $config;
	
	///////////////////////////////////////////////////////////////////////////
	
	public function initialize($z, $x, $y) {
		$this->z = $z;
		$this->x = $x;
		$this->y = $y;
	}
	
	public function caching($request) {
		return intval($this->config('cache'));
	}
	
	abstract public function getModel();
	abstract public function getAssetData();
	public function getTexture($image, $format) {
		return null;
	}
	
	///////////////////////////////////////////////////////////////////////////
	
	public static function load($backend, $config)
	{
		$class = Utils::loadClassFromFile($backend, __DIR__ . '/backend');
		if ($class === null)
			return null;
		return new $class($config);
	}
	
	///////////////////////////////////////////////////////////////////////////
	
	protected function __construct($config) {
		$this->config = array_replace_recursive($this->defaultConfig(), $config);
	}
	
	protected function config($path) {
		return Utils::json_path($this->config, $path);
	}
	
	protected function defaultConfig() {
		return array(
			'cache' => 300
		);
	}
	
}

?>