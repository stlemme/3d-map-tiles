<?php


require_once(__DIR__ . '/../layered-backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../uri-resolver.php');
require_once(__DIR__ . '/../adapter/overpass-adapter.php');
require_once(__DIR__ . '/../builder/block-builder.php');
require_once(__DIR__ . '/../layer/building-layer.php');
require_once(__DIR__ . '/../layer/plane-layer.php');


class OsmGeometry extends LayeredBackend
{
	protected $overpass;
	
	
	public function useCaching($request) {
		return $request != 'model';
	}
	
	public function initialize($z, $x, $y) {
		parent::initialize($z, $x, $y);
		
		$this->overpass->initialize($x, $y, $z);
	}
	
	protected function getLayers() {
		return array(
			'plane' => $this->getGround(),
			'buildings' => $this->getBuildings()				
		);
	}
	
	public function getTexture() {
		$delta=$this->config('texture-lod-delta');
		$res=$this->config('texture-resolution');
		
		if($delta==0){
		
		$im=@ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z) . '/' . ($this->x) . '/' . ($this->y) . '.png');
		
		ob_start();
		imagepng($im);
		$image_data = ob_get_contents();
		ob_end_clean();
		
		imagedestroy($im);
		
		return $image_data;
		}
		
		else if($delta>0){
		$count=pow(2,$delta);
		$im = @ImageCreate ($count*$res, $count*$res);
		
		for($x=0;$x<$count;$x++){
				for($y=0;$y<$count;$y++){
				$tile = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+$delta) . '/' . ($this->x*$count+$x) . '/' . ($this->y*$count+$y) . '.png');
				imagecopy($im, $tile, $x*$res, $y*$res, 0, 0, $res, $res);
				imagedestroy($tile);
				}
			}
		
		ob_start();
		imagepng($im);
		$image_data = ob_get_contents();
		ob_end_clean();
		
		imagedestroy($im);
		
		return $image_data;
		
		}
		else{
		$count=pow(2,-$delta);
		$im_res=$res/$count;
		$im = @ImageCreate ($im_res,$im_res);
		
		$x=floor($this->x/$count);
		$y=floor($this->y/$count);
		$x_tile=$this->x-$x*$count;
		$y_tile=$this->y-$y*$count;
		$tile = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+$delta) . '/' . ($x) . '/' . ($y) . '.png');
		imagecopy($im, $tile, 0, 0, $x_tile*$im_res, $y_tile*$im_res, $im_res, $im_res);
		imagedestroy($tile);
		
		ob_start();
		imagepng($im);
		$image_data = ob_get_contents();
		ob_end_clean();
		
		imagedestroy($im);
		
		return $image_data;
		
		
		
		
		
		}
		//handle texture here
		/*
		$im = @ImageCreate (512, 512);
		$im00 = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+1) . '/' . ($this->x*2) . '/' . ($this->y*2) . '.png');
		$im01 = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+1) . '/' . ($this->x*2) . '/' . ($this->y*2+1) . '.png');
		$im10 = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+1) . '/' . ($this->x*2+1) . '/' . ($this->y*2) . '.png');
		$im11 = @ImageCreateFromPNG ($this->config('osm-url'). '/' . ($this->z+1) . '/' . ($this->x*2+1) . '/' . ($this->y*2+1) . '.png');
		imagecopy($im, $im00, 0, 0, 0, 0, 256, 256);
		imagecopy($im, $im01, 0, 256, 0, 0, 256, 256); 
		imagecopy($im, $im10, 256, 0, 0, 0, 256, 256); 
		imagecopy($im, $im11, 256, 256, 0, 0, 256, 256); 		

		ob_start();
		imagepng($im);
		$image_data = ob_get_contents();
		ob_end_clean();
		
		return $image_data;
		*/
	}

	
	///////////////////////////////////////////////////////////////////////////
	
	
	protected function getGround()
	{
		return new PlaneLayer();
	}
	
	protected function getBuildings()
	{
		$this->overpass = new OverpassAdapter($this->config('endpoint'));

		$params = array(
			// 'layers'  => $this->config('wfs.params.layers'),
		);

		return new BuildingLayer(
			$this->overpass,
			$params,
			new BlockBuilder($this->uriResolver)
		);
	}

	
}

?>