<?php


require_once(__DIR__ . '/../layered-backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../uri-resolver.php');
require_once(__DIR__ . '/../adapter/wfs-adapter.php');
require_once(__DIR__ . '/../adapter/wms-adapter.php');
require_once(__DIR__ . '/../builder/block-builder.php');
require_once(__DIR__ . '/../layer/building-layer.php');
require_once(__DIR__ . '/../layer/plane-layer.php');


class GisDataProvider extends LayeredBackend
{
	protected $buildings;
	protected $surface;
	
	
	public function initialize($z, $x, $y) {
		parent::initialize($z, $x, $y);
		
		$this->buildings->initialize($x, $y, $z);
		
		$this->surface = new WMSAdapter($this->config('wms.endpoint'));
		$this->surface->initialize($x, $y, $z);
	}
	
	
	protected function getLayers() {
		return array(
			'plane' => $this->getGround(),
			'buildings' => $this->getBuildings()				
		);
	}
	
	public function getTexture($image, $format) {
		if ($image != '')
			return null;
		
		$params = array(
			'layers'  => $this->config('wms.params.layers'),
			'styles' => $this->config('wms.params.styles'),
			'bgcolor' => $this->config('wms.params.bgcolor'),
			'format' => $this->config('wms.params.format'),
			'transparent' => ($this->config('wms.params.transparent') ? 'true' : 'false'),
			'tile_size' => $this->config('texture.resolution')
		);
		
		// TODO: error handling - default texture
		$this->surface->query($params);
		
		return $this->surface->texture();
	}
	
	
	///////////////////////////////////////////////////////////////////////////
	
	
	protected function getGround()
	{
		return new PlaneLayer($this->config('texture.preference'));
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

	protected function defaultConfig() {
		$config = array(
			'wfs' => array(
				'params' => array()
			),
			'wms' => array(
				'params' => array(
					'styles' => '',
					'bgcolor' => '0xFF8000',
					'transparent' => false,
					'format' => 'image/jpeg'
				)
			),
			'w3ds' => array(
				'params' => array()
			),
			'texture' => array(
				'preference' => 'png',
				'resolution' => 256
			)
		);
		
		return array_replace_recursive(parent::defaultConfig(), $config);
	}
	
}

?>