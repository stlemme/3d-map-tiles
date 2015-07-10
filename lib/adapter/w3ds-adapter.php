<?php


require_once(__DIR__ . '/../service-adapter.php');


class W3DSAdapter extends ServiceAdapter
{
	protected $format = 'json';

	public function query($params)
	{
		// TODO: retrieve elevation model
		///////////////////////////////////////////////////
		// $bbox = $this->tile_bounds($this->x, $this->y, $this->z);
		// $crs = 'EPSG:4326';
		// $format = 'model/xml3d+xml';
		// $lod = 6;
		
		// $params = array(
			// 'service' => 'w3ds',
			// 'version' => '0.4',
			// 'request' => 'GetScene',
			
			// 'layers'  => $this->config('w3ds.params.layers'),
			
			// 'boundingbox' => implode($bbox, ','),
			// 'crs' => $crs,

			// 'LOD' => $lod
		// );
		
		// $url = $this->config('w3ds.endpoint') . '?' . http_build_query($params) . '&format=' . $format;
		
		// $data = file_get_contents($url);
		// die($data);

		// return $data;
	}
}


?>