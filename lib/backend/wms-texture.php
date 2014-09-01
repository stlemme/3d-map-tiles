<?php


require_once(__DIR__ . '/../backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../xml3d.php');


class WmsTexture extends Backend
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
	
	protected function tile_bounds($xtile, $ytile, $zoom) {
		$n = pow(2, $zoom);
		return array(
			 $xtile      / $n * 360.0 - 180.0, rad2deg(atan(sinh(pi() * (1 - 2 *  $ytile      / $n)))),
			($xtile + 1) / $n * 360.0 - 180.0, rad2deg(atan(sinh(pi() * (1 - 2 * ($ytile + 1) / $n))))
		);
	}
	
	
	public function getTexture() {
		// $bbox = array(
			// 427481,7210400,
			// 428200,7211000
		// );
		$bbox = $this->tile_bounds($this->x, $this->y, $this->z);
		// $srs = 'EPSG:3067';
		$srs = 'EPSG:3857';
		$format = 'image/png';
		
		$params = array(
			'service' => 'WMS',
			'version' => '1.1.0',
			'request' => 'GetMap',
			
			'layers'  => $this->config('params.layers'),
			'styles' => $this->config('params.styles'),
			
			'bbox' => implode($bbox, ','),
			'srs' => $srs,
			'format' => $format,

			'width' => 256,
			'height' => 256
		);
		
		$url = $this->config('endpoint') . '?' . http_build_query($params);
		// echo $url;
		// exit;
		
		//$url = 'http://localhost/api/tiles/filab/' . $this->z . '/' . $this->x . '/' . $this->y . '.png';
		//$url = 'http://a.tile.openstreetmap.org/' . $this->z . '/' . $this->x . '/' . $this->y . '.png';
		
		$data = file_get_contents($url);

		return $data;
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
		$src = $this->y . '-texture.png';
		$tex->addImage($src);
		return $tex;
	}

	///////////////////////////////////////////////////
	
	protected function __construct($config) {
		parent::__construct($config);
	}
	
}

?>