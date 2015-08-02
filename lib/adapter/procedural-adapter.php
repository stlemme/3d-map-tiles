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
	
	//earth radius in kilometres (avg)
	//*10 because lack of noise due to low amount of octaves
	private $r = 6371*10;
	private $octaves = 25*10;
	private $scaling = 3000;
	
	
	public function __construct($seed = null)
	{
		$this->perlin = new Perlin($seed);
	}

	public function query($params)
	{

		$this->size = pow(2, $params['lod']) + 1;
		$this->data = [];

		$bbox = $this->tile_bounds();
		
		$this->sample3dSphere($bbox);
	}
	
	public function size() {
		return [$this->size, $this->size];
	}
	
	public function tilesize(){
		return $this->tile_size();
	}
	
	
	public function data() {
		return $this->data;
	}
	
	private function sample3dSphere($bbox) {
		for ($y = 0; $y < $this->size; $y++) {
			for ($x = 0; $x < $this->size; $x++) {
				$s = $x / ($this->size-1);
				$t = $y / ($this->size-1);
				
				//linear interpolation + deg2rad
				$phi= ($s*$bbox[2]+(1-$s)*$bbox[0])* 0.017453292519943295;
				$omega= ($t*$bbox[1]+(1-$t)*$bbox[3])* 0.017453292519943295;

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