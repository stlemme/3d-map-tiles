<?php


require_once(__DIR__ . '/service-adapter.php');


abstract class GeometryServiceAdapter extends ServiceAdapter
{
	protected $meshes = array();
	protected $heights = array();
	protected $names = array();

	public function meshes() {
		return $this->meshes;
	}
	
	public function height($idx) {
		if ($idx >= count($this->heights))
			return null;
		return $this->heights[$idx];
	}
	
	public function name($idx) {
		if ($idx >= count($this->names))
			return null;
		return $this->names[$idx];
	}

	protected function projectVertices($vertices) {
		$c = count($vertices);
		$r = array();
		// $y = 0.1; // TODO: use config for that
		for ($i=0; $i<$c; $i+=2)
		{
			$r[] = $this->xtile($vertices[$i  ], $this->z) - $this->x;
			// $r[] = $y;
			$r[] = $this->ytile($vertices[$i+1], $this->z) - $this->y;
		}
		return $r;
	}
}


?>