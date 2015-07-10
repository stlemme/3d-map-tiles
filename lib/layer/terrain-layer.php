<?php


require_once(__DIR__ . '/../layer.php');
require_once(__DIR__ . '/../xml3d.php');


class TerrainLayer extends Layer
{
	protected $diffuse = 'diffuseTexture';
	protected $format = 'png';

	public function __construct($format = 'png') {
		$this->format = $format;
	}

	protected function getTextureReference($texname, $suffix = '') {
		$tex = new Texture($texname);
		$src = $this->y . '-texture' . ($suffix != '' ? '-' . $suffix : '') . '.' . $this->format;
		$tex->addImage($src);
		return $tex;
	}
}


?>