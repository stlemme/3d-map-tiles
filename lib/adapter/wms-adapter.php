<?php


require_once(__DIR__ . '/../adapter.php');


class WMSAdapter extends Adapter
{
	protected $texture = null;
	protected $format = 'image/png';
	protected $tile_size = 256;

	public function query($params)
	{
		$bbox = $this->tile_bounds();
		
		$params = array(
			'service' => 'WMS',
			'version' => '1.1.0',
			'request' => 'GetMap',
			
			'layers'  => $params['layers'],
			'styles' => $params['styles'],
			
			'bbox' => implode($bbox, ','),
			'srs' => $this->srs,
			'format' => $this->format,

			'width' => $this->tile_size,
			'height' => $this->tile_size,
			'bgcolor' => $params['bgcolor'],
			'transparent' => $params['transparent']
		);
		
		$this->texture = $this->queryService($params);
		
		// TODO: enhance image handling, e.g. filters, error handling, stitching, etc.
	}
	
	public function texture() {
		return $this->texture;
	}

}


?>