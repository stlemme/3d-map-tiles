<?php


require_once(__DIR__ . '/../layer.php');
require_once(__DIR__ . '/../xml3d.php');
require_once(__DIR__ . '/../builder/quad-builder.php');


class PlaneLayer extends Layer
{
	protected $diffuse = 'diffuseTexture';

	public function generate($asset)
	{
		// TODO: $this->elevation->query();
		
		$mesh = $asset->addAssetMesh();
		$builder = new QuadBuilder($this->uriResolver);
		$builder->generate($mesh, null);
		$mesh->addChild($this->getTextureReference($this->diffuse));
	}
	
	protected function getTextureReference($texname) {
		$tex = new Texture($texname);
		$src = $this->y . '-texture.png';
		$tex->addImage($src);
		return $tex;
	}
}


class ExternalPlaneLayer extends PlaneLayer
{
	protected $url;
	
	public function __construct($url) {
		$this->url = $url;
		
	}

	protected function getTextureReference($texname) {
		$tex = new Texture($texname);
		$src = $this->url . '/' . $this->z . '/' . $this->x . '/' . $this->y . '.png';
		$tex->addImage($src);
		return $tex;
	}
}





?>