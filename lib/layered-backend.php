<?php


require_once(__DIR__ . '/backend.php');
require_once(__DIR__ . '/utils.php');
require_once(__DIR__ . '/xml3d.php');


abstract class LayeredBackend extends Backend
{
	protected $layers;
	protected $uriResolver;
	
	
	public function initialize($z, $x, $y) {
		parent::initialize($z, $x, $y);
		
		$this->layers = $this->getLayers();
		foreach($this->layers as $name => $layer) {
			$layer->initialize($this->uriResolver, $x, $y, $z);
		}
	}
	
	public function caching($request) {
		return $request != 'model' ? parent::caching($request) : 0;
	}
	
	public function getModel()
	{
		$xml3d = new Xml3d();
		foreach($this->layers as $name => $layer) {
			$model = $xml3d->addModel($this->y . '-asset.xml#' . $name);
			$model->setTransform($this->y . '-asset.xml#tf');
		}
		return $xml3d;
	}
	
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
			// $asset = $xml3d->addAsset($name);
			$layer->generate($asset);
		}
		return $xml3d;
	}
	
	
	///////////////////////////////////////////////////////////////////////////
	
	
	protected abstract function getLayers();

	
	protected function __construct($config) {
		parent::__construct($config);
		
		$this->uriResolver = new BaseUriResolver();
	}
	
}

?>