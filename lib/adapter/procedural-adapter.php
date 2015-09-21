<?php


require_once(__DIR__ . '/../adapter.php');
require_once(__DIR__ . '/../perlin.php');


class ProceduralAdapter extends Adapter
{
	protected $terrain = null;
	protected $lod = 0;
	protected $size;
	protected $data;
	protected $normals;
	protected $errorMetric;
	
	
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
		
		if ($params['vertex-normals']) {
			$this->sample3dSphereNormals($bbox, $params['vertex-normals-lod']);
		}
		
		$this->sample3dSphere($bbox);
		
		$this->errorMetric=$this->calculateErrorMetric($bbox);

	}
	
	public function metric() {
		return $this->errorMetric;
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
	
	public function normals() {
		return $this->normals;
	}
	
	private function calculateErrorMetric($bbox){
		$error=0;
		for ($y = 0; $y < $this->size-1; $y++) {
			for ($x = 0; $x < $this->size-1; $x++) {
			
				//x direction
				$v1=$this->data[$x+$y*$this->size];
				$v2=$this->data[$x+1+$y*$this->size];
			
				$s = ($x+0.5) / ($this->size-1);
				$t = $y / ($this->size-1);
				
				//linear interpolation + deg2rad
				$phi= ($s*$bbox[2]+(1-$s)*$bbox[0])* 0.017453292519943295;
				$omega= ($t*$bbox[1]+(1-$t)*$bbox[3])* 0.017453292519943295;

				$interpolated = $this->getSample3d($omega,$phi);
			
				$error=max($error,abs((($v1+$v2)/2)-$interpolated));
				
				//y direction
				$v2=$this->data[$x+($y+1)*$this->size];
			
				$s = $x / ($this->size-1);
				$t = ($y+0.5) / ($this->size-1);
				
				//linear interpolation + deg2rad
				$phi= ($s*$bbox[2]+(1-$s)*$bbox[0])* 0.017453292519943295;
				$omega= ($t*$bbox[1]+(1-$t)*$bbox[3])* 0.017453292519943295;

				$interpolated = $this->getSample3d($omega,$phi);
			
				$error=max($error,abs((($v1+$v2)/2)-$interpolated));
			}
		}
		
		return $error;
	}
	
	private function sample3dSphere($bbox) {
		for ($y = 0; $y < $this->size; $y++) {
			for ($x = 0; $x < $this->size; $x++) {
				$s = $x / ($this->size-1);
				$t = $y / ($this->size-1);
				
				//linear interpolation + deg2rad
				$phi= ($s*$bbox[2]+(1-$s)*$bbox[0])* 0.017453292519943295;
				$omega= ($t*$bbox[1]+(1-$t)*$bbox[3])* 0.017453292519943295;

				$this->data[] = $this->getSample3d($omega,$phi);
			}
		}
	}
	
	private function sample3dSphereNormals($bbox, $lod) {
	
		//distance in tile space if tile is 1 unit square between 2 sample points spanning vectors for cross product
		$tile_space_distance=pow(2,($this->z+1))/(($this->size-1)*pow(2,$lod));
		$y_normal=$tile_space_distance*$tile_space_distance;
		//distance from center in latitude/longitude space
		$ll_distance=(($bbox[3]-$bbox[1])*$tile_space_distance/2)* 0.017453292519943295;
		
		for ($y = 0; $y < $this->size; $y++) {
			for ($x = 0; $x < $this->size; $x++) {
				$s = $x / ($this->size-1);
				$t = $y / ($this->size-1);
				
				//linear interpolation + deg2rad
				$phi= ($s*$bbox[2]+(1-$s)*$bbox[0])* 0.017453292519943295;
				$omega= ($t*$bbox[1]+(1-$t)*$bbox[3])* 0.017453292519943295;
				
				$n=$this->getSample3d($omega+$ll_distance,$phi);
				$s=$this->getSample3d($omega-$ll_distance,$phi);
				$o=$this->getSample3d($omega,$phi+$ll_distance);
				$w=$this->getSample3d($omega,$phi-$ll_distance);
				
				$uz=$n-$s;
				$vz=$w-$o;
				
				// cross product
				
				$x_normal =  $tile_space_distance*$vz;
				$z_normal = -$tile_space_distance*$uz;
				
				$l = sqrt($x_normal*$x_normal + $y_normal*$y_normal + $z_normal*$z_normal);
				
				$this->normals[]=$x_normal/$l;
				$this->normals[]=$y_normal/$l;
				$this->normals[]=$z_normal/$l;
				
			}
		}
		
	}
	
	/*
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
	*/
	private function getSample3d($omega,$phi){
		//convert sphere coordinates to 3D texture space coordinates 
		$x_tex = $this->r*sin($omega)*cos($phi);
		$y_tex = $this->r*sin($omega)*sin($phi);
		$z_tex = $this->r*cos($omega);
				
		return($this->scaling * exp(($this->perlin->noise($x_tex, $y_tex, $z_tex, $this->octaves)*1.5)-0.5));
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