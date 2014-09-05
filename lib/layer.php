<?php


abstract class Layer
{
	protected $uriResolver;
	protected $x, $y, $z;
	
	public function initialize($uriResolver, $x, $y, $z) {
		$this->uriResolver = $uriResolver;
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
	}
	
	public abstract function generate($asset);
}


?>