<?php


require_once(__DIR__ . '/../geometry-builder.php');
require_once(__DIR__ . '/../triangulation.php');


class BlockBuilder extends GeometryBuilder
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
		$height = ($options['height'] > 0) ? $options['height'] : $this->height;
		$pos1 = Triangulate::ensureCCWContour($vertices);
		$pos2 = Triangulate::planeXZ($pos1);
		$ret= Triangulate::extrudePolygon($pos2, $height);
		$pos3=$ret[0];
		$index=$ret[1];
		$position=Triangulate::deindex($pos3,$index);
		$normal=Triangulate::generateFaceNormal($position);
		/*	pos1 = xflow.ensureCCWContour(contour);
			pos2 = xflow.planeXZ(pos1);
			pos3, index = xflow.extrudePolygon(pos2, height);
			position = xflow.deindex(pos3, index);
			normal = xflow.generateFaceNormal(position);
		*/
		$data = $mesh->addData();
		$data->addChild(new Float3('position', $position));
		$data->addChild(new Float3('normal', $normal));
		
		//$data = $mesh->addData();
		//$data->compute("dataflow['" . $this->dataflow->getReference('extrude') . "']");
		//$data->addChild(new Float2('contour', $vertices));
		//$height = ($options['height'] > 0) ? $options['height'] : $this->height;
		//$data->addChild(new Float('height', array($height)));
	}
}


?>