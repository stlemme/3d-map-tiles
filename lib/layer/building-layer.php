<?php


require_once(__DIR__ . '/../adapter.php');
require_once(__DIR__ . '/../geometry-tools.php');
require_once(__DIR__ . '/../layer.php');
require_once(__DIR__ . '/../xml3d.php');


class BuildingLayer extends Layer
{
	protected $adapter;
	protected $params;
	protected $builder;
	
	public function __construct($adapter, $params, $builder)
	{
		$this->adapter = $adapter;
		$this->params = $params;
		$this->builder = $builder;
	}
	
	public function generate($asset)
	{
		$this->adapter->query($this->params);
		
		$meshes = $this->adapter->meshes();
		$n = count($meshes);
		for ($i = 0; $i < $n; $i++)
		{
			$vertices = $meshes[$i];
			$mesh_bbox = GeometryTools::calcBoundingBox($vertices);
			// echo $i . '  -  ' . $this->adapter->height($i) . PHP_EOL;
			$height = $this->adapter->height($i);
			$name = $this->adapter->name($i);
			
			// cull features with the center outside of the current tile
			if (abs(0.5 * ($mesh_bbox['minx'] + $mesh_bbox['maxx']) - 0.5) > 0.5) continue;
			if (abs(0.5 * ($mesh_bbox['miny'] + $mesh_bbox['maxy']) - 0.5) > 0.5) continue;

			$m = $asset->addAssetMesh();
			$options = array(
				'height' => $height
			);
			$this->builder->generate($m, $vertices, $options);
			
			if ($name !== null) {
				$m->setId($name);
				$m->setName($name);
			}
		}
	}
}


?>