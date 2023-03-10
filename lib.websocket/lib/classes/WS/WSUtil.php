<?php

namespace WS;

class WSUtil
{
	/**
	 * Parse request header
	 * @param string $rawHeaders Request header from client
	 * @return array Associated array of the request header
	 */
	public static function parseRawHeaders($rawHeaders)
	{
		$rawHeaders = trim($rawHeaders, "\r\n");
		$rawHeaders = str_replace("\n", "\r\n", $rawHeaders);
		$rawHeaders = str_replace("\r\r\n", "\r\n", $rawHeaders);
		$rawHeaders = str_replace("\r", "\r\n", $rawHeaders);
		$rawHeaders = str_replace("\r\n\n", "\r\n", $rawHeaders);
		$arr = explode("\r\n", $rawHeaders);
		$headers = array();
		$firstLine = $arr[0];
		$arr4 = explode(" ", $firstLine);
		$method = @$arr4[0];
		$version = @$arr4[2];
		$path = '/';
		$requestURL = '/';
		$query = array();

		if (isset($arr4[1])) {
			$requestURL = $arr4[1];
			if (stripos($arr4[1], "?") !== false) {
				$arr5 = explode("?", $arr4[1], 2);
				$path = $arr5[0];
				@parse_str($arr5[1], $query);
			}
		}

		foreach ($arr as $idx => $value) {
			if ($idx > 0) {
				$arr3 = explode(": ", $value, 2);
				if (count($arr3) == 2) {
					$headers[strtolower($arr3[0])] = $arr3[1];
				}
			}
		}
		return array(
			'method' => $method,
			'uri' => $requestURL,
			'path' => $path,
			'query' => $query,
			'version' => $version,
			'headers' => $headers
		);
	}

	/**
	 * Parse raw cookies
	 * @param string $cookieString Raw cookies from client
	 * @return array Associated array of the cookie
	 */
	public static function parseRawCookies($cookieString)
	{
		$cookieData = array();
		$arr = explode("; ", $cookieString);
		foreach ($arr as $val) {
			$arr2 = explode("=", $val, 2);
			if (count($arr2) > 1) {
				$cookieData[$arr2[0]] = $arr2[1];
			}
		}
		return $cookieData;
	}

	/**
	 * Read cookie
	 * @param array $cookieData Associated array of the cookie
	 * @return string name Cooke name
	 */
	public static function readCookie($cookieData, $name)
	{
		$v0 = (isset($cookieData[$name . "0"])) ? ($cookieData[$name . "0"]) : "";
		$v1 = (isset($cookieData[$name . "1"])) ? ($cookieData[$name . "1"]) : "";
		$v2 = (isset($cookieData[$name . "2"])) ? ($cookieData[$name . "2"]) : "";
		$v3 = (isset($cookieData[$name . "3"])) ? ($cookieData[$name . "3"]) : "";
		$v  = strrev(str_rot13($v1 . $v3 . $v2 . $v0));
		if ($v == "") {
			return md5(microtime() . mt_rand(1, 9999999));
		} else {
			return $v;
		}
	}

	/**
	 * Get session data
	 * @param \WS\WSSessionParams $sessionParams Session parameters
	 * @return array Asociated array contain session
	 */
	public static function getSessions($sessionID, $sessionParams)
	{
		$prefix = $sessionParams->getSessionFilePrefix();
		$sessionSavePath = $sessionParams->getSessionSavePath();

		if ($sessionSavePath === null) {
			$sessionSavePath = session_save_path();
		}
		$path = $sessionSavePath . "/" . $prefix . $sessionID;
		if (file_exists($path)) {
			$session_text = file_get_contents($path);
			if ($session_text != '') {
				return self::sessionDecode($session_text);
			}
		}
		return array();
	}

	/**
	 * Decode session data
	 * @param string $sessionData Raw session data
	 * @return array Asociated array contain session
	 */
	public static function sessionDecode($sessionData)
	{
		$return_data = array();
		$offset = 0;
		while ($offset < strlen($sessionData)) {
			if (!strstr(substr($sessionData, $offset), "|")) {
				throw new WSException("invalid data, remaining: " . substr($sessionData, $offset));
			}
			$pos = strpos($sessionData, "|", $offset);
			$num = $pos - $offset;
			$varname = substr($sessionData, $offset, $num);
			$offset += $num + 1;
			$data = unserialize(substr($sessionData, $offset));
			$return_data[$varname] = $data;
			$offset += strlen(serialize($data));
		}
		return $return_data;
	}

	/**
	 * Decode binary session data
	 * @param string $sessionData Raw session data
	 * @return array Asociated array contain session
	 */
	public static function sessionDecodeBinary($sessionData)
	{
		$return_data = array();
		$offset = 0;
		while ($offset < strlen($sessionData)) {
			$num = ord($sessionData[$offset]);
			$offset += 1;
			$varname = substr($sessionData, $offset, $num);
			$offset += $num;
			$data = unserialize(substr($sessionData, $offset));
			$return_data[$varname] = $data;
			$offset += strlen(serialize($data));
		}
		return $return_data;
	}

	/**
	 * Unmask incoming framed message
	 * @param string $text Masked message
	 * @return string Plain text
	 */
	public static function unmask($text)
	{
		$length = ord($text[1]) & 127;
		if ($length == 126) {
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		} else if ($length == 127) {
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		} else {
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$text = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$text .= $data[$i] ^ $masks[$i % 4];
		}
		return $text;
	}

	/**
	 * Encode message for transfer to client
	 * @param string $text Plain text to be sent to the client
	 * @return string Masked message
	 */
	public static function mask($text)
	{
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);
		if ($length <= 125) {
			$header = pack('CC', $b1, $length);
		} else if ($length > 125 && $length < 65536) {
			$header = pack('CCn', $b1, 126, $length);
		} else if ($length >= 65536) {
			$header = pack('CCNN', $b1, 127, $length);
		}
		return $header . $text;
	}
}
