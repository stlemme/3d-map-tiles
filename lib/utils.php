<?php

require_once(__DIR__ . '/response.php');


class Utils
{
	public static function set_eTagHeaders($file, $timestamp) {
		$gmt_mTime = gmdate('r', $timestamp);
	 
		header('Cache-Control: public');
		header('ETag: "' . md5($timestamp . $file) . '"');
		header('Last-Modified: ' . $gmt_mTime);
	 
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
			if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mTime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($timestamp . $file)) {
				header('HTTP/1.1 304 Not Modified');
				exit();
			}
		}
	}
	
	public static function clamp($x, $a, $b) {
		return min(max(intval($x), $a), $b);
	}
	
	// public static function generate_guidv4()
	// {
		// $data = openssl_random_pseudo_bytes(16);
		// $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
		// $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
		// return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	// }
	
	
	// const PATTERN_GUIDv4    = "/^([a-f0-9]{8})-([a-f0-9]{4})-([a-f0-9]{4})-([a-f0-9]{4})-([a-f0-9]{12})$/";
	// const PATTERN_URI       = "/([a-z][a-z0-9\+\-]*):\/\/(.+)/";
	// const PATTERN_COMP_NAME = "/^([a-z]{2,})\_([a-z0-9]+)$/";

	// public static function validate_guidv4($id) {
		// return self::validate_pattern(strtolower($id), self::PATTERN_GUIDv4);
	// }

	// public static function validate_uri($uri) {
		// return self::validate_pattern($uri, self::PATTERN_URI);
	// }
	
	// public static function validate_comp_name($comp_name) {
		// return self::validate_pattern($comp_name, self::PATTERN_COMP_NAME);
	// }

	// public static function validate_pattern($value, $pattern)
	// {
		// if (preg_match($pattern, $value) == 1)
			// return $value;

		// return null;
	// }
	
	
	public static function json_encode($data)
	{
		return json_encode($data);
	}
	
	public static function json_decode($json, $assoc_array = true, $debug = false)
	{
		$json_errors = array(
			JSON_ERROR_DEPTH => "The maximum stack depth has been exceeded",
			JSON_ERROR_STATE_MISMATCH => "Invalid or malformed JSON",
			JSON_ERROR_CTRL_CHAR => "Control character error, possibly incorrectly encoded",
			JSON_ERROR_SYNTAX => "Syntax error"
		);
		
		$data = json_decode($json, $assoc_array);
		$err = json_last_error();
		
		if ($debug) {
			if ($err != JSON_ERROR_NONE)
				die('JSON error : ' . $json_errors[$err]);
		}

		return $data;
	}
	
	public static function json_path($data, $path)
	{
		$current = $data;
		$path_parts = explode('.', $path);
		while(($elem = array_shift($path_parts)) !== null)
		{
			if ($current === null)
				return null;
			
			if (!array_key_exists($elem, $current))
				return null;
				
			$current = $current[$elem];
		}
		return $current;
	}
	
	public static function json_update($data, $update)
	{
		$new_data = array_replace_recursive($data, $update);
		// TODO: remove "deleted" (null) fields
		return $new_data;
	}

	
	// public static function extractCoordinates($poi_data, &$lon, &$lat)
	// {
		// $wgs84 = self::json_path($poi_data, 'fw_core.location.wgs84');
		// if ($wgs84 == null)
			// return false;
		
		// $lon = $wgs84['longitude'];
		// $lat = $wgs84['latitude'];
		
		// return true;
	// }

	
	public static function className($filename)
	{
		$filename = basename($filename, '.php');
		$s = str_replace('-', ' ', $filename);
		$t = ucwords($s);
		return str_replace(' ', '', $t);
	}
	
	public static function loadClassFromFile($filename, $path = null)
	{
		if ($path == null)
			$path = __DIR__;
		
		$filename = realpath($path . '/' . $filename . '.php');
		if (!file_exists($filename))
			return null;
		
		include_once($filename);

		return Utils::className($filename);
	}
	
}

?>