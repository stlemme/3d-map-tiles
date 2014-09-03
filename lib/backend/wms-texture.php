<?php


require_once(__DIR__ . '/../backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../xml3d.php');
require_once(__DIR__ . '/../adapter/wfs-adapter.php');


class WmsTexture extends Backend
{
	protected $buildings;
	
	
	protected function getLayers() {
		return array('plane', 'buildings');
	}
	
	public function usecaching($request) {
		return $request == 'texture';
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
	
	protected function getGround($asset)
	{
		// TODO: retrieve elevation model
		///////////////////////////////////////////////////
		// $bbox = $this->tile_bounds($this->x, $this->y, $this->z);
		// $crs = 'EPSG:4326';
		// $format = 'model/xml3d+xml';
		// $lod = 6;
		
		// $params = array(
			// 'service' => 'w3ds',
			// 'version' => '0.4',
			// 'request' => 'GetScene',
			
			// 'layers'  => $this->config('w3ds.params.layers'),
			
			// 'boundingbox' => implode($bbox, ','),
			// 'crs' => $crs,

			// 'LOD' => $lod
		// );
		
		// $url = $this->config('w3ds.endpoint') . '?' . http_build_query($params) . '&format=' . $format;
		
		// $data = file_get_contents($url);
		// die($data);

		// return $data;
		$m = $asset->addAssetMesh();
		$m->setShader($this->getShader('surface'));
		$m->setMeshtype('triangles');
		$m->addData($this->getGeometry());
		$m->addChild($this->getTextureReference());
	}
	
	protected function projectVertices($vertices, &$bbox) {
		// $bbox = $this->tile_bounds($this->x, $this->y, $this->z);
		$c = count($vertices);
		$r = array();
		$bbox['minx'] = 1.0;
		$bbox['maxx'] = 0.0;
		$bbox['minz'] = 1.0;
		$bbox['maxz'] = 0.0;
		$y = 0.1; // TODO: use config for that
		for ($i=0; $i<$c; $i+=2)
		{
			$x = $this->xtile($vertices[$i  ], $this->z) - $this->x;
			$z = $this->ytile($vertices[$i+1], $this->z) - $this->y;
			
			$r[] = $x;
			$r[] = $y;
			$r[] = $z;
			
			$bbox['minx'] = min($bbox['minx'], $x);
			$bbox['maxx'] = max($bbox['maxx'], $x);
			$bbox['minz'] = min($bbox['minz'], $z);
			$bbox['maxz'] = max($bbox['maxz'], $z);
		}
		return $r;
	}
	
	protected function getBuildings($asset)
	{
		$bbox = $this->tile_bounds($this->x, $this->y, $this->z);
		$srs = 'EPSG:4326';
		$format = 'json';
		// $tile_size = 256;
		
		$params = array(
			'service' => 'WFS',
			'version' => '2.0.0',
			'request' => 'GetFeature',
			
			'typeName'  => $this->config('wfs.params.layers'),
			
			'bbox' => implode($bbox, ',') . ',' . $srs,
			'srsName' => $srs,
			'outputFormat' => $format,

		);

		$this->buildings->query($params);
		
		foreach ($this->buildings->meshes() as $vertices)
		{
			$mesh_bbox = array();
			// and map lat/lon of geometry to tile coords
			$proj_vert = $this->projectVertices($vertices, $mesh_bbox);
			// print_r($mesh_bbox);
			// exit;
			
			// TODO: cull features with the center outside of the current tile
			if (abs(0.5 * ($mesh_bbox['minx'] + $mesh_bbox['maxx']) - 0.5) > 0.5) continue;
			if (abs(0.5 * ($mesh_bbox['minz'] + $mesh_bbox['maxz']) - 0.5) > 0.5) continue;

			$m = $asset->addAssetMesh();

			// export as single colored silhouette
			$m->setShader($this->getShader('silhouette'));
			$m->setMeshtype('linestrips');
			$pos = new Float3('position', $proj_vert);
			$m->addChild($pos);

			// TODO: extrude buildings
		}
	}
	
	public function getAssetData()
	{
		$xml3d = new Xml3d();
		$defs = $xml3d->addDefs();
		$tf = $defs->addTransform($this->x . ' 0 ' . $this->y);
		$tf->setId('tf');
		foreach($this->getLayers() as $layer) {
			$a = $xml3d->addAsset($layer);
			switch($layer) {
				case 'plane': $this->getGround($a); continue;
				case 'buildings': $this->getBuildings($a); continue;
			}
		}
		return $xml3d;
	}
	
	protected function xtile($lon, $zoom) {
		return (($lon + 180) / 360) * pow(2, $zoom);
	}
	
	protected function ytile($lat, $zoom) {
		return (1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom);
	}
	
	protected function tile_bounds($xtile, $ytile, $zoom) {
		$n = pow(2, $zoom);
		return array(
			 $xtile      / $n * 360.0 - 180.0, rad2deg(atan(sinh(pi() * (1.0 - 2.0 * ($ytile + 1) / $n)))),
			($xtile + 1) / $n * 360.0 - 180.0, rad2deg(atan(sinh(pi() * (1.0 - 2.0 * ($ytile    ) / $n))))
		);
	}
	
	
	public function getTexture() {
		$bbox = $this->tile_bounds($this->x, $this->y, $this->z);
		$srs = 'EPSG:4326';
		$format = 'image/png';
		$tile_size = 256;
		
		$params = array(
			'service' => 'WMS',
			'version' => '1.1.0',
			'request' => 'GetMap',
			
			'layers'  => $this->config('wms.params.layers'),
			'styles' => $this->config('wms.params.styles'),
			
			'bbox' => implode($bbox, ','),
			'srs' => $srs,
			'format' => $format,

			'width' => $tile_size,
			'height' => $tile_size,
			'bgcolor' => $this->config('wms.params.bgcolor'),
			'transparent' => ($this->config('wms.params.transparent') ? 'true' : 'false')
		);
		
		$url = $this->config('wms.endpoint') . '?' . http_build_query($params);
		
		$data = file_get_contents($url);

		return $data;
	}
	
	public function getShader($part) {
		return $this->getBaseUrl() . '/basic.xml#shader_' . $part;
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
		$this->buildings = new WFSAdapter($this->config('wfs.endpoint'));
	}
	
}

?>