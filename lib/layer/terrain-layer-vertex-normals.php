<?php


require_once(__DIR__ . '/../layer.php');
require_once(__DIR__ . '/../adapter.php');
require_once(__DIR__ . '/../xml3d.php');
require_once(__DIR__ . '/../geometry-tools.php');


class TerrainLayer extends Layer
{
	public function __construct($adapter,$params)
	{
		$this->adapter = $adapter;
		$this->params = $params;
		//$this->adapter->query();
	}

	protected $diffuse = 'diffuseTexture';

	public function generate($asset)
	{
		// TODO: $this->elevation->query();
		
		
	/*	<!-- Load terrain data: -->
	<data id="terrain_data">
		<int id="gis_dimension" name="dimensions">2</int>
		<float id="gis_elevation" name="elevation">0 0 0 0</float>
	</data>

	<!-- Generate Grid: -->    
	<data id="generatedGrid" compute="(position, normal, texcoord, index) = xflow.mygrid(size)">
		<data src="#terrain_data" filter="rename({size: dimensions})"></data>
	</data>

	<!-- Transform Grid by elevation data: -->
	<data id="surface" compute="normal = xflow.vertexNormal(position, index)">
		<data id="displace" compute="position = xflow.morph(position, scale, elevation)">
			<float3 name="scale" id="scale">0 0.0015 0</float3>
			<data src="#generatedGrid"></data>
		</data>
	</data>
	*/

		/*$mesh = $asset->addAssetMesh();
		$builder = new QuadBuilder($this->uriResolver);
		$builder->generate($mesh, null);
		$mesh->addChild($this->getTextureReference($this->diffuse));
		*/
	$this->adapter->query($this->params);
	$shader = new ShaderResolver($this->uriResolver);
	$dataflow = new DataFlowResolver($this->uriResolver);
		
	$mesh = $asset->addAssetMesh();
	$mesh->setShader($shader->getReference('terrain'));
	//$mesh->setShader($shader->getReference('building'));
	$mesh->setMeshtype('triangles');
	$data1= $mesh->addData();
	$data1->compute("dataflow['" . $dataflow->getReference('vertexNormal') . "']");
	
	/*
	$texture= new Texture('diffuseTexture');
	$tex_src = $this->y . '-texture.png';
	$texture->addImage($tex_src);
	$mesh->addChild($texture);
	*/
	
	$data2=$data1->addData();
	$data2->compute("position = xflow.morph(position, scale, elevation)");
	$data2->addChild(new Float3('scale', [0,1,0]));
	$data3=$data2->addData();
	$data3->compute("dataflow['" . $dataflow->getReference('mygrid') . "']");
	$data3->addChild(new Int('size', $this->adapter->size()));
	$data3->addChild(new Float('elevation', $this->adapter->data()));
	}
	
	protected function getTextureReference($texname) {
		$tex = new Texture($texname);
		$src = $this->y . '-texture.png';
		$tex->addImage($src);
		return $tex;
	}
}
?>