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
		
		$position=array();
		$normal=array();
		
		$c=0;
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
			$c++;
		}
		
		if($c==0){
			//no buildings to be generated!
			return;
		}
		
		$m = $asset->addAssetMesh();
		
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

			
			$options = array(
				'height' => $height
			);
			$result=$this->builder->generate($m, $vertices, $options);
			
			
			$position = array_merge($position, $result[0]);
			$normal = array_merge($normal, $result[1]);

		}
		
		
		
		$data = $m->addData();
		$data->addChild(new Float3('position', $position));
		$data->addChild(new Float3('normal', $normal));
		
		
	}
}


?>