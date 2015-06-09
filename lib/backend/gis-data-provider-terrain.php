<?php


require_once(__DIR__ . '/../layered-backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../uri-resolver.php');
require_once(__DIR__ . '/../adapter/wfs-adapter.php');
require_once(__DIR__ . '/../adapter/wms-adapter.php');
require_once(__DIR__ . '/../adapter/terrain-adapter.php');
require_once(__DIR__ . '/../builder/block-builder.php');
require_once(__DIR__ . '/../layer/building-layer.php');
require_once(__DIR__ . '/../layer/terrain-layer.php');


class GisDataProviderTerrain extends LayeredBackend
{
	protected $buildings;
	protected $surface;
	protected $terrain;
	
	
	public function initialize($z, $x, $y) {
		parent::initialize($z, $x, $y);
		
		//$this->buildings->initialize($x, $y, $z);
		$this->terrain->initialize($x, $y, $z);

		$this->surface = new WMSAdapter($this->config('wms.endpoint'));
		$this->surface->initialize($x, $y, $z);
	}
	
	protected function getLayers() {
		return array(
			'plane' => $this->getGround(),
			//'buildings' => $this->getBuildings()				
		);
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
	
	
	protected function getGround()
	{
		$this->terrain = new TerrainAdapter($this->config('w3ds.endpoint'));
		
		
		$params = array(
			'layers'  => $this->config('w3ds.params.layers'),
		);
		//must intialize and query in terrain layer!
		return new TerrainLayer($this->terrain, $params);
	}
	
	protected function getBuildings()
	{
		$this->buildings = new WFSAdapter($this->config('wfs.endpoint'));

		$params = array(
			'layers'  => $this->config('wfs.params.layers'),
		);

		return new BuildingLayer(
			$this->buildings,
			$params,
			new BlockBuilder($this->uriResolver)
		);
	}

	
}

?>