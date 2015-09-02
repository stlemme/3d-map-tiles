<?php


abstract class UriResolver
{
	public abstract function getFileReference($filename);
	
	public function getFragmentReference($filename, $fragment) {
		return $this->getFileReference($filename) . '#' . $fragment;
	}
}


class BaseUriResolver extends UriResolver
{
	protected $baseUrl;
	
	public function __construct() {
		$this->baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
	}
	
	public function getFileReference($filename) {
		return $this->getBaseUrl() . '/' . $filename;
	}
	
	public function getBaseUrl() {
		return $this->baseUrl;
	}
}


class DependentUriResolver extends UriResolver
{
	private $uriResolver;
	
	public function __construct($uriResolver) {
		$this->uriResolver = $uriResolver;
	}
	
	public function getFileReference($filename) {
		return $this->uriResolver->getFileReference($filename);
	}
}


class BasicXmlResolver extends DependentUriResolver
{
	private static $filename = 'basic.xml';
	
	protected function getBasicReference($type, $name) {
		return $this->getFragmentReference(self::$filename, $type . '_' . $name);
	}
}

class ShaderResolver extends BasicXmlResolver
{
	private static $type = 'shader';
	
	public function getReference($name) {
		return $this->getBasicReference(self::$type, $name);
	}
}

class MeshResolver extends BasicXmlResolver
{
	private static $type = 'mesh';
	
	public function getReference($name) {
		return $this->getBasicReference(self::$type, $name);
	}
}

class DataflowResolver extends BasicXmlResolver
{
	private static $type = 'dataflow';
	
	public function getReference($name) {
		return $this->getBasicReference(self::$type, $name);
	}
}

class DataResolver extends BasicXmlResolver
{
	private static $type = 'data';
	
	public function getReference($name) {
		return $this->getBasicReference(self::$type, $name);
	}
}


?>