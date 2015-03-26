<?php


class XmlElement
{
	protected $children = array();
	protected $attributes = array();
	
	protected function __construct($attributes = array()) {
		$this->attributes = $attributes;
	}
	
	public function serialize() {
		$doc = '<' . $this->tagName();
		// var_dump($this->attributes);
		foreach($this->attributes as $attr => $value) {
			$doc .= ' ' . $attr . '="' . $value . '"';
		}
		if (count($this->children) > 0) {
			$doc .= '>' . PHP_EOL;
			foreach($this->children as $child) {
				$doc .= $child->serialize();
			}
			$doc .= '</' . $this->tagName() . '>' . PHP_EOL;
		} else {
			$doc .= '/>' . PHP_EOL;
		}
		return $doc;
	}
	
	public function setId($id) {
		$this->attributes['id'] = $id;
	}
	
	public function addChild($child) {
		$this->children[] = $child;
		return $child;
	}
	
	protected function tagName() {
		return 'unknown';
	}
}


class Model extends XmlElement
{
	public function __construct($src = null) {
		if ($src !== null) {
			parent::__construct(array(
				'src' => $src
			));
		}
	}
	
	public function setTransform($tf) {
		$this->attributes['transform'] = $tf;
	}

	protected function tagName() {
		return 'model';
	}
}

class Defs extends XmlElement
{
	public function addTransform($trans = null, $rot = null, $scale = null) {
		return $this->addChild(new Transform($trans, $rot, $scale));
	}

	protected function tagName() {
		return 'defs';
	}
}

class Transform extends XmlElement
{
	public function __construct($trans, $rot, $scale)
	{
		$attrs = array();
		if ($trans !== null) $attrs['translation'] = $trans;
		if ($rot !== null) $attrs['rotation'] = $rot;
		if ($scale !== null) $attrs['scale'] = $scale;
		parent::__construct($attrs);
	}

	protected function tagName() {
		return 'transform';
	}
}

class Asset extends XmlElement
{
	public function __construct($id)
	{
		parent::__construct(array(
		));
		$this->setId($id);
	}
	
	public function addAssetMesh() {
		return $this->addChild(new AssetMesh());
	}

	public function addAsset($id = null) {
		return $this->addChild(new Asset($id));
	}
	
	public function setName($name) {
		$this->attributes['name'] = $name;
	}
	
	protected function tagName() {
		return 'asset';
	}
}

class AssetMesh extends XmlElement
{
	public function __construct($meshType = null)
	{
		parent::__construct(array(
		));
		$this->setMeshType($meshType);
	}
	
	public function addData($src = null) {
		return $this->addChild(new Data($src));
	}
	
	public function setMeshType($meshType) {
		if ($meshType === null)
			return;
		$this->attributes['type'] = $meshType;
	}
	
	public function setShader($shader) {
		if ($shader === null)
			return;
		$this->attributes['shader'] = $shader;
	}
	
	protected function tagName() {
		return 'assetmesh';
	}
}

class Group extends XmlElement
{
	public function __construct($tf = null) {
		parent::__construct(array(
		));
		if ($tf !== null) {
			$this->attributes['transform'] = $tf;
		}
	}
	
	public function addModel($src = null) {
		return $this->addChild(new Model($src));
	}

	public function addGroup($tf = null) {
		return $this->addChild(new Group($tf));
	}
	
	public function addDefs() {
		return $this->addChild(new Defs());
	}

	public function addAsset($id = null) {
		return $this->addChild(new Asset($id));
	}
	
	protected function tagName() {
		return 'group';
	}
}

class Data extends XmlElement
{
	public function __construct($src = null) {
		parent::__construct(array(
		));
		if ($src !== null)
			$this->attributes['src'] = $src;
	}
	
	public function compute($operation) {
		$this->attributes['compute'] = $operation;
	}
	
	public function filter($operation) {
		$this->attributes['filter'] = $operation;
	}

	protected function tagName() {
		return 'data';
	}
}

class Image extends XmlElement
{
	public function __construct($src = null) {
		parent::__construct(array(
		));
		if ($src !== null)
			$this->attributes['src'] = $src;
	}
	
	protected function tagName() {
		return 'img';
	}
}

class Float3 extends ArrayDataType
{
	public function __construct($name, $array) {
		parent::__construct($name);
		$this->setArray($array);
	}
	
	protected function tagName() {
		return 'float3';
	}
}

class Float2 extends ArrayDataType
{
	public function __construct($name, $array) {
		parent::__construct($name);
		$this->setArray($array);
	}
	
	protected function tagName() {
		return 'float2';
	}
}

class Float extends ArrayDataType
{
	public function __construct($name, $array) {
		parent::__construct($name);
		$this->setArray($array);
	}
	
	protected function tagName() {
		return 'float';
	}
}


class TextElement extends XmlElement
{
	private $text = '';
	
	public function __construct($text = null) {
		parent::__construct(array(
		));
		if ($text !== null)
			$this->text = $text;
	}
	
	public function serialize() {
		return $this->text;
	}
	
	public function setText($text) {
		$this->text = $text;
	}

	public function setArray($array) {
		$this->text = implode($array, ' ');
	}
}

class ArrayDataType extends XmlElement
{
	public function __construct($name) {
		parent::__construct(array(
		));
		if ($name !== null)
			$this->attributes['name'] = $name;
	}
	
	public function setArray($array) {
		$t = new TextElement();
		$t->setArray($array);
		$this->addChild($t);
	}
}


class Texture extends XmlElement
{
	public function __construct($name) {
		parent::__construct(array(
		));
		if ($name !== null)
			$this->attributes['name'] = $name;
	}
	
	public function addImage($url) {
		$this->addChild(new Image($url));
	}
	
	protected function tagName() {
		return 'texture';
	}
}

class Xml3d extends Group
{
	public function __construct() {
		parent::__construct();
		$this->attributes['xmlns'] = 'http://www.xml3d.org/2009/xml3d';
	}
	
	protected function tagName() {
		return 'xml3d';
	}
}


?>