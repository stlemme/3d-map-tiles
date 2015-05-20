<?php


require_once(__DIR__ . '/../adapter.php');


class TerrainAdapter extends Adapter
{
	protected $terrain = null;
	protected $format = 'application/octet-stream';
	//protected $tile_size = 256;
	protected $lod=4;
	protected $size;
	//protected $sizey;
	protected $data;

	public function query($params)
	{
		// var options = {
		//	"version": "0.4",
		//	"service": "w3ds",
		//	"request": "GetScene",
		//	"crs": "EPSG:3047",
		//	"format": "application/octet-stream",
		//	"layers": "fiware:pallas-terrain",
		//	"boundingbox": "374000,7548000,375202,7549200",
		//	"LOD": "4"
		// };
	
	
		$bbox = $this->tile_bounds();
		
		$params = array(
			'service' => 'w3ds',
			'version' => '0.4',
			'request' => 'GetScene',
			
			'layers'  => $params['layers'],
			
			//'bbox' => implode($bbox, ','),
			'boundingbox' => implode($bbox, ','),
			'srs' => $this->srs,
			'format' => $this->format,

			//'width' => $this->tile_size,
			//'height' => $this->tile_size,
			//'bgcolor' => $params['bgcolor'],
			//'transparent' => $params['transparent']
		);
		
		$this->terrain = $this->queryService($params);
		
		$list = array_merge(unpack("l/l/f*", $this->terrain));
		$this->size=bindec ($list[0]);
		//$this->sizey=$list[1];
		$this->data=array_slice($list, 4);
		// TODO: enhance image handling, e.g. filters, error handling, stitching, etc.
	}
	
	public function size() {
		return [$this->size];
	}
	
	
	public function data() {
		return $this->data;
	}

}


?>