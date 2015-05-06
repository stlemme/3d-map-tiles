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
	
	
	public function useCaching($request) {
		return $request != 'model';
	}
	
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
	
	public function getTexture() {
		//handle texture here
		$im = @ImageCreate (512, 512);
		$im00 = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+1) . '/' . ($this->x*2) . '/' . ($this->y*2) . '.png');
		$im01 = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+1) . '/' . ($this->x*2) . '/' . ($this->y*2+1) . '.png');
		$im10 = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+1) . '/' . ($this->x*2+1) . '/' . ($this->y*2) . '.png');
		$im11 = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+1) . '/' . ($this->x*2+1) . '/' . ($this->y*2+1) . '.png');
		imagecopy($im, $im00, 0, 0, 0, 0, 256, 256);
		imagecopy($im, $im01, 0, 256, 0, 0, 256, 256); 
		imagecopy($im, $im10, 256, 0, 0, 0, 256, 256); 
		imagecopy($im, $im11, 256, 256, 0, 0, 256, 256); 		
		return $im;
	}

	
	///////////////////////////////////////////////////////////////////////////
	
	
	protected function getGround()
	{
		//change url here
		//todo: use path from config
		return new ExternalPlaneLayer('http://127.0.0.1/api/3d-map-tiles/sb',true);
		//return new ExternalPlaneLayer($this->config('osm-url'));
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