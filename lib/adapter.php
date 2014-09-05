<?php


abstract class Adapter
{
	protected $srs = 'EPSG:4326';
	protected $endpoint;
	protected $x, $y, $z;

	public function __construct($endpoint) {
		$this->endpoint = $endpoint;
	}
	
	public function initialize($x, $y, $z) {
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
	}
	
	
	public abstract function query($layers);

	protected function queryService($params) {
		// TODO: error handling
		$url = $this->endpoint . '?' . http_build_query($params);
		// die($url);
		
		$data = file_get_contents($url);
		// die($data);
		return $data;
	}
	
	
	protected function xtile($lon, $zoom) {
		return (($lon + 180) / 360) * pow(2, $zoom);
	}
	
	protected function ytile($lat, $zoom) {
		return (1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom);
	}
	
	protected function tile_bounds() {
		$n = pow(2, $this->z);
		return array(
			 $this->x      / $n * 360.0 - 180.0, rad2deg(atan(sinh(pi() * (1.0 - 2.0 * ($this->y + 1) / $n)))),
			($this->x + 1) / $n * 360.0 - 180.0, rad2deg(atan(sinh(pi() * (1.0 - 2.0 * ($this->y    ) / $n))))
		);
	}
}


?>