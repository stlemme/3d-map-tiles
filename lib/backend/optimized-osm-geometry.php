<?php


require_once(__DIR__ . '/osm-geometry.php');
require_once(__DIR__ . '/../builder/geometry-block-builder.php');
require_once(__DIR__ . '/../layer/single-mesh-building-layer.php');


class OptimizedOsmGeometry extends OsmGeometry
{
	
	protected function getBuildings()
	{
		$this->overpass = new OverpassAdapter($this->config('overpass.endpoint'));

		$params = array(
			// 'layers'  => $this->config('wfs.params.layers'),
		);

		return new SingleMeshBuildingLayer(
			$this->overpass,
			$params,
			new GeometryBlockBuilder($this->uriResolver)
		);
	}

}

?>