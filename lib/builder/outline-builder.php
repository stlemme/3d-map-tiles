<?php


require_once(__DIR__ . '/../geometry-builder.php');


class OutlineBuilder extends GeometryBuilder
{
	public function generate($mesh, $vertices)
	{
		// export as single colored outline
		$mesh->setShader($this->shader->getReference('silhouette'));
		$mesh->setMeshtype('linestrips');
		$data = $mesh->addData();
		// $data->filter("rename({position: contour})");
		$data->compute("dataflow['" . $this->dataflow->getReference('planeXZ') . "']");
		$data->addChild(new Float2('contour', $vertices));
	}
}


?>