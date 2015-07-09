<?php


require_once(__DIR__ . '/osm-texture.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../uri-resolver.php');
require_once(__DIR__ . '/../adapter/overpass-adapter.php');
require_once(__DIR__ . '/../builder/block-builder.php');
require_once(__DIR__ . '/../layer/building-layer.php');


class OsmGeometry extends OsmTexture
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
	
	
	
	protected function getBuildings()
	{
		$this->overpass = new OverpassAdapter($this->config('overpass.endpoint'));

		$params = array(
			// 'layers'  => $this->config('wfs.params.layers'),
		);

		return new BuildingLayer(
			$this->overpass,
			$params,
			new BlockBuilder($this->uriResolver)
		);
	}

	protected function defaultConfig() {
		$config = array(
		);
		
		return array_replace_recursive(parent::defaultConfig(), $config);
	}

}

?>