<?php


require_once(__DIR__ . '/../layered-backend.php');
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../uri-resolver.php');
require_once(__DIR__ . '/../adapter/procedural-adapter.php');
require_once(__DIR__ . '/../layer/heightfield-layer.php');
require_once(__DIR__ . '/../image-tools.php');


class ProceduralTerrain extends LayeredBackend
{
	protected $terrain;
	
	
	public function initialize($z, $x, $y) {
		parent::initialize($z, $x, $y);
		
		$this->terrain->initialize($x, $y, $z);
	}
	
	protected function getLayers() {
		return array(
			'terrain' => $this->getGround(),
		);
	}
	
	public function getTexture($image, $format) {
		if ($image != 'normal')
			return null;

		$params = array(
			'calculate-error-metric' => false,
			'vertex-normals' => false,
			'lod' => $this->config('texture.normalmap-lod')
		);
		
		// TODO: double check that this makes sense
		
		$this->terrain->query($params);

		$vertexcount_per_row = $this->terrain->size()[0];
		$resolution = $vertexcount_per_row - 1;
		$tilesize=$this->terrain->tilesize();
		//vertex distance in meters
		$vertexdistance = $tilesize/$resolution;
		
		
		$img = ImageTools::create($resolution, $resolution);
		
		$elevation = $this->terrain->data();

		for ($t = 0; $t < $resolution; $t++) {
			for ($s = 0; $s < $resolution; $s++) {
				
				$idx = $t*$vertexcount_per_row + $s;
				
				$h00 = $elevation[$idx];
				$h10 = $elevation[$idx + 1                       ];
				$h01 = $elevation[$idx     + $vertexcount_per_row];
				$h11 = $elevation[$idx + 1 + $vertexcount_per_row];
				$uz  = 0.5 * ($h11+$h01-$h00-$h10);
				$vz  = 0.5 * ($h00+$h01-$h11-$h10);
				
				// cross product
				$x =  $vertexdistance*$vz;
				$y = -$vertexdistance*$uz;
				$z =  $vertexdistance*$vertexdistance;
				
				$l = sqrt($x*$x + $y*$y + $z*$z);
				$k = 127/$l;
				
				$r = Utils::clamp($k*$x + 128, 0, 255);
				$g = Utils::clamp($k*$y + 128, 0, 255);
				$b = Utils::clamp($k*$z + 128, 0, 255);
				
				ImageTools::setpixel($img, $s, $t, $r, $g, $b);
			}
		}
		
		if ($img === null)
			$img = ImageTools::placeholder();
		
		return $img;
	}
	
	
	///////////////////////////////////////////////////////////////////////////
	public function getAssetData()
	{
		$xml3d = new Xml3d();
		
		$defs = $xml3d->addDefs();

		$tf = $defs->addTransform($this->x . ' 0 ' . $this->y);
		$tf->setId('tf');

		$all_assets = $xml3d->addAsset("all");

		foreach($this->layers as $name => $layer) {
			$asset = $all_assets->addAsset($name);
			$asset->setName($name);
			$layer->generate($asset);
		}
		if($this->config('calculate-error-metric')){
			$data=$defs->addData();
			$data->setId('meta-data');
			$data->addChild(new Float('errormetric', [$this->terrain->metric()]));
		}
		return $xml3d;
	}
	
	protected function getGround()
	{
		$this->terrain = new ProceduralAdapter($this->config('seed'));
		
		$params = array(
			'lod' => $this->config('mesh.lod'),
			'vertex-normals'=> $this->config('mesh.vertex-normals'),
			'vertex-normals-lod'=> $this->config('mesh.vertex-normals-lod'),
			'calculate-error-metric'=>$this->config('calculate-error-metric'),
			'shaded'=> $this->config('mesh.shaded')
		);
		
		return new HeightfieldLayer(
			$this->terrain, $params,
			$this->config('mesh.vertex-normals'),
			$this->config('texture.preference')
		);
	}
	
	protected function defaultConfig() {
		$config = array(
			'mesh' => array(
				'lod' => 4,
				'vertex-normals' => false,
				'vertex-normals-lod'=> 16,
				'shaded'=> true
			),
			'texture' => array(
				'preference' => 'png',
				'normalmap-lod' => 7
			),
			'seed' => 2000,
			'calculate-error-metric' => true
		);
		
		return array_replace_recursive(parent::defaultConfig(), $config);
	}

	
}

?>