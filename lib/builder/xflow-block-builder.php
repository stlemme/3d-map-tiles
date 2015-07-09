<?php


require_once(__DIR__ . '/../geometry-builder.php');


class XflowBlockBuilder extends GeometryBuilder
{
	protected $height;
	
	public function __construct($uriResolver, $height = 5.0)
	{
		parent::__construct($uriResolver);
		$this->height = $height;
	}
	
	public function generate($mesh, $vertices, $options = array())
	{
		// export as extruded triangulated polygon
		$mesh->setShader($this->shader->getReference('building'));
		$mesh->setMeshtype('triangles');
		$data = $mesh->addData();
		$data->compute("dataflow['" . $this->dataflow->getReference('extrude') . "']");
		$data->addChild(new Float2('contour', $vertices));
		$height = ($options['height'] > 0) ? $options['height'] : $this->height;
		$data->addChild(new Float('height', array($height)));
	}
}


?>