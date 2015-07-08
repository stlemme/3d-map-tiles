<?php


require_once(__DIR__ . '/../phpfastcache/phpfastcache.php');


abstract class Adapter
{
	protected $srs = 'EPSG:4326';
	protected $endpoint;
	protected $cache;
	protected $x, $y, $z;
	protected $CACHE_TIME = 1000;


	public function __construct($endpoint) {
		$this->endpoint = $endpoint;
		$this->cache = phpFastCache();
	}
	
	public function initialize($x, $y, $z) {
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
	}
	
	
	public abstract function query($layers);


	protected function queryService($params, $forceRequest = false) {
		$url = $this->endpoint . '?' . http_build_query($params);
		// die($url);

		$data = $this->cache->get($url);

		if ($data === null || $forceRequest) {
			$data = @file_get_contents($url);
			if ($data === false)
				return null;
			
			$this->cache->set($url, $data, $this->CACHE_TIME);
		}
		// die($data);
		return $data;
	}
	
	
	protected function xtile($lon, $zoom) {
		return (($lon + 180) / 360) * pow(2, $zoom);
	}
	
	protected function ytile($lat, $zoom) {
		return (1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom);
	}
	
	// returns: ll, ur -> w, s, e, n
	protected function tile_bounds() {
		$n = pow(2, $this->z);
		return array(
			 $this->x      / $n * 360.0 - 180.0, rad2deg(atan(sinh(pi() * (1.0 - 2.0 * ($this->y + 1) / $n)))),
			($this->x + 1) / $n * 360.0 - 180.0, rad2deg(atan(sinh(pi() * (1.0 - 2.0 * ($this->y    ) / $n))))
		);
	}
	
	protected function toMeters($str)
	{
		$YARD_TO_METER = 0.9144;
		$FOOT_TO_METER = 0.3048;
		$INCH_TO_METER = 0.0254;
		
		$str = '' . $str;
		$value = floatval($str);
		if ($value === $str)
			return $value <<0;
		
		if (strpos($str, 'm') !== false)
			return $value <<0;
		
		if (strpos($str, 'yd') !== false) {
			// die($str);
			return $value * $YARD_TO_METER <<0;
		}
		if (strpos($str, 'ft') !== false) {
			// die($str);
			return $value * $FOOT_TO_METER <<0;
		}
		if (strpos($str, '\'') !== false) {
			// die($str);
			$parts = explode($str, '\'', 2);
			$res = $parts[0]*$FOOT_TO_METER + $parts[1]*$INCH_TO_METER;
			return $res <<0;
		}
		return $value <<0;
	}
}


?>