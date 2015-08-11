<?php


require_once(__DIR__ . '/terrain-layer.php');
require_once(__DIR__ . '/../xml3d.php');
require_once(__DIR__ . '/../builder/heightfield-builder.php');


class HeightfieldLayer extends TerrainLayer
{
	private $builder = null;
	protected $normal = 'normalTexture';
	protected $useVertexNormals = true;
	
	public function __construct($adapter, $params, $useVertexNormals = true, $format = 'png')
	{
		parent::__construct($format);
		$this->adapter = $adapter;
		$this->params = $params;
		$this->useVertexNormals = $useVertexNormals;
	}

	public function generate($asset)
	{
		if ($this->adapter->query($this->params)) {
			$asset->addComment('Request to backend service failed.');
			return;
		}
		
		$mesh = $asset->addAssetMesh();
		
		$this->builder = new HeightfieldBuilder($this->uriResolver);

		$vertices = $this->adapter->data();
		$options = array(
			'dimensions' => $this->adapter->size(),
			'vertex-normals' => $this->useVertexNormals,
			'normals'=> $this->adapter->normals()
		);
		$this->builder->generate($mesh, $vertices, $options);
		
		if (!$this->useVertexNormals)
			$mesh->addChild($this->getTextureReference($this->diffuse, 'normal')); // $this->normal));
	}
}


?>