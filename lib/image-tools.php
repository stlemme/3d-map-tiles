<?php


class ImageTools
{
	private static $placeholder = null;
	
	public static function frompng($url) {
		return @imagecreatefrompng($url);
	}

	public static function fromstring($data) {
		return @imagecreatefromstring($data);
	}

	public static function create($width, $height) {
		return @imagecreatetruecolor($width, $height);
	}

	public static function free($img) {
		if ($img === self::$placeholder) return;
		imagedestroy($img);
	}
	
	public static function placeholder() {
		if (self::$placeholder === null)
			self::$placeholder = self::frompng(__DIR__ . '/../checkerboard.png');
		return self::$placeholder;
	}

}


?>