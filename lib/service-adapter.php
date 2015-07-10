<?php


require_once(__DIR__ . '/adapter.php');


abstract class ServiceAdapter extends Adapter
{
	protected $endpoint;


	public function __construct($endpoint) {
		parent::__construct();
		$this->endpoint = $endpoint;
	}
	
	protected function queryService($params, $forceRequest = false) {
		$url = $this->endpoint . '?' . http_build_query($params);
		// die($url);

		$data = $this->cache->get($url);

		if ($data === null || $forceRequest) {
			$data = @file_get_contents($url);
			if ($data === false)
				return null;
			
			$this->cache->set($url, $data, $this->CACHE_TIME);
		}
		// die($data);
		return $data;
	}
	
}


?>