<?php


require_once(__DIR__ . '/../layered-backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../uri-resolver.php');
require_once(__DIR__ . '/../adapter/wfs-adapter.php');
require_once(__DIR__ . '/../adapter/wms-adapter.php');
require_once(__DIR__ . '/../adapter/procedural-terain-adapter.php');
//require_once(__DIR__ . '/../adapter/terrain-adapter.php');
require_once(__DIR__ . '/../builder/block-builder.php');
require_once(__DIR__ . '/../layer/building-layer.php');
require_once(__DIR__ . '/../layer/terrain-layer.php');


class GisDataProviderTerrain extends LayeredBackend
{
	protected $buildings;
	protected $surface;
	protected $terrain;
	
	
	public function initialize($z, $x, $y) {
		parent::initialize($z, $x, $y);
		
		//$this->buildings->initialize($x, $y, $z);
		$this->terrain->initialize($x, $y, $z);
		/*
		$this->surface = new WMSAdapter($this->config('wms.endpoint'));
		$this->surface->initialize($x, $y, $z);
		*/
		$this->surface = new TerrainAdapter($this->config('w3ds.endpoint'));
		$this->surface->initialize($x, $y, $z);
		
	}
	
	protected function getLayers() {
		return array(
			'plane' => $this->getGround(),
			//'buildings' => $this->getBuildings()				
		);
	}
		
	public function getTexture() {
		/*
		$params = array(
			'layers'  => $this->config('wms.params.layers'),
			'styles' => $this->config('wms.params.styles'),
			'bgcolor' => $this->config('wms.params.bgcolor'),
			'transparent' => ($this->config('wms.params.transparent') ? 'true' : 'false')
		);
		
		$this->surface->query($params);
		return $this->surface->texture();
		*/
		$lod=$this->config('w3ds.params.normalmap-lod');
		if($lod==null){
			$lod=8;
		}
		$params = array(
			'layers'  => $this->config('w3ds.params.layers'),
			'lod'  => $lod
		);
		
		$this->surface->query($params);
		$resolution=pow(2,$lod);
		
		$vertexcount_per_row=$resolution+1;
		$C = 40075017; // earth equatorial circumference in meters
		$longitude=((($this->y+0.5)/pow(2,$this->z))-0.5)*3.14159265359; //[-90,90] deg
		$tilesize = $C * cos($longitude) / pow(2,$this->z);
		$vertexdistance=$tilesize/$resolution;
		$evelation=$this->surface->data();
		$im = @imagecreatetruecolor ($resolution,$resolution);
		for($y=0;$y<$resolution;$y++){
			for($x=0;$x<$resolution;$x++){
				
				$h00=$evelation[$x+$y*$vertexcount_per_row];
				$h10=$evelation[$x+1+$y*$vertexcount_per_row];
				$h01=$evelation[$x+($y+1)*$vertexcount_per_row];
				$h11=$evelation[$x+1+($y+1)*$vertexcount_per_row];
				$uz=($h11+$h01-$h00-$h10)/2;
				$vz=($h00+$h01-$h11-$h10)/2;
				
				//cross product
				$x_comp=$vertexdistance*$vz;
				$y_comp=-$vertexdistance*$uz;
				$z_comp=$vertexdistance*$vertexdistance;
				
				$length=sqrt(pow($x_comp,2)+pow($y_comp,2)+pow($z_comp,2));
				
				$r_value=intval($x_comp/$length*127+128);
				$g_value=intval($y_comp/$length*127+128);
				$b_value=intval($z_comp/$length*127+128);
				
				$color=imagecolorallocate($im,$r_value,$g_value,$b_value);
				
				
				imagesetpixel($im,$x,$y,$color);
				
			}
		}
		ob_start();
		imagepng($im);
		$image_data = ob_get_contents();
		ob_end_clean();
		
		imagedestroy($im);
		
		return $image_data;
	}
	
	
	///////////////////////////////////////////////////////////////////////////
	
	
	protected function getGround()
	{
		$this->terrain = new TerrainAdapter($this->config('w3ds.endpoint'));
		
		
		$params = array(
			'layers'  => $this->config('w3ds.params.layers'),
			'lod'  => $this->config('w3ds.params.lod')
		);
		//must intialize and query in terrain layer!
		return new TerrainLayer($this->terrain, $params);
	}
	
	protected function getBuildings()
	{
		$this->buildings = new WFSAdapter($this->config('wfs.endpoint'));

		$params = array(
			'layers'  => $this->config('wfs.params.layers'),
		);

		return new BuildingLayer(
			$this->buildings,
			$params,
			new BlockBuilder($this->uriResolver)
		);
	}

	
}

?>