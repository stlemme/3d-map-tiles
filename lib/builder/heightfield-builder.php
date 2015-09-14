<?php


require_once(__DIR__ . '/../geometry-builder.php');


class HeightfieldBuilder extends GeometryBuilder
{
	protected $scale;
	
	public function __construct($uriResolver)
	{
		parent::__construct($uriResolver, $scale = 1);
		$this->scale = $scale;
	}
	
	public function generate($asset, $vertices, $options = array())
	{
		/*
		$defs = $asset->addDefs();
		$defs->setId('meta-data');
		$defs->addChild(new Float('errormetric', [$options['metric']]));
		*/
		$mesh = $asset->addAssetMesh();
		if($options['shaded']==true){
			$mesh->setShader($this->shader->getReference('terrain'));
		}
		$mesh->setMeshtype('triangles');
		$mesh->setName('terrain_shaded');
		$mesh->setIncludes('terrain_morph');
		
		
		$data = $asset->addAssetData();
		
		$data->setName('terrain_morph');
		$data->addChild(new Float('elevation', $vertices));
		//$data->addChild(new Int('lod', [$options['lod']]));
		$data->addChild(new Int('stitching', [0,0,0,0]));
		$static_tile=$data->addData();
		
		
		if ($options['vertex-normals']) {
			$data->addChild(new Float3('normal', $options['normals']));
			if($options['shaded']==false){
				$static_tile->setSrc($this->data->getReference('grid_'.$options['lod'].'_vertex'));
				//$data->compute("dataflow['" . $this->dataflow->getReference('generateDynamicGridWireframeSimple') . "'](lod,position,elevation,stitching,normal,texcoord)");
				$data->compute("dataflow['" . $this->dataflow->getReference('generateDynamicGridWireframe') . "'](lod,position,elevation,stitching,normal,texcoord)");
			}
			else{
				//$static_tile->setSrc($this->data->getReference('grid_'.$options['lod'].'_vertex'));
				//$data->compute("dataflow['" . $this->dataflow->getReference('generateDynamicGridWireframeSimple') . "'](lod,position,elevation,stitching,normal,texcoord)");

				$static_tile->setSrc($this->data->getReference('grid_'.$options['lod']));
				$data->compute("dataflow['" . $this->dataflow->getReference('generateDynamicGrid') . "'](lod,position,index,elevation,stitching)");
			}
		}
		else{
			$texture=$mesh->addChild($options['texture']);
			$texture->setName('normalMap');
			$static_tile->setSrc($this->data->getReference('grid_'.$options['lod']));
			$data->compute("dataflow['" . $this->dataflow->getReference('generateDynamicGrid') . "'](lod,position,index,elevation,stitching)");
			
		}
		
		
		/*
		$mesh->setShader($this->shader->getReference('terrain'));
		$mesh->setMeshtype('triangles');
		$mesh->setName('terrain_shaded');

		$data = $mesh->addData();
		$data->setName('terrain_data');
		
		if ($options['vertex-normals']) {
			$data->addChild(new Float3('normal', $options['normals']));
			$data = $data->addData();
		}

		$data->compute("position = xflow.morph(position, scale, elevation)");
		$data->setName('terrain_morph');
		$data->addChild(new Float3('scale', [0,$this->scale,0]));
		$data->addChild(new Float('elevation', $vertices));
		$data = $data->addData();

		$data->compute("dataflow['" . $this->dataflow->getReference('generateStichedGrid') . "']");
		$data->setName('terrain_grid');
		$data->addChild(new Int('lod', [$options['lod']]));
		$data->addChild(new Int('stitching', [0,0,0,0]));
		*/
	}
}


?>