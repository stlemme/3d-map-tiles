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
	
	public function generate($mesh, $vertices, $options = array())
	{
		$mesh->setShader($this->shader->getReference('terrain'));
		$mesh->setMeshtype('triangles');

		$data = $mesh->addData();
		
		if ($options['vertex-normals']) {
			//$data->compute("dataflow['" . $this->dataflow->getReference('generateVertexNormal') . "']");
			$data->addChild(new Float3('normal', $options['normals']));
			$data = $data->addData();
		}

		$data->compute("position = xflow.morph(position, scale, elevation)");
		$data->addChild(new Float3('scale', [0,$this->scale,0]));
		$data->addChild(new Float('elevation', $vertices));
		$data = $data->addData();

		$data->compute("dataflow['" . $this->dataflow->getReference('generateGrid') . "']");
		$data->addChild(new Int('size', $options['dimensions']));
	}
}


?>