<?php


class GeometryTools
{
	public static function min_replace(&$min, $value) {
		if ($value < $min) $min = $value;
	}

	public static function max_replace(&$max, $value) {
		if ($value > $max) $max = $value;
	}

	public static function calcBoundingBox($vertices, $dim = 2)
	{
		$c = count($vertices);
		$axis = array('x', 'y', 'z');
		$bbox = array();
		for ($i=0; $i<$dim; $i++) {
			$bbox['min' . $axis[$i]] = 1.0;
			$bbox['max' . $axis[$i]] = 0.0;
		}

		for ($j=0; $j<$c; $j+=$dim)
		{
			for ($i=0; $i<$dim; $i++) {
				$val = $vertices[$j+$i];
				self::min_replace($bbox['min' . $axis[$i]], $val);
				self::max_replace($bbox['max' . $axis[$i]], $val);
			}
		}
		
		return $bbox;
	}
	
}

?>