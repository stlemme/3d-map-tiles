<?php


require_once(__DIR__ . '/uri-resolver.php');


abstract class GeometryBuilder
{
	public $shader;
	protected $dataflow;
	protected $mesh;
	
	public function __construct($uriResolver) {
		$this->shader = new ShaderResolver($uriResolver);
		$this->dataflow = new DataFlowResolver($uriResolver);
		$this->mesh = new MeshResolver($uriResolver);
		$this->data = new DataResolver($uriResolver);
	}
	
	public abstract function generate($mesh, $vertices, $options = array());
}


?>