<?php


require_once(__DIR__ . '/../backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../xml3d.php');
require_once(__DIR__ . '/../adapter/wfs-adapter.php');
require_once(__DIR__ . '/../adapter/wms-adapter.php');


class GisDataProvider extends Backend
{
	protected $buildings;
	protected $surface;
	
	
	public function initialize($z, $x, $y) {
		parent::initialize($z, $x, $y);
		
		$this->buildings = new WFSAdapter(
			$this->config('wfs.endpoint'),
			$x, $y, $z
		);
		
		$this->surface = new WMSAdapter(
			$this->config('wms.endpoint'),
			$x, $y, $z
		);
	}
	
	protected function getLayers() {
		return array('plane', 'buildings');
	}
	
	public function usecaching($request) {
		return false; // $request != 'model';
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
			switch($layer) {
				case 'plane': $this->getGround($a); continue;
				case 'buildings': $this->getBuildings($a); continue;
			}
		}
		return $xml3d;
	}
	
	public function getTexture() {
		$params = array(
			'layers'  => $this->config('wms.params.layers'),
			'styles' => $this->config('wms.params.styles'),
			'bgcolor' => $this->config('wms.params.bgcolor'),
			'transparent' => ($this->config('wms.params.transparent') ? 'true' : 'false')
		);
		
		$this->surface->query($params);
		return $this->surface->texture();
	}
	
	
	///////////////////////////////////////////////////////////////////////////
	
	
	protected function getGround($asset)
	{
		// TODO: $this->elevation->query();
		
		$m = $asset->addAssetMesh();
		$m->setShader($this->getShaderReference('surface'));
		$m->setMeshtype('triangles');
		$m->addData($this->getGeometryReference());
		$m->addChild($this->getTextureReference());
	}
	
	protected function calcBoundingBox($vertices)
	{
		$c = count($vertices);
		$bbox = array(
			'minx' => 1.0,
			'maxx' => 0.0,
			'minz' => 1.0,
			'maxz' => 0.0
		);

		for ($i=0; $i<$c; $i+=3)
		{
			$x = $vertices[$i  ];
			$z = $vertices[$i+2];
			$bbox['minx'] = min($bbox['minx'], $x);
			$bbox['maxx'] = max($bbox['maxx'], $x);
			$bbox['minz'] = min($bbox['minz'], $z);
			$bbox['maxz'] = max($bbox['maxz'], $z);
		}
		
		return $bbox;
	}

	
	protected function generateOutline($mesh, $vertices)
	{
		// export as single colored outline
		$mesh->setShader($this->getShaderReference('silhouette'));
		$mesh->setMeshtype('linestrips');
		$data = $mesh->addData();
		$data->filter("rename({position: contour})");
		$data->addChild(new Float3('contour', $vertices));
	}

	protected function generatePolygon($mesh, $vertices)
	{
		// export as single colored triangulated polygon
		$mesh->setShader($this->getShaderReference('silhouette'));
		$mesh->setMeshtype('triangles');
		$data = $mesh->addData();
		$data->compute("dataflow['" . $this->getDataFlowReference('triangulate') . "']");
		$data->addChild(new Float3('contour', $vertices));
	}
	
	protected function generateBlock($mesh, $vertices)
	{
		$height = 5.0;
		
		$mesh->setShader($this->getShaderReference('building'));
		$mesh->setMeshtype('triangles');
		$data = $mesh->addData();
		$data->compute("dataflow['" . $this->getDataFlowReference('extrude') . "']");
		$data->addChild(new Float3('contour', $vertices));
		$data->addChild(new Float('height', array($height)));
	}
	
	protected function getBuildings($asset)
	{
		$params = array(
			'layers'  => $this->config('wfs.params.layers'),
		);

		$this->buildings->query($params);
		
		foreach ($this->buildings->meshes() as $vertices)
		{
			$mesh_bbox = $this->calcBoundingBox($vertices);
			
			// cull features with the center outside of the current tile
			if (abs(0.5 * ($mesh_bbox['minx'] + $mesh_bbox['maxx']) - 0.5) > 0.5) continue;
			if (abs(0.5 * ($mesh_bbox['minz'] + $mesh_bbox['maxz']) - 0.5) > 0.5) continue;

			$m = $asset->addAssetMesh();

			// $this->generateOutline($m, $vertices);
			// $this->generatePolygon($m, $vertices);
			$this->generateBlock($m, $vertices);
			// DEBUG:
			// break;
		}
	}
	
	protected function getDataFlowReference($flow) {
		return $this->getBaseUrl() . '/basic.xml#' . $flow;
	}
	
	protected function getShaderReference($part) {
		return $this->getBaseUrl() . '/basic.xml#shader_' . $part;
	}
	
	protected function getBaseUrl() {
		return 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
	}

	protected function getGeometryReference() {
		return $this->getBaseUrl() . '/basic.xml#mesh_ground';
	}
	
	protected function getTextureReference() {
		$tex = new Texture('diffuseTexture');
		$src = $this->y . '-texture.png';
		$tex->addImage($src);
		return $tex;
	}

	///////////////////////////////////////////////////
	
	// protected function __construct($config) {
		// parent::__construct($config);
	// }
	
}

?>