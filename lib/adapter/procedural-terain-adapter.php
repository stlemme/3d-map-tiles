<?php


require_once(__DIR__ . '/../adapter.php');
require_once(__DIR__ . '/../perlin.php');

class TerrainAdapter extends Adapter
{
	protected $terrain = null;
	protected $format = 'application/octet-stream';
	//protected $tile_size = 256;
	protected $lod=4;
	protected $size;
	//protected $sizey;
	protected $data;

	public function query($params)
	{
		// var options = {
		//	"version": "0.4",
		//	"service": "w3ds",
		//	"request": "GetScene",
		//	"crs": "EPSG:3047",
		//	"format": "application/octet-stream",
		//	"layers": "fiware:pallas-terrain",
		//	"boundingbox": "374000,7548000,375202,7549200",
		//	"LOD": "4"
		// };
	
	
		$bbox = $this->tile_bounds();
		
		$req_params = array(
			'service' => 'w3ds',
			'version' => '0.4',
			'request' => 'GetScene',
			
			'layers'  => $params['layers'],
			
			//'bbox' => implode($bbox, ','),
			'boundingbox' => implode($bbox, ','),
			'srs' => $this->srs,
			'crs' => $this->srs,
			'format' => $this->format,

			//'width' => $this->tile_size,
			//'height' => $this->tile_size,
			//'bgcolor' => $params['bgcolor'],
			//'transparent' => $params['transparent']
		);
		
		// $this->terrain = $this->queryService($req_params);
		
		// $list = array_merge(unpack("l/l/N*", $this->terrain));
		// $this->size=bindec ($list[0]);
		// $this->sizey=$list[1];
		// $this->data=array_slice($list, 4);
		// TODO: enhance image handling, e.g. filters, error handling, stitching, etc.
		
		$lod=$params['lod'];
		if($lod==null){
			$lod=6;
		}
		$this->size = pow(2,$lod)+1;
		$this->data = [];
		$tilesize=1/pow(2, $this->z);
		$xtile=$this->x*$tilesize;
		$ytile=$this->y*$tilesize;
		$perlingenerator=new Perlin(2000);
		/*
		for ($y=0;$y<$this->size;$y++){
			for ($x=0;$x<$this->size;$x++){
				$xpos=($xtile+($tilesize*($x/($this->size-1))))*50000;
				$ypos=($ytile+($tilesize*($y/($this->size-1))))*50000;
				$num = $perlingenerator->noise($xpos,$ypos,0,150);
				$raw = $num*2000;
				$this->data[count($this->data)]=$raw;
			}
		}
		*/
		$r=15000; //radius of virtual sphere, used to control frequency spectrum
		for ($y=0;$y<$this->size;$y++){
			for ($x=0;$x<$this->size;$x++){
				$phi=($xtile+($tilesize*($x/($this->size-1))))*3.14159265359*2; //[0,360] deg
				$omega=((($ytile+($tilesize*($y/($this->size-1))))-0.5)*3.14159265359); //[90,-90] deg
				
				//convert sphere coordinates to 3D texture space coordinates 
				$x_tex=$r*sin($omega)*cos($phi);
				$y_tex=$r*sin($omega)*sin($phi);
				$z_tex=$r*cos($omega);
				
				$num = $perlingenerator->noise($x_tex,$y_tex,$z_tex,30);
				$raw = $num*5000;
				$this->data[count($this->data)]=$raw;
			}
		}
	}
	
	public function size() {
		return [$this->size, $this->size];
	}
	
	
	public function data() {
		return $this->data;
	}

}


?>