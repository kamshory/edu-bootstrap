<?php
require_once dirname(dirname(__FILE__)) . "/lib.config/inc-cfg.php";
require_once dirname(__FILE__) . "/autoload.php";

mb_regex_encoding('UTF-8');
function mb_replace($search, $replace, $subject, &$count = 0) //NOSONAR
{
	if (!is_array($search) && is_array($replace)) {
		return false;
	}
	if (is_array($subject)) {
		// call mb_replace for each single string in $subject
		foreach ($subject as &$string) //NOSONAR
		{
			$string = &mb_replace($search, $replace, $string, $c);
			$count += $c;
		}
	} 
	else if (is_array($search)) 
	{
		if (!is_array($replace)) {
			foreach ($search as &$string) {
				$subject = mb_replace($string, $replace, $subject, $c);
				$count += $c;
			}
		} else {
			$n = max(count($search), count($replace));
			while ($n--) {
				$subject = mb_replace(current($search), current($replace), $subject, $c);
				$count += $c;
				next($search);
				next($replace);
			}
		}
	} 
	else 
	{
		$parts = mb_split(preg_quote($search), $subject);
		$count = count($parts) - 1;
		$subject = implode($replace, $parts);
	}
	return $subject;
}
function utf8ToEntities($string)
{
	return mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
}

// constant
if (!defined('FILTER_SANITIZE_NO_DOUBLE_SPACE')) {
	define('FILTER_SANITIZE_NO_DOUBLE_SPACE', 512);
}
if (!defined('FILTER_SANITIZE_STRING_NEW')) {
	define('FILTER_SANITIZE_STRING_NEW', 513);
}
if (!defined('FILTER_SANITIZE_PASSWORD')) {
	define('FILTER_SANITIZE_PASSWORD', 511);
}
if (!defined('FILTER_SANITIZE_ALPHA')) {
	define('FILTER_SANITIZE_ALPHA', 510);
}
if (!defined('FILTER_SANITIZE_ALPHANUMERIC')) {
	define('FILTER_SANITIZE_ALPHANUMERIC', 509);
}
if (!defined('FILTER_SANITIZE_ALPHANUMERICPUNC')) {
	define('FILTER_SANITIZE_ALPHANUMERICPUNC', 506);
}
if (!defined('FILTER_SANITIZE_NUMBER_UINT')) {
	define('FILTER_SANITIZE_NUMBER_UINT', 508);
}
if (!defined('FILTER_SANITIZE_STRING_NEW_INLINE')) {
	define('FILTER_SANITIZE_STRING_NEW_INLINE', 507);
}
if (!defined('FILTER_SANITIZE_STRING_NEW_BASE64')) {
	define('FILTER_SANITIZE_STRING_NEW_BASE64', 505);
}
if (!defined('FILTER_SANITIZE_IP')) {
	define('FILTER_SANITIZE_IP', 504);
}
if (!defined('FILTER_SANITIZE_NUMBER_OCTAL')) {
	define('FILTER_SANITIZE_NUMBER_OCTAL', 503);
}
if (!defined('FILTER_SANITIZE_NUMBER_HEXADECIMAL')) {
	define('FILTER_SANITIZE_NUMBER_HEXADECIMAL', 502);
}
if (!defined('FILTER_SANITIZE_COLOR')) {
	define('FILTER_SANITIZE_COLOR', 501);
}
if (!defined('FILTER_SANITIZE_POINT')) {
	define('FILTER_SANITIZE_POINT', 500);
}
if (!defined('FILTER_SANITIZE_NUMERIC')) {
	define('FILTER_SANITIZE_NUMERIC', 530);
}

function dmstoreal($deg, $min, $sec)
{
	return $deg + ((($min / 60) + ($sec)) / 3600);
}

function real2dms($val)
{
	$tm = $val * 3600;
	$tm = round($tm);
	$h = sprintf("%02d", date("H", $tm) - 7);
	if ($h < 0) {
		$h += 24;
	}
	$m = date("i", $tm);
	$s = date("s", $tm);
	return array($h, $m, $s);
}

function scrap($url)
{
	$html = @implode("\r\r", @file($url));
	if ($html == '') {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$html = curl_exec($curl);
		curl_close($curl);
	}
	return $html;
}



function getDefaultValues($database, $table, $fields)
{
	$sql = "show columns FROM `$table` ";
	$stmt = $database->executeQuery($sql);
	$arr = array();
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
		if (in_array($data['Field'], $fields)) 
		{
			$obj = new \stdClass();
			$obj->field = $data['Field'];
			$obj->value = $data['Default'];
			$arr[] = $obj;
		}
	}
?>
	<script type="text/javascript">
		$(document).ready(function(e) {
			var defdata = <?php echo json_encode($arr); ?>;
			var i;
			for (i in defdata) {
				var obj = $(':input[name=' + defdata[i]['field'] + ']');
				var val = defdata[i]['value'];
				if (obj.attr('type') == 'time' && val.indexOf(':') == -1) {
					var date = new Date(null);
					date.setSeconds(val);;
					obj.val(date.toISOString().substr(11, 8));
				} else if (obj.attr('type') == 'radio') {
					$('[name=' + defdata[i]['field'] + '][value=' + val + ']').attr('checked', 'checked');
				} else if (obj.find('option') > 0) {
					obj.find('option[value=' + val + ']').attr('selected', 'selected');
				} else if (obj.attr('type') == 'checkbox' && val != null && val != 0 && val != "0") {
					$('[name=' + defdata[i]['field'] + ']').attr('checked', 'checked');
				} else if (obj.attr('type') != 'password' && obj.attr('type') != 'checkbox' && obj.attr('type') != 'radio') {
					obj.val(defdata[i]['value']);
				}
			}
		});
	</script>
<?php
}

function translateDate($string)
{
	$arr1_en = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',  'September', 'October', 'November', 'December');
	$arr1_id = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'Nopember', 'Desember');

	$arr2_en = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	$arr2_id = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nop', 'Des');

	$arr3_en = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
	$arr3_id = array('Minggu', 'Senin',  'Selasa',  'Rabu',      'Kamis',    'Jumat',  'Sabtu');

	$arr4_en = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
	$arr4_id = array('Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab');

	$string = str_replace($arr1_en, $arr1_id, $string);
	$string = str_replace($arr3_en, $arr3_id, $string);
	$string = str_replace($arr2_en, $arr2_id, $string);
	$string = str_replace($arr4_en, $arr4_id, $string);

	return $string;
}
function excel2MySQLDate($int)
{
	return date('Y-m-d H:i:s', ($int - 25569) * 86400);
}

function liststyle($style, $index = 1) //NOSONAR
{
	switch ($style) //NOSONAR
	{
		case "armenian":
			break;
		case "circle":
			break;
		case "cjk-ideographic":
			break;
		case "decimal":
			return $index;
		case "decimal-leading-zero":
			return sprintf("%02d", $index);
		case "disc":
		case "georgian":
			break;
		case "hebrew":
			break;
		case "hiragana":
		case "hiragana-iroha":
		case "katakana":
			break;
		case "katakana-iroha";
			break;
		case "lower-alpha":
			return chr(96 + $index);
		case "lower-greek":
			break;
		case "lower-latin":
			return chr(96 + $index);
		case "lower-roman":
			return strtolower(ar_rom($index));
		case "square":
			break;
		case "upper-alpha":
			return chr(64 + $index);
		case "upper-latin":
			return chr(64 + $index);
		case "upper-roman":
			return strtoupper(ar_rom($index));
	}
}
function ar_rom($ar, $br = "\r\n")
{
	$lin = '';
	$num = '';
	$rom = array(
		array('no' => 1000000, 'lin' => '_', 'num' => 'M'),
		array('no' => 900000, 'lin' => '_', 'num' => 'CM'),
		array('no' => 500000, 'lin' => '_', 'num' => 'D'),
		array('no' => 400000, 'lin' => '_', 'num' => 'CD'),
		array('no' => 100000, 'lin' => '_', 'num' => 'C'),
		array('no' => 90000, 'lin' => '_', 'num' => 'XC'),
		array('no' => 50000, 'lin' => '_', 'num' => 'L'),
		array('no' => 40000, 'lin' => '_', 'num' => 'XL'),
		array('no' => 10000, 'lin' => '_', 'num' => 'X'),
		array('no' => 9000, 'lin' => '_', 'num' => 'IX'),
		array('no' => 5000, 'lin' => '_', 'num' => 'V'),
		array('no' => 4000, 'lin' => '_', 'num' => 'IV'),
		array('no' => 1000, 'lin' => ' ', 'num' => 'M'),
		array('no' => 900, 'lin' => ' ', 'num' => 'CM'),
		array('no' => 500, 'lin' => ' ', 'num' => 'D'),
		array('no' => 400, 'lin' => ' ', 'num' => 'CD'),
		array('no' => 100, 'lin' => ' ', 'num' => 'C'),
		array('no' => 90, 'lin' => ' ', 'num' => 'XC'),
		array('no' => 50, 'lin' => ' ', 'num' => 'L'),
		array('no' => 40, 'lin' => ' ', 'num' => 'XL'),
		array('no' => 10, 'lin' => ' ', 'num' => 'X'),
		array('no' => 9, 'lin' => ' ', 'num' => 'IX'),
		array('no' => 5, 'lin' => ' ', 'num' => 'V'),
		array('no' => 4, 'lin' => ' ', 'num' => 'IV'),
		array('no' => 1, 'lin' => ' ', 'num' => 'I'),
	);
	foreach ($rom as $v) {
		while ($ar >= $v['no']) {
			$ar = $ar - $v['no'];
			$lin .= $v['lin'];
			$num .= $v['num'];
		}
	}
	if (strpos($lin, '_') === false) {
		return $num;
	} else {
		return $lin . $br . $num;
	}
}




/**
 * Filter input from POST, GET, REQUEST, etc
 * @param int $type
 * @param string $variable_name
 * @param int $filter
 * @param mixed $options
 * @return string
 */
function kh_filter_input($type, $variable_name, $filter = FILTER_DEFAULT, $options = null) //NOSONAR
{
	switch ($type) //NOSONAR
	{
		case INPUT_GET:
			$var = $_GET;
			break;
		case INPUT_POST:
			$var = $_POST;
			break;
		case INPUT_COOKIE:
			$var = $_COOKIE;
			break;
		case INPUT_SERVER:
			$var = $_SERVER;
			break;
		case INPUT_ENV:
			$var = $_ENV;
	}
	$val = (isset($var[$variable_name])) ? $var[$variable_name] : "";
	if (!is_scalar($val)) {
		unset($val);
		$val = "";
		// ignore
	}

	if (@get_magic_quotes_runtime()) //NOSONAR
	{
		$val = my_stripslashes($val);
	}

	// add filter
	if ($filter == FILTER_SANITIZE_EMAIL) {
		$val = trim(strtolower($val));
		$val = filter_var($val, FILTER_VALIDATE_EMAIL);
		if ($val === false) {
			$val = "";
		}
	}
	if ($filter == FILTER_SANITIZE_URL) {
		// filter url
		$val = trim($val);
		if (stripos($val, "://") === false && strlen($val) > 2) {
			$val = "http://" . $val;
		}
		$val = filter_var($val, FILTER_VALIDATE_URL);
		if ($val === false) {
			$val = "";
		}
	}
	if ($filter == FILTER_SANITIZE_ALPHA) {
		$val = preg_replace("/[^A-Za-z]/i", "", $val); //NOSONAR
	}
	if ($filter == FILTER_SANITIZE_NUMERIC) {
		$val = preg_replace("/[^0-9]/i", "", $val); //NOSONAR
	}
	if ($filter == FILTER_SANITIZE_ALPHANUMERIC) {
		$val = preg_replace("/[^A-Za-z\d]/i", "", $val); //NOSONAR
	}
	if ($filter == FILTER_SANITIZE_ALPHANUMERICPUNC) {
		$val = preg_replace("/[^A-Za-z\.\-\d_]/i", "", $val); //NOSONAR
	}
	if ($filter == FILTER_SANITIZE_NUMBER_FLOAT) {
		$val = preg_replace("/[^Ee\+\-\.\d]/i", "", $val); //NOSONAR
		if(empty($val))
		{
			$val = 0;
		}
	}
	if ($filter == FILTER_SANITIZE_NUMBER_INT) {
		$val = preg_replace("/[^\+\-\d]/i", "", $val); //NOSONAR
		if(empty($val))
		{
			$val = 0;
		}
		$val = (int) $val;
	}
	if ($filter == FILTER_SANITIZE_NUMBER_UINT) {
		$val = preg_replace("/[^\+\-\d]/i", "", $val); //NOSONAR
		if(empty($val))
		{
			$val = 0;
		}
		$val = (int) $val;
		$val = abs($val);
	}
	if ($filter == FILTER_SANITIZE_NUMBER_OCTAL) {
		$val = preg_replace("/[^0-7]/i", "", $val); //NOSONAR
		if(empty($val))
		{
			$val = 0;
		}
	}
	if ($filter == FILTER_SANITIZE_NUMBER_HEXADECIMAL) {
		$val = preg_replace("/[^A-Fa-f\d]/i", "", $val); //NOSONAR
		if(empty($val))
		{
			$val = 0;
		}
	}
	if ($filter == FILTER_SANITIZE_COLOR) {
		$val = preg_replace("/[^A-Fa-f\d]/i", "", $val); //NOSONAR
		if (strlen($val) < 3) {
			$val = "";
		} else if (strlen($val) > 3 && strlen($val) != 3 && strlen($val) < 6) {
			$val = substr($val, 0, 3);
		} else if (strlen($val) > 6) {
			$val = substr($val, 0, 6);
		}
		if (strlen($val) >= 3) {
			$val = strtoupper("#" . $val);
		}
	}
	if ($filter == FILTER_SANITIZE_NO_DOUBLE_SPACE) {
		$val = trim(preg_replace("/\s+/", " ", $val)); //NOSONAR
	}
	if ($filter == FILTER_SANITIZE_PASSWORD) {
		$val = trim(preg_replace("/\s+/", " ", $val));
		$val = str_ireplace(array('"', "'", "`", "\\", "\0", "\r", "\n", "\t"), "", $val);
	}
	if ($filter == FILTER_SANITIZE_SPECIAL_CHARS) {
		$val = htmlspecialchars($val);
	}
	if ($filter == FILTER_SANITIZE_ENCODED) {
		$val = rawurlencode($val);
	}
	if ($filter == FILTER_SANITIZE_STRING_NEW) {
		$val = trim(strip_tags($val), "\r\n\t "); //NOSONAR
		$val = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $val); //NOSONAR
	}
	if ($filter == FILTER_SANITIZE_STRING_NEW_INLINE) {
		$val = trim(strip_tags($val), "\r\n\t ");
		$val = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $val); //NOSONAR
	}
	if ($filter == FILTER_SANITIZE_STRING_NEW_BASE64) {
		$val = preg_replace("/[^A-Za-z0-9\+\/\=]/", "", $val);
	}
	if ($filter == FILTER_SANITIZE_POINT) {
		$val = preg_replace("/[^0-9\-\+\/\.,]/", "", $val);
	}
	if ($filter == FILTER_SANITIZE_IP) {
		$val = filter_var($val, FILTER_VALIDATE_IP);
		if ($val === false) {
			$val = "";
		}
	}
	if (
		$filter == FILTER_SANITIZE_EMAIL ||
		$filter == FILTER_SANITIZE_ENCODED ||
		$filter == FILTER_SANITIZE_IP ||
		$filter == FILTER_SANITIZE_NO_DOUBLE_SPACE ||
		$filter == FILTER_SANITIZE_SPECIAL_CHARS ||
		$filter == FILTER_SANITIZE_STRING_NEW ||
		$filter == FILTER_SANITIZE_STRING_NEW_INLINE ||
		$filter == FILTER_SANITIZE_URL
	) {
		$val = my_addslashes($val);
	}
	return $val;
}

function kh_filter_input_search_get($var = 'q')
{
	$val = (isset($_GET[$var])) ? $_GET[$var] : "";
	if ($val != "" && is_array($val)) {
		unset($val);
		$val = "";
	}
	$val = trim(strip_tags($val), "\r\n\t ");
	$val = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $val);
	if (@get_magic_quotes_runtime()) //NOSONAR
	{
		$val = my_stripslashes($val);
	}
	return $val;
}

function kh_filter_file_name_safe($input)
{
	$output = preg_replace(
		array("/\s+/", "/[^-\.\w\s+]+/"),
		array("_", "-"),
		$input
	);
	$output = str_replace(
		array("-_", "_-", "__", "___", "--", "---"),
		array("_", "_", "_", "_", "-", "-"),
		$output
	);
	return $output;
}

function kh_filter_file_name($input)
{
	$output = preg_replace(
		array("/\s+/", "/[^-\.\w\s+]+/"),
		array(" ", "-"),
		$input
	);
	$output = str_replace(
		array("-_", "_-", "__", "___", "--", "---"),
		array("_", "_", "_", "_", "-", "-"),
		$output
	);
	return $output;
}
function my_addslashes($inp)
{
	return addslashes($inp);
}
function my_stripslashes($inp)
{
	$inp = str_replace(array("\\'"), array("'"), $inp);
	$inp = str_replace(array("\\\""), array("\""), $inp);
	$inp = str_replace(array("\\\\", "\\0", "\\n", "\\r", "\\Z"), array("\\", "\0", "\n", "\r", "\x1a"), $inp);
	return $inp;
}
function array_addslashes(&$item, $key = null) //NOSONAR
{
	$item = my_addslashes($item);
}
function array_stripslashes(&$item, $key = null) //NOSONAR
{
	$item = my_stripslashes($item);
}
function strip_only_tags($str, $tags, $stripContent = false)
{
	$content = '';
	if (!is_array($tags)) {
		$tags = (stripos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
		if (end($tags) == '') {
			array_pop($tags);
		}
	}
	foreach ($tags as $tag) {
		if ($stripContent) {
			$content = '(.+</' . $tag . '(>|\s[^>]*>)|)';
		}
		$str = @preg_replace('#</?' . $tag . '(>|\s[^>]*>)' . $content . '#is', '', $str);
	}
	return $str;
}

$database = new \Pico\PicoDatabase(
	(new \Pico\PicoDatabaseCredentials())->load($databaseConfigs->config_file),
	new \Pico\PicoDatabaseSyncConfig(
		$configs->sync_database_application_dir,
		$configs->sync_database_base_dir,
		$configs->sync_database_pool_name,
		$configs->sync_database_rolling_prefix,
		$configs->sync_database_extension,
		$configs->sync_database_maximum_length,
		$configs->sync_database_delimiter
	)
);

$database->connect();

$fileSync = new \Pico\FileSynchronizer(
	$configs->sync_file_application_dir,
	$configs->sync_file_base_dir,
	$configs->sync_file_pool_name,
	$configs->sync_file_rolling_prefix,
	$configs->sync_file_extension,
	$configs->sync_file_maximum_length,
	$configs->sync_file_use_relative_path
);

$picoEdu = new \Pico\PicoEdu($database);

$ip_create = $_SERVER['REMOTE_ADDR'];
$ip_edit = $ip_create;



