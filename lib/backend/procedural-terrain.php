<?php


require_once(__DIR__ . '/../layered-backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../uri-resolver.php');
require_once(__DIR__ . '/../adapter/procedural-adapter.php');
require_once(__DIR__ . '/../layer/heightfield-layer.php');


class ProceduralTerrain extends LayeredBackend
{
	protected $terrain;
	
	
	public function initialize($z, $x, $y) {
		parent::initialize($z, $x, $y);
		
		$this->terrain->initialize($x, $y, $z);

		// $this->surface = new ProceduralTerrainAdapter($this->config('w3ds.endpoint'));
		// $this->surface->initialize($x, $y, $z);
		
	}
	
	protected function getLayers() {
		return array(
			'terrain' => $this->getGround(),
		);
	}
	
	// public function getTexture($image, $format) {
		// if ($image != 'normal')
			// return;

		// $lod=$this->config('w3ds.params.normalmap-lod');
		// if($lod==null){
			// $lod=8;
		// }
		// $params = array(
			// 'layers'  => $this->config('w3ds.params.layers'),
			// 'lod'  => $lod
		// );
		
		// $this->surface->query($params);
		// $resolution=pow(2,$lod);
		
		// $vertexcount_per_row=$resolution+1;
		// $C = 40075017; // earth equatorial circumference in meters
		// $longitude=((($this->y+0.5)/pow(2,$this->z))-0.5)*3.14159265359; //[-90,90] deg
		// $tilesize = $C * cos($longitude) / pow(2,$this->z);
		// $vertexdistance=$tilesize/$resolution;
		// $evelation=$this->surface->data();
		// $im = @imagecreatetruecolor ($resolution,$resolution);
		// for($y=0;$y<$resolution;$y++){
			// for($x=0;$x<$resolution;$x++){
				
				// $h00=$evelation[$x+$y*$vertexcount_per_row];
				// $h10=$evelation[$x+1+$y*$vertexcount_per_row];
				// $h01=$evelation[$x+($y+1)*$vertexcount_per_row];
				// $h11=$evelation[$x+1+($y+1)*$vertexcount_per_row];
				// $uz=($h11+$h01-$h00-$h10)/2;
				// $vz=($h00+$h01-$h11-$h10)/2;
				
				// cross product
				// $x_comp=$vertexdistance*$vz;
				// $y_comp=-$vertexdistance*$uz;
				// $z_comp=$vertexdistance*$vertexdistance;
				
				// $length=sqrt(pow($x_comp,2)+pow($y_comp,2)+pow($z_comp,2));
				
				// $r_value=intval($x_comp/$length*127+128);
				// $g_value=intval($y_comp/$length*127+128);
				// $b_value=intval($z_comp/$length*127+128);
				
				// $color=imagecolorallocate($im,$r_value,$g_value,$b_value);
				
				
				// imagesetpixel($im,$x,$y,$color);
				
			// }
		// }
		
		// return $im;
	// }
	
	
	///////////////////////////////////////////////////////////////////////////
	
	
	protected function getGround()
	{
		$this->terrain = new ProceduralAdapter($this->config('w3ds.endpoint'));
		
		$params = array(
			// 'layers'  => $this->config('w3ds.params.layers'),
			'lod'  => $this->config('mesh.lod')
		);
		
		//must intialize and query in terrain layer!
		return new HeightfieldLayer($this->terrain, $params);
	}
	
	

	
}

?>