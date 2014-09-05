<?php


require_once(__DIR__ . '/../layered-backend.php');
require_once(__DIR__ . '/../layer/plane-layer.php');


class OsmTexture extends LayeredBackend
{
	protected function getLayers() {
		return array(
			'plane' => new ExternalPlaneLayer($this->config('url'))
		);
	}
}


?>