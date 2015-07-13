<?php


class ImageTools
{
	private static $placeholder = null;
	
	public static function frompng($url) {
		$img = @imagecreatefrompng($url);
		return $img !== false ? $img : null;
	}
	
	public static function compress($img, $format) {
		if ($img === null)
			return null;
		ob_start();
		switch ($format) {
			case 'jpg': @imagejpeg($img); break;
			case 'png': @imagepng($img); break;
			default: break;
		}
		$image_data = ob_get_contents();
		ob_end_clean();
		return $image_data;
	}

	public static function fromstring($data) {
		$img = @imagecreatefromstring($data);
		return $img !== false ? $img : null;
	}

	public static function create($width, $height) {
		$img = @imagecreatetruecolor($width, $height);
		return $img !== false ? $img : null;
	}

	public static function free($img) {
		if ($img === null) return;
		if ($img === self::$placeholder) return;
		@imagedestroy($img);
	}
	
	public static function getcolor($img, $r, $g, $b) {
		$c = imagecolorexact($img, $r, $g, $b);
		if ($c != -1) return $c;
		$c = imagecolorallocate($img, $r, $g, $b);
		if ($c != -1) return $c;
		return imagecolorclosest($img, $r, $g, $b);
	}
	
	public static function setpixel($img, $x, $y, $r, $g, $b) {
		$col = self::getcolor($img, $r, $g, $b);
		imagesetpixel($img, $x, $y, $col);
	}
	
	public static function placeholder() {
		if (self::$placeholder === null)
			self::$placeholder = self::frompng(__DIR__ . '/../checkerboard.png');
		return self::$placeholder;
	}

}


?>