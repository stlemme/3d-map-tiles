<?php


require_once(__DIR__ . '/../layered-backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../uri-resolver.php');
require_once(__DIR__ . '/../adapter/overpass-adapter.php');
require_once(__DIR__ . '/../builder/block-builder.php');
require_once(__DIR__ . '/../layer/building-layer.php');
require_once(__DIR__ . '/../layer/plane-layer.php');


class OsmGeometry extends LayeredBackend
{
	protected $overpass;
	
	
	public function initialize($z, $x, $y) {
		parent::initialize($z, $x, $y);
		
		$this->overpass->initialize($x, $y, $z);
	}
	
	protected function getLayers() {
		return array(
			'plane' => $this->getGround(),
			'buildings' => $this->getBuildings()				
		);
	}

	
	///////////////////////////////////////////////////////////////////////////
	
	
	protected function getGround()
	{
		return new ExternalPlaneLayer($this->config('osm-url'));
	}
	
	protected function getBuildings()
	{
		$this->overpass = new OverpassAdapter($this->config('endpoint'));

		$params = array(
			// 'layers'  => $this->config('wfs.params.layers'),
		);

		return new BuildingLayer(
			$this->overpass,
			$params,
			new BlockBuilder($this->uriResolver)
		);
	}

	
}

?>