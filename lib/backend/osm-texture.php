<?php


require_once(__DIR__ . '/../backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../xml3d.php');


class OsmTexture extends Backend
{
	protected function getLayers() {
		return array('plane');
	}
	
	public function getModel()
	{
		$xml3d = new Xml3d();
		foreach($this->getLayers() as $layer) {
			$model = $xml3d->addModel($this->y . '-asset.xml#' . $layer);
			$model->setTransform($this->y . '-asset.xml#tf');
		}
		return $xml3d;
	}
	
	public function getAssetData()
	{
		$xml3d = new Xml3d();
		$defs = $xml3d->addDefs();
		$tf = $defs->addTransform($this->x . ' 0 ' . $this->y);
		$tf->setId('tf');
		foreach($this->getLayers() as $layer) {
			$a = $xml3d->addAsset($layer);
			$m = $a->addAssetMesh();
			$m->setShader($this->getShader());
			$m->setMeshtype('triangles');
			$m->addData($this->getGeometry());
			$m->addChild($this->getTextureReference());
		}
		return $xml3d;
	}
	
	public function getTexture() {
		return null;
	}
	
	public function getShader() {
		return $this->getBaseUrl() . '/basic.xml#shader_surface';
	}
	
	public function getBaseUrl() {
		return 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
	}

	public function getGeometry() {
		return $this->getBaseUrl() . '/basic.xml#mesh_ground';
	}
	
	public function getTextureReference() {
		$tex = new Texture('diffuseTexture');
		// TODO: use map replace
		$url = $this->config('url');
		// var_dump($url);
		$src = $url . '/' . $this->z . '/' . $this->x . '/' . $this->y . '.png';
		$tex->addImage($src);
		return $tex;
	}

	public static function load($provider, $config)
	{
		$class = Utils::loadClassFromFile($provider, __DIR__ . '/backend');
		if ($class === null)
			return null;
		return new $class($config);
	}
	
	///////////////////////////////////////////////////
	
	protected function __construct($config) {
		parent::__construct($config);
	}
	
}

?>