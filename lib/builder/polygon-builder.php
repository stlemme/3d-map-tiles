<?php


require_once(__DIR__ . '/../geometry-builder.php');


class PolygonBuilder extends GeometryBuilder
{
	public function generate($mesh, $vertices, $options = array())
	{
		// export as single colored triangulated polygon
		$mesh->setShader($this->shader->getReference('silhouette'));
		$mesh->setMeshtype('triangles');
		$data = $mesh->addData();
		$data->compute("dataflow['" . $this->dataflow->getReference('triangulate') . "']");
		$data->addChild(new Float2('contour', $vertices));
	}
}


?>