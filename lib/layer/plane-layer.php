<?php


require_once(__DIR__ . '/terrain-layer.php');
require_once(__DIR__ . '/../xml3d.php');
require_once(__DIR__ . '/../builder/quad-builder.php');


class PlaneLayer extends TerrainLayer
{
	public function generate($asset)
	{
		$mesh = $asset->addAssetMesh();
		$builder = new QuadBuilder($this->uriResolver);
		$builder->generate($mesh, null);
		$mesh->addChild($this->getTextureReference($this->diffuse));
	}
}


class ExternalPlaneLayer extends PlaneLayer
{
	protected $url;
	
	public function __construct($url, $format = 'png') {
		parent::__construct($format);
		$this->url = $url;
	}

	protected function getTextureReference($texname) {
		$tex = new Texture($texname);
		// TODO: use map replace
		$src = $this->url . '/' . $this->z . '/' . $this->x . '/' . $this->y . '.' . $this->format;
		$tex->addImage($src);
		return $tex;
	}
}


?>