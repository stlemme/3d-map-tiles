<?php


require_once(__DIR__ . '/../geometry-builder.php');


class QuadBuilder extends GeometryBuilder
{
	public function generate($mesh, $vertices, $options = array())
	{
		$mesh->setShader($this->shader->getReference('surface'));
		$mesh->setMeshtype('triangles');
		$mesh->addData($this->mesh->getReference('ground'));
	}
}


?>