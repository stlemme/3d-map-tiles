<?php


require_once(__DIR__ . '/../adapter.php');


class WMSAdapter extends Adapter
{
	protected $texture = null;

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
			'format' => $params['format'],

			'width' => $params['tile_size'],
			'height' => $params['tile_size'],
			'bgcolor' => $params['bgcolor'],
			'transparent' => $params['transparent']
		);

		// TODO: enhance image handling, e.g. filters, error handling, stitching, etc.
		
		$result = $this->queryService($params);
		if ($result === null)
			return false;
		
		$img = @imagecreatefromstring($result);
		if ($img === false)
			return false;
		
		$this->texture = $img;
		return true;
	}
	
	public function texture() {
		return $this->texture;
	}

}


?>