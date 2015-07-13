<?php


require_once(__DIR__ . '/../adapter.php');
require_once(__DIR__ . '/../perlin.php');


class ProceduralAdapter extends Adapter
{
	protected $terrain = null;
	protected $lod = 0;
	protected $size;
	protected $data;
	
	protected $perlin;
	
	//radius of virtual sphere, used to control frequency spectrum
	private $r = 15000;
	private $octaves = 30;
	private $scaling = 15;
	
	
	public function __construct($seed = null)
	{
		$this->perlin = new Perlin($seed);
	}

	public function query($params)
	{
		// TODO: use $bbox for bilinear interpolation
		// $bbox = $this->tile_bounds();
		
		$this->size = pow(2, $params['lod']) + 1;
		$this->data = [];

		$tilesize = 1 / pow(2, $this->z);
		$xtileoffset = $tilesize * $this->x;
		$ytileoffset = $tilesize * $this->y;
		
		$this->sample3dSphere($xtileoffset, $ytileoffset, $tilesize);
	}
	
	public function size() {
		return [$this->size, $this->size];
	}
	
	
	public function data() {
		return $this->data;
	}
	
	private function sample3dSphere($offx, $offy, $tilesize) {
		for ($y = 0; $y < $this->size; $y++) {
			for ($x = 0; $x < $this->size; $x++) {
				$s = $x / ($this->size-1);
				$t = $y / ($this->size-1);
				
				$phi   = (($offx + $tilesize*$s) - 0.5) * M_PI*2; //[-180,180] deg
				$omega = (($offy + $tilesize*$t) - 0.5) * M_PI; //[90,-90] deg
				
				//convert sphere coordinates to 3D texture space coordinates 
				$x_tex = $this->r*sin($omega)*cos($phi);
				$y_tex = $this->r*sin($omega)*sin($phi);
				$z_tex = $this->r*cos($omega);
				
				$this->data[] = $this->scaling * $this->perlin->noise($x_tex, $y_tex, $z_tex, $this->octaves);
			}
		}
	}
	
	private function sample2dPlane($offx, $offy, $tilesize) {
		/*
		for ($y=0;$y<$this->size;$y++){
			for ($x=0;$x<$this->size;$x++){
				$xpos=($xtile+($tilesize*($x/($this->size-1))))*50000;
				$ypos=($ytile+($tilesize*($y/($this->size-1))))*50000;
				$num = $perlingenerator->noise($xpos,$ypos,0,150);
				$raw = $num*2000;
				$this->data[] = $raw;
			}
		}
		*/
	}

}


?>