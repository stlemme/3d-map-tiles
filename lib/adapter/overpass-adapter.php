<?php


require_once(__DIR__ . '/../geometry-adapter.php');


class OverpassAdapter extends GeometryAdapter
{
	protected $format = 'json';
	protected $nodes = array();
	protected $METERS_PER_LEVEL = 3.0;

	public function query($params)
	{
		$bbox = $this->tile_bounds();
		
		// w, s, e, n
		// $swne = '48.22467,11.57959,48.23199,11.59058';
		$swne = implode(array($bbox[1], $bbox[0], $bbox[3], $bbox[2]), ',');
		
		$qlQuery = '[out:' . $this->format . '];' .
			'(' .
				'(' .
					'rel(' . $swne . ')["building"];' .
					'rel(' . $swne . ')[type="building"];' .
				');' .
				'(' .
					'._;' .
					'way(r);' .
				');' .
				'(' .
					'._;' .
					'node(w);' .
				');' .
				'(' .
					'way(' . $swne . ')["building"];' .
					'way(' . $swne . ')["building:part"];' .
					// 'way(48.22467,11.57959,48.23199,11.59058)[aeroway~"aerodrome|runway"];' .
					// 'way(48.22467,11.57959,48.23199,11.59058)[waterway~"riverbank|dock"];' .
					// 'way(48.22467,11.57959,48.23199,11.59058)[waterway="canal"][area="yes"];' .
					// 'way(48.22467,11.57959,48.23199,11.59058)[natural~"water|scrub"];' .
					// 'way(48.22467,11.57959,48.23199,11.59058)[leisure~"park|pitch"];' .
					// 'way(' . $swne . ')[landuse~"grass|meadow|forest"];' .
					// 'way(' . $swne . ')[highway~"motorway|trunk|primary|secondary|tertiary|motorway_link|primary_link|secondary_link|tertiary_link|road"];' .
				');' .
				'(' .
					'._;' .
					'node(w);' .
				');' .
			');' .
			'out;';
		// print_r($qlQuery);
		
		$params = array(
			'data' => $qlQuery
		);
		
		$data = $this->queryService($params);
		// die($data);
		if($data === null)
			return;
		
		$result = json_decode($data, false, 512, JSON_BIGINT_AS_STRING);
		// echo count($result->elements);
		// print_r($result);
		// die($data);
		// exit;
		if ($result === null)
			return;
		
		$this->processNodes($result->elements);
		$this->processWays($result->elements);
	}
	
	
	protected function processNodes($elements)
	{
		$this->nodes = array();
		
		// {
			// "type" : "node",
			// "id" : 213477695,
			// "lat" : 65.0146403,
			// "lon" : 25.5388558
		// }
		foreach ($elements as $n)
		{
			if ($n->type != 'node')
				continue;
			
			$this->nodes[$n->id] = array($n->lon, $n->lat);
		}
		// print_r($this->nodes);
		// exit;
	}
	
	protected function processWays($elements)
	{
		$this->meshes = array();
		$this->heights = array();
		// {
			// "type" : "way",
			// "id" : 38436920,
			// "nodes" : [454411704, 454411705, 454411701, 213477695, 454411706, 1555775313, 370047113, 370047101, 2275856041, 454411704],
			// "tags" : {
				// "landuse" : "meadow"
			// }
		// }
		foreach ($elements as $w)
		{
			if ($w->type != 'way')
				continue;
				
			$poly2d = $this->extractPolygon2D($w->nodes);
			$height = isset($w->tags) ? $this->extractHeight($w->tags) : null;
			// print_r($m);
			$this->meshes[] = $this->projectVertices($poly2d);
			$this->heights[] = $height;
		}
	}
	
	protected function extractPolygon2D($nodes)
	{
		$geo_vertices = array();
		foreach ($nodes as $nid) {
			$n = $this->nodes[$nid];
			$geo_vertices[] = $n[0];
			$geo_vertices[] = $n[1];
			// ignore z component
		}
		return $geo_vertices;
	}

	// ported and adapted
	// from https://github.com/robhawkes/vizicities/blob/master/src/client/data/DataOverpass.js#L486
	protected function extractHeight($tags)
	{
		// print_r($tags);
		$scalingFactor = (isset($tags->building) && ($tags->building === "office")) ? 1.45 : 1.0;

		if (isset($tags->height))
			return $this->toMeters($tags->height);
			
		if (isset($tags->{'building:height'}))
			return $this->toMeters($tags->{'building:height'});
			
		if (isset($tags->levels))
			return $tags->levels * $this->METERS_PER_LEVEL * $scalingFactor <<0;
			
		if (isset($tags->{'building:levels'}))
			return $tags->{'building:levels'} * $this->METERS_PER_LEVEL * $scalingFactor <<0;
			
		if (isset($tags->building))
			return 10.0 + $this->randf() * 10.0;
			
		if (isset($tags->landuse) && ($tags->landuse === "forest"))
			return 7.0;

		// if (tags["waterway"] || tags["natural"] && /water|scrub/.test(tags["natural"]) || tags["leisure"] && /park|pitch/.test(tags["leisure"]) || tags["landuse"] && /grass|meadow|commercial|retail|industrial|brownfield/.test(tags["landuse"])) {
		
		if (isset($tags->waterway) || (isset($tags->natural) && ($tags->natural === "water")))
			return 4.0;
		
		// if (tags["natural"] === "scrub" || tags["leisure"] && /park|pitch/.test(tags["leisure"]) || tags["landuse"] && /grass|meadow/.test(tags["landuse"]) || tags["aeroway"] === "runway") {
		//	height = 3;

		// height *= this.geo.pixelsPerMeter;

		return null;
	}
	
	private function randf() {
		return mt_rand() / mt_getrandmax();
	}
}


?>