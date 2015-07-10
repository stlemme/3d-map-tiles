<?php


require_once(__DIR__ . '/../geometry-adapter.php');


class WFSAdapter extends GeometryAdapter
{
	protected $format = 'json';

	public function query($params)
	{
		$bbox = $this->tile_bounds();
		
		$params = array(
			'service' => 'WFS',
			'version' => '2.0.0',
			'request' => 'GetFeature',
			
			'typeName'  => $params['layers'],
			
			'bbox' => implode($bbox, ',') . ',' . $this->srs,
			'srsName' => $this->srs,
			'outputFormat' => $this->format,
		);
		
		$result = $this->queryService($params);
		if ($result === null)
			return false;
		
		$collection = json_decode($result);
		if ($collection === null)
			return false;
		
		$this->meshes = $this->processFeatures($collection->features);
		return true;
	}
	
	
	protected function processFeatures($features)
	{
		$r = array();
		foreach ($features as $f)
		{
			$geom = $f->geometry;
			
			if ($geom->type != 'Polygon')
				continue;
			
			foreach ($geom->coordinates as $poly) {
				$poly2d = $this->extractPolygon2D($poly);
				$r[] = $this->projectVertices($poly2d);
			}
		}
		return $r;
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

}


?>