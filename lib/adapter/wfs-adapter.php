<?php


class WFSAdapter
{
	protected $endpoint;
	protected $meshes = array();

	public function __construct($endpoint) {
		$this->endpoint = $endpoint;
	}
	
	protected function extractPolygon2D($poly)
	{
		$geo_vertices = array();
		foreach ($poly as $coords) {
			$geo_vertices[] = $coords[0];
			$geo_vertices[] = $coords[1];
			// ignore z component
		}
		return $geo_vertices;
	}
	
	public function query($params) {
		$url = $this->endpoint . '?' . http_build_query($params);
		// die($url);
		
		$data = file_get_contents($url);
		// die($data);
		
		$collection = json_decode($data);
		// print_r($collection);
		
		$r = array();
		
		foreach ($collection->features as $feature)
		{
			$geom = $feature->geometry;
			
			if ($geom->type != 'Polygon')
				continue;
			
			foreach ($geom->coordinates as $poly)
				$r[] = $this->extractPolygon2D($poly);
		}
		
		$this->meshes = $r;
	}
	
	public function meshes() {
		return $this->meshes;
	}

}


?>