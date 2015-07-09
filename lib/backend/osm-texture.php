<?php


require_once(__DIR__ . '/../layered-backend.php');
require_once(__DIR__ . '/../layer/plane-layer.php');
require_once(__DIR__ . '/../image-tools.php');


class OsmTexture extends LayeredBackend
{
	private $TILE_RESOLUTION = 256;


	protected function getLayers() {
		return array(
			'plane' => $this->getGround(),
		);
	}
	
	
	public function getTexture($image, $format) {
		$delta = $this->config('texture.lod-delta');
		$osm = $this->config('osm.endpoint');
		
		if ($delta == 0) {
		
			$img = $this->loadTile($osm . '/' . ($this->z) . '/' . ($this->x) . '/' . ($this->y) . '.png');
			
		} else if ($delta > 0) {
		
			$count = pow(2, $delta);
			$img = ImageTools::create($count*$this->TILE_RESOLUTION, $count*$this->TILE_RESOLUTION);
			
			for ($x = 0; $x < $count; $x++) {
				for ($y = 0; $y < $count; $y++) {
					$tile = $this->loadTile($osm . '/' . ($this->z+$delta) . '/' . ($this->x*$count+$x) . '/' . ($this->y*$count+$y) . '.png');
					imagecopy($img, $tile, $x*$this->TILE_RESOLUTION, $y*$this->TILE_RESOLUTION, 0, 0, $this->TILE_RESOLUTION, $this->TILE_RESOLUTION);
					ImageTools::free($tile);
				}
			}
		
		} else if ($delta < 0) {
			
			$count = pow(2, -$delta);
			$im_res = $this->TILE_RESOLUTION / $count;
			$img = ImageTools::create($im_res, $im_res);
			
			$x = floor($this->x / $count);
			$y = floor($this->y / $count);
			$x_tile = $this->x - $x*$count;
			$y_tile = $this->y - $y*$count;

			$tile = $this->loadTile($osm . '/' . ($this->z+$delta) . '/' . ($x) . '/' . ($y) . '.png');
			imagecopy($img, $tile, 0, 0, $x_tile*$im_res, $y_tile*$im_res, $im_res, $im_res);
			ImageTools::free($tile);
			
		}
		
		if ($img === null || $img === false)
			$img = ImageTools::placeholder();

		return $img;
	}
	
	
	///////////////////////////////////////////////////////////////////////////

	
	private function loadTile($url) {
		$tile = ImageTools::frompng($url);
		if ($tile === null || $tile === false)
			$tile = ImageTools::placeholder();
		return $tile;
	}

	private function canUseExternalTexture() {
		if (!$this->config('texture.allow-external'))
			return false;
		if ($this->config('texture.preference') != 'png')
			return false;
		if ($this->config('texture.lod-delta') != 0)
			return false;
		return true;
	}
	
	protected function getGround()
	{
		if ($this->canUseExternalTexture()) {
			return new ExternalPlaneLayer($this->config('osm.endpoint'));
		} else {
			return new PlaneLayer($this->config('texture.preference'));
		}
	}

	protected function defaultConfig() {
		$config = array(
			'texture' => array(
				'allow-external' => true,
				'preference' => 'png',
				'lod-delta' => 0
			)
		);
		
		return array_replace_recursive(parent::defaultConfig(), $config);
	}

}


?>