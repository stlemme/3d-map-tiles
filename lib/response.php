<?php

require_once(__DIR__ . '/utils.php');


class Response
{
	private static $status = array(
		400 => "Bad Request",
		500 => "Internal Server Error"
	);

	public static function fail($code, $message) {
		if (!array_key_exists($code, self::$status))
			$code = 500;
		
		header('HTTP/1.1 ' . $code . ' ' . self::$status[$code]);
		die($message);
	}
	
	public static function json($data)
	{
		header("Content-type: application/json");
		header("Access-Control-Allow-Origin: *");
		echo Utils::json_encode($data);
	}
	
	public static function jsonp($jsoncb, $data)
	{
		header("Content-Type: application/javascript; charset=utf-8");
		echo $jsoncb . "(" . Utils::json_encode($data) . ")";
	}
	
	public static function xml($data)
	{
		header("Content-type: application/xml");
		echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		echo $data->serialize();
	}
	
	public static function image($data, $format)
	{
		$mime = $format == 'jpg' ? 'image/jpeg' : 'image/png';
		header("Content-type: " . $mime);
		switch ($mime) {
			case 'image/jpeg': imagejpeg($data); break;
			case 'image/png': imagepng($data); break;
		}
		imagedestroy($data);
		//echo $data;
	}
	
}

?>