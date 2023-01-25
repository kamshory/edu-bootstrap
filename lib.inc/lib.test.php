<?php

$cfg->image_not_exported = array('latex.codecogs.com');
$cfg->audio_not_exported = array();


function replaceImageData($html, $base_dir)
{
	global $cfg;
	error_reporting(0);
	$files = array();
	$dom = new domDocument;
	$html = UTF8ToEntities($html);
	@$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
	$dom->preserveWhiteSpace = false;
	$images = $dom->getElementsByTagName('img');
	$obj = new StdClass();
	foreach ($images as $image) {
		$src = $image->getAttribute('src');
		$skip = false;
		if (is_array($cfg->image_not_exported)) {
			foreach ($cfg->image_not_exported as $val) {
				if (stripos($src, $val) !== false) {
					$skip = true;
				}
			}
		}
		if ($skip) {
			continue;
		}


		if (stripos($src, "data:") === 0) {
			$arr = explode(",", $src, 2);
			$arr2 = explode(";", $arr[0]);
			$arr3 = explode(":", $arr2[0]);
			$content_type = $arr3[1];
			$arr4 = explode("/", $arr3[1]);
			$base_name = md5($arr[1]) . "." . $arr4[1];
			$data = base64_decode($arr[1]);
		} else {
			if (stripos($src, "://") === false) {
				$path = $base_dir . $src;
				$data = file_get_contents($path);
			} else {
				$url = $src;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
				$data = curl_exec($ch);
				$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			}
			$hash = substr(md5($src), 0, 6) . "_";

			if (stripos($src, "?", 0) !== false) {
				$bn = explode("?", $src);
				$src = $bn[0];
			}
			if (stripos($src, "#", 0) !== false) {
				$bn = explode("#", $src);
				$src = $bn[0];
			}

			$base_name = $hash . basename($src);
			$ext = pathinfo($src, PATHINFO_EXTENSION);

			$image->setAttribute('src', $base_name);

			switch ($ext) {
				case 'gif':
					$content_type = "image/gif";
					break;

				case 'png':
					$content_type = "image/png";
					break;

				case 'jpeg':
				case 'jpg':
					$content_type = "image/jpeg";
					break;

				default:
					$content_type = "image/jpeg";
			}
		}
		unset($obj);
		$obj = new StdClass();

		$obj->name = $base_name;
		$obj->type = $content_type;
		$obj->encoding = 'base64';
		$obj->data = base64_encode($data);

		$files[] = $obj;
	}

	$dom->encoding = "utf-8";
	$data = $dom->saveHTML();
	$data = UTF8ToEntities($data);
	if (stripos($data, '<body') !== false) {
		$data = getDataInTag($data, 'body');
	} else if (stripos($data, '<head') !== false) {
		$data = getDataInTag($data, 'head');
	}

	unset($obj);
	$obj = new StdClass();

	$obj->html = $data;
	$obj->files = $files;

	return $obj;
}

function extract_image($html, $base_dir)
{
	global $cfg;
	$files = array();
	$dom = new domDocument;
	$html = UTF8ToEntities($html);
	@$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
	$dom->preserveWhiteSpace = false;
	$images = $dom->getElementsByTagName('img');
	$obj = new StdClass();
	foreach ($images as $image) {
		$src = $image->getAttribute('src');
		$skip = false;
		if (is_array($cfg->image_not_exported)) {
			foreach ($cfg->image_not_exported as $key => $val) {
				if (stripos($src, $val) !== false) {
					$skip = true;
				}
			}
		}
		if ($skip) {
			continue;
		}
		if (stripos($src, "data:") === 0) {
			$arr = explode(",", $src, 2);
			$arr2 = explode(";", $arr[0]);
			$arr3 = explode(":", $arr2[0]);
			$content_type = $arr3[1];
			$arr4 = explode("/", $arr3[1]);
			$base_name = md5($arr[1]) . "." . $arr4[1];
			$data = base64_decode($arr[1]);
			$image->setAttribute('src', $base_name);
		} else {
			if (stripos($src, "://") === false) {
				$path = $base_dir . $src;
				$data = file_get_contents($path);
			} else {
				$url = $src;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
				$data = curl_exec($ch);
				$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			}
			$hash = substr(md5($src), 0, 6) . "_";

			if (stripos($src, "?", 0) !== false) {
				$bn = explode("?", $src);
				$src = $bn[0];
			}
			if (stripos($src, "#", 0) !== false) {
				$bn = explode("#", $src);
				$src = $bn[0];
			}

			$base_name = $hash . basename($src);
			$ext = pathinfo($src, PATHINFO_EXTENSION);

			$image->setAttribute('src', $base_name);

			switch ($ext) {
				case 'gif':
					$content_type = "image/gif";
					break;

				case 'png':
					$content_type = "image/png";
					break;

				case 'jpeg':
				case 'jpg':
					$content_type = "image/jpeg";
					break;

				default:
					$content_type = "image/jpeg";
			}
		}
		unset($obj);
		$obj = new StdClass();

		$obj->name = $base_name;
		$obj->type = $content_type;
		$obj->encoding = 'base64';
		$obj->data = base64_encode($data);

		$files[] = $obj;
	}

	$audios = $dom->getElementsByTagName('audio');
	$obj = new StdClass();
	foreach ($audios as $audio) {
		$src = $audio->getAttribute('src');
		$skip = false;
		if (is_array($cfg->audio_not_exported)) {
			foreach ($cfg->audio_not_exported as $key => $val) {
				if (stripos($src, $val) !== false) {
					$skip = true;
				}
			}
		}
		if ($skip) {
			continue;
		}


		if (stripos($src, "data:") === 0) {
			$arr = explode(",", $src, 2);
			$arr2 = explode(";", $arr[0]);
			$arr3 = explode(":", $arr2[0]);
			$content_type = $arr3[1];
			$arr4 = explode("/", $arr3[1]);
			$base_name = md5($arr[1]) . "." . $arr4[1];
			$data = base64_decode($arr[1]);
			$audio->setAttribute('src', $base_name);
		} else {
			if (stripos($src, "://") === false) {
				$path = $base_dir . $src;
				$data = file_get_contents($path);
			} else {
				$url = $src;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
				$data = curl_exec($ch);
				$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			}
			$hash = substr(md5($src), 0, 6) . "_";

			if (stripos($src, "?", 0) !== false) {
				$bn = explode("?", $src);
				$src = $bn[0];
			}
			if (stripos($src, "#", 0) !== false) {
				$bn = explode("#", $src);
				$src = $bn[0];
			}

			$base_name = $hash . basename($src);
			$ext = pathinfo($src, PATHINFO_EXTENSION);

			$audio->setAttribute('src', $base_name);

			switch ($ext) {
				case 'ogg':
					$content_type = "audio/ogg";
					break;

				case 'mp4':
					$content_type = "audio/mp4";
					break;

				case 'mp3':
					$content_type = "audio/mp3";
					break;

				case 'mpeg':
					$content_type = "audio/mpeg";
					break;

				case 'wav':
					$content_type = "audio/wav";
					break;

				default:
					$content_type = "audio/mp3";
			}
		}
		unset($obj);
		$obj = new StdClass();

		$obj->name = $base_name;
		$obj->type = $content_type;
		$obj->encoding = 'base64';
		$obj->data = base64_encode($data);

		$files[] = $obj;
	}

	$dom->encoding = "utf-8";
	$data = $dom->saveHTML();
	$data = UTF8ToEntities($data);
	if (stripos($data, '<body') !== false) {
		$data = getDataInTag($data, 'body');
	} else if (stripos($data, '<head') !== false) {
		$data = getDataInTag($data, 'head');
	}

	unset($obj);
	$obj = new StdClass();

	$obj->html = $data;
	$obj->files = $files;

	return $obj;
}

$arr_files = array();

function export_question($database, $question_id, $base_dir = "")
{
	global $arr_files;
	$sql = "select * from `edu_question` where `question_id` = '$question_id' ";
	$stmt = $database->executeQuery($sql);
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	$content = $data['content'];
	$numbering = $data['numbering'];
	$random = $data['random'];
	$competence = trim($data['basic_competence']);

	$html_question = "";
	$html_option = "";
	$file1 = "";
	$file2 = "";
	if ($content != "") {
		$parsed_data = extract_image($content, $base_dir);
		$files = $parsed_data->files;
		$content = htmlspecialchars($parsed_data->html);

		if (count($files) > 0) {
			foreach ($files as $key => $val) {
				if (!in_array($val->name, $arr_files)) {
					$file1 .= "
		<file>
		<name>" . $val->name . "</name>
		<type>" . $val->type . "</type>
		<encoding>" . $val->encoding . "</encoding>
		<data>" . $val->data . "</data>
		</file>\r\n";
					$arr_files[] = $val->name;
				}
			}
		}
	}

	$html_question = "
	<question>
	<text>" . ($content) . "</text>
	<random>$random</random>
	<numbering>$numbering</numbering>
	<competence>$competence</competence>
	$file1
	</question>\r\n";


	$sql = "select * from `edu_option` where `question_id` = '$question_id' order by `order` asc";
	$stmt = $database->executeQuery($sql);
	$html_option .= "
		<answer>
		";
	if ($stmt->rowCount() > 0) {
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $data) {
			$content = $data['content'];
			$score = $data['score'] * 1;

			$file2 = "";
			if ($content != '') {
				$parsed_data = extract_image($content, $base_dir);
				$files = $parsed_data->files;
				$content = htmlspecialchars($parsed_data->html);
				if (count($files) > 0) {
					foreach ($files as $key => $val) {
						if (!in_array($val->name, $arr_files)) {
							$file2 .= "
				<file>
				<name>" . $val->name . "</name>
				<type>" . $val->type . "</type>
				<encoding>" . $val->encoding . "</encoding>
				<data>" . $val->data . "</data>
				</file>\r\n";
						}
						$arr_files[] = $val->name;
					}
				}
			}
			$html_option .= "
			<option>
				<text>" . ($content) . "</text>
				<value>$score</value>
				<score>$score</score>
				$file2
			</option>\r\n";
		}
	}
	$html_option .= "
		</answer>\r\n";

	return $html_question . $html_option;
}

function exportTest($database, $test_id, $base_dir = "")
{
	$html = "<" . "?xml version=\"1.0\" encoding=\"utf-8\"?" . ">
<test>
";
	$sql = "SELECT `question_id` from `edu_question` where `test_id` = '$test_id' order by `order` asc, `question_id` asc";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $data) {
			$question = export_question($data['question_id'], $base_dir);
			$html .= "
	<item>" . $question . "</item>\r\n";
		}
	}
	$html .= "</test>";
	return $html;
}
function getYoutubeParams($url)
{
	$s = $url;
	$params = array(
		'video_id' => '',
		'time' => '0',
		'url' => ''
	);
	if (stripos($s, "://") !== false && stripos($s, "youtube.com") !== false) {
		$s = htmlspecialchars_decode($s);
		$data = parse_url($s);
		parse_str(@$data['query'], $args);
		$vid = @$args['v'];
		$sstart = @$args['start'];

		$ff = @$data['fragment'];
		parse_str(@$ff, $fragment);
		if ($sstart == '' && @$fragment['t']) {
			$sstart = @$fragment['t'];
		}

		$sstart = str_ireplace(array("h", "m", "s"), array(" ", " ", ""), $sstart);
		$arr = explode(" ", $sstart);
		$arr2 = array_reverse($arr);
		$t = $arr2[0] + (@$arr2[1] * 60) + (@$arr2[2] * 3600);
		$time = $t;

		$yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1&amp;start=$time";

		$params['url'] = $yurl;
		$params['video_id'] = $vid;
		$params['time'] = $time;
	} else if (stripos($s, "://") !== false && stripos($s, "youtu.be") !== false) {
		$s = htmlspecialchars_decode($s);
		$data = parse_url($s);
		$vid = trim(@$data['path'], "/");
		parse_str(@$data['query'], $args);
		$sstart = @$args['t'];
		$sstart = str_ireplace(array("h", "m", "s"), array(" ", " ", ""), $sstart);
		$arr = explode(" ", $sstart);
		$arr2 = array_reverse($arr);
		$t = $arr2[0] + (@$arr2[1] * 60) + (@$arr2[2] * 3600);
		$time = $t;
		$yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1&amp;start=$time";
		$params['url'] = $yurl;
		$params['video_id'] = $vid;
		$params['time'] = $time;
	} else if (stripos($s, "://") !== false && stripos($s, "ytimg.com") !== false) {
		$s = htmlspecialchars_decode($s);
		$data = parse_url($s);
		$str = trim(@$data['path'], "/");
		$arr = explode("/", $str);
		$vid = @$arr[1];
		$yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1";
		$params['url'] = $yurl;
		$params['video_id'] = $vid;
	}
	return $params;
}
function filter_html($text)
{
	return strip_tags($text, '<iframe><img><audio><video>');
}

function first_index($array)
{
	return $array[0];
}
function last_index($array)
{
	return $array[count($array) - 1];
}
function getNumberingType($s1, $s2)
{
	$a1 = explode(".", $s1);
	$q1 = $a1[0];
	$a2 = explode(".", $s2);
	$q2 = $a2[0];

	$ret = false;

	if ($q1 == 'A' && $q2 == 'B') {
		$ret = 'upper-alpha';
	} else if ($q1 == 'a' && $q2 == 'b') {
		$ret = 'lower-alpha';
	} else if ($q1 == 'I' && $q2 == 'II') {
		$ret = 'upper-roman';
	} else if ($q1 == 'i' && $q2 == 'ii') {
		$ret = 'lower-roman';
	} else if ($q1 == '1' && $q2 == '2') {
		$ret = 'decimal';
	} else if ($q1 == '01' && $q2 == '02') {
		$ret = 'decimal-leading-zero';
	}

	return $ret;
}
function delTree($dir)
{
	$files = array_diff(scandir($dir), array('.', '..'));
	foreach ($files as $file) {
		(@is_dir("$dir/$file")) ? delTree("$dir/$file") : @unlink("$dir/$file");
	}
	return rmdir($dir);
}
function parseRawQuestion($raw_text)
{
	$raw_data = explode("\n", $raw_text);
	foreach ($raw_data as $line_no => $line_text) {
		$raw_data[$line_no] = trim($line_text, " \t\r\n ");
	}
	$text_data = trim(implode("\r\n", $raw_data), " \t\r\n ");
	while (stripos($text_data, "\r\n\r\n\r\n") !== false) {
		$text_data = str_replace("\r\n\r\n\r\n", "\r\n\r\n", $text_data);
	}
	$text_data = str_replace("\\\\\r\n", "<br>", $text_data);
	return explode("\r\n\r\n", $text_data);
}

function optionMatch($opt, $numbering)
{
	$numberingList = array(
		'upper-alpha' => array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'),
		'lower-alpha' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'),
		'upper-roman' => array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'),
		'lower-roman' => array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'vii', 'ix', 'x'),
		'decimal' => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10'),
		'decimal-leading-zero' => array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10')
	);
	if (!isset($numberingList[$numbering])) {
		return -1;
	}
	$num = $numberingList[$numbering];
	foreach ($num as $k => $v) {
		if ($v == $opt) {
			return $k;
		}
	}
	return -1;
}

function parseQuestion($question)
{
	$question_text = "";
	$question = str_replace("\\\\\r\n", "<br />", $question);
	$lines = explode("\r\n", $question);
	$question_text = $lines[0];
	$numbering_type = false;
	$result = array();
	$options = array();
	$lineslength = count($lines);
	if ($lineslength > 2) {
		foreach ($lines as $key => $val) {
			$lines[$key] = trim($val, " \t\r\n\t ");
		}
		$i = 1;
		do {
			$numbering = getNumberingType($lines[$i], $lines[$i + 1]);
			$i++;
			if ($i >= $lineslength - 2) {
				break;
			}
		} while ($numbering == '');
		$numbering_type = $numbering;

		for ($i = 1, $k = -1; $i < $lineslength - 1; $i++) {
			if (stripos($lines[$i], '.') !== false) {
				$tmp = explode(".", $lines[$i], 2);
				$opt = trim($tmp[0], " \t\r\n\t ");
				if (optionMatch($opt, $numbering_type) > -1) {
					$options[] = array('text' => trim($tmp[1], " \t\r\n\t "), 'value' => 0);
					$k++;
				} else {
					if ($k == -1) {
						$question_text .= '<br />' . $lines[$i];
					} else {
						$options[$k]['text'] .= '<br />' . $lines[$i];
					}
				}
			} else {
				if ($k == -1) {
					$question_text .= '<br />' . $lines[$i];
				} else {
					$options[$k]['text'] .= '<br />' . $lines[$i];
				}
			}
		}
		if ($lineslength > 3) {
			$lastIsAnswer = true;
			if (substr_count($lines[$lineslength - 1], "\\\\:") == substr_count($lines[$lineslength - 1], ":")) {
				$lastIsAnswer = false;
			}
			if (stripos($lines[$lineslength - 1], ':') !== false && $lastIsAnswer) {
				$lines[$lineslength - 1] = str_replace("\t", " ", $lines[$lineslength - 1]);
				$tmp = explode(":", $lines[$lineslength - 1], 2);
				$opt = trim($tmp[1], " \t\r\n\t ");
				$xx = explode(" ", $opt);
				$opt = $xx[0];
				$opt = trim($opt, " \t\r\n\t ");
				$answerIndex = optionMatch($opt, $numbering_type);
				if ($answerIndex > -1 && $answerIndex < count($options)) {
					$options[$answerIndex]['value'] = 1;
				} else {
					$tmp = explode(".", $lines[$lineslength - 1], 2);
					$opt = trim($tmp[0], " \t\r\n\t ");
					if (optionMatch($opt, $numbering_type) > -1) {
						$options[] = array('text' > trim($tmp[1], " \t\r\n\t "), 'value' => 0);
					}
				}
			} else {
				if (stripos($lines[$lineslength - 1], '.') !== false) {
					$tmp = explode(".", $lines[$lineslength - 1], 2);
					$opt = trim($tmp[0], " \t\r\n\t ");
					if (optionMatch($opt, $numbering_type) > -1) {
						$options[] = array('text' => trim($tmp[1], " \t\r\n\t "), 'value' => 0);
					}
				}
			}
		} else {
			$question_text = $lines[0];
			$options = array();
		}
		$question_text = trim($question_text, " \r\n ");
		$question_text = str_replace("\\\\:", ":", $question_text);
		foreach ($options as $key => $val) {
			$options[$key]['text'] = str_replace("\\\\:", ":", $options[$key]['text']);
		}
		$result = array(
			'question' => $question_text,
			'numbering' => $numbering_type,
			'option' => $options
		);
	}
	return $result;
}

function addImages($text, $base_dir = '', $base_src = '', $temp_dir = "")
{
	$verticalAlign = array('baseline', 'top', 'bottom', 'middle', 'text-top', 'text-bottom');

	$base_src = ltrim(rtrim($base_src, "/") . "/", "/");
	$temp = preg_replace('/\s+/', ' ', $text);
	$temp = str_replace("<br>", " <br> ", $temp);
	$temp = str_replace("<br />", " <br /> ", $temp);
	$temp = trim(preg_replace("/\s+/", " ", $temp));
	$arr = explode(" ", $temp);
	$arr_find = array();
	$arr_replace = array();
	foreach ($arr as $key => $val) {
		if (stripos($val, "img:") === 0) {
			$val2 = trim(str_replace("<br>", "", $val));
			$val2 = trim(str_replace("<br />", "", $val2));
			$arr2 = explode(":", $val2, 2);

			$img = trim($arr2[1]);
			$style = array();
			$style_element = '';
			if (stripos($img, "#") !== false) {
				$arr2 = explode("#", $img);
				$img = $arr2[0];
				if (in_array($arr2[1], $verticalAlign)) {
					$style[] = "vertical-align:" . $arr2[1];
				}
				$style_element = ' style="' . implode("; ", $style) . '"';
				$style_element = str_replace(' style=""', '', $style_element);
				if (count($arr2) > 2) {
					$l = explode(",", $arr2[2]);
					$m = ((int)@$l[0]) * 1;
					$n = ((int)@$l[1]) * 1;
					if ($m > 0) {
						$style_element .= ' width="' . $m . '"';
					}
					if ($n > 0) {
						$style_element .= ' height="' . $n . '"';
					}
					if (count($arr2) > 3) {
						$alt = rawurldecode($arr2[3]);
						if (stripos($alt, 'latex|') === 0) {
							$latex = substr($alt, 6);
							$alt = $latex;
							$style_element .= ' data-latex="' . htmlspecialchars($latex) . '"';
							$style_element .= ' class="latex-image"';
						}
						$style_element .= ' alt="' . htmlspecialchars($alt) . '"';
					}
				}
			}

			$arr_find[] = $val2;
			$arr_replace[] = '<img src="' . $base_src . $img . '" alt="' . $img . '"' . $style_element . '>';
			if ($temp_dir != $base_dir && $temp_dir != "" && file_exists($temp_dir . "/" . $img)) {
				@copy($temp_dir . "/" . $img, $base_dir . "/" . $img);
			}
		}
		if (stripos($val, "video:") === 0) {
			$val2 = trim(str_replace("<br>", "", $val));
			$val2 = trim(str_replace("<br />", "", $val2));
			$arr2 = explode(":", $val2, 2);

			$img = trim($arr2[1]);
			$style = array();
			$style_element = '';
			if (stripos($img, "#") !== false) {
				$arr2 = explode("#", $img);
				$img = $arr2[0];
				if (in_array($arr2[1], $verticalAlign)) {
					$style[] = "vertical-align:" . $arr2[1];
				}
				$style_element = ' style="' . implode("; ", $style) . '"';
				$style_element = str_replace(' style=""', '', $style_element);
				if (count($arr2) > 2) {
					$l = explode(",", $arr2[2]);
					$m = @$l[0] * 1;
					$n = @$l[1] * 1;
					if ($m > 0) {
						$style_element .= ' width="' . $m . '"';
					} else {
						$style_element .= ' width="500"';
					}
					if ($n > 0) {
						$style_element .= ' height="' . $n . '"';
					} else {
						$style_element .= ' height="280"';
					}
				} else {
					$style_element .= ' height="500" height="280"';
				}
			} else {
				$style_element .= ' height="500" height="280"';
			}

			if (stripos($img, "://") !== false) {
				$base_src2 = "";
			} else {
				$base_src2 = $base_src;
			}

			$arr_find[] = $val2;
			$arr_replace[] = '<video src="' . $base_src2 . $img . '" alt="' . $img . '"' . $style_element . ' controls></video>';

			if ($temp_dir != $base_dir && $temp_dir != "") {
				if (!file_exists($temp_dir . "/" . basename($img))) {
					@copy($temp_dir . "/" . $img, $base_dir . "/" . basename($img));
				}
			}
		}
		if (stripos($val, "iframe:") === 0) {
			$val2 = trim(str_replace("<br>", "", $val));
			$val2 = trim(str_replace("<br />", "", $val2));
			$arr2 = explode(":", $val2, 2);

			$img = trim($arr2[1]);
			$style = array();
			$style_element = '';
			if (stripos($img, "#") !== false) {
				$arr2 = explode("#", $img);
				$img = $arr2[0];
				if (in_array($arr2[1], $verticalAlign)) {
					$style[] = "vertical-align:" . $arr2[1];
				}
				$style_element = ' style="' . implode("; ", $style) . '"';
				$style_element = str_replace(' style=""', '', $style_element);
				if (count($arr2) > 2) {
					$l = explode(",", $arr2[2]);
					$m = @$l[0] * 1;
					$n = @$l[1] * 1;
					if ($m > 0) {
						$style_element .= ' width="' . $m . '"';
					} else {
						$style_element .= ' width="500"';
					}
					if ($n > 0) {
						$style_element .= ' height="' . $n . '"';
					} else {
						$style_element .= ' height="280"';
					}
				} else {
					$style_element .= ' height="500" height="280"';
				}
			} else {
				$style_element .= ' height="500" height="280"';
			}

			if (stripos($img, "://") !== false) {
				$base_src2 = "";
			} else {
				$base_src2 = $base_src;
			}

			$arr_find[] = $val2;
			$arr_replace[] = '<iframe src="' . $base_src2 . $img . '" alt="' . $img . '"' . $style_element . ' allowfullscreen="allowfullscreen" frameborder="0"></iframe>';

			if ($temp_dir != $base_dir && $temp_dir != "" && !file_exists($temp_dir . "/" . basename($img))) {
				@copy($temp_dir . "/" . $img, $base_dir . "/" . basename($img));
			}
		}
		if (stripos($val, "audio:") === 0) {
			$val2 = trim(str_replace("<br>", "", $val));
			$val2 = trim(str_replace("<br />", "", $val2));
			$arr2 = explode(":", $val2, 2);

			$img = trim($arr2[1]);
			$style = array();
			$style_element = '';
			if (stripos($img, "#") !== false) {
				$arr2 = explode("#", $img);
				$img = $arr2[0];
				if (in_array($arr2[1], $verticalAlign)) {
					$style[] = "vertical-align:" . $arr2[1];
				}
				$style_element = ' style="' . implode("; ", $style) . '"';
				$style_element = str_replace(' style=""', '', $style_element);
				if (count($arr2) > 2) {
					$l = explode(",", $arr2[2]);
					$m = @$l[0] * 1;
					$n = @$l[1] * 1;
					if ($m > 0) {
						$style_element .= ' width="' . $m . '"';
					} else {
						$style_element .= ' width="300"';
					}
					if ($n > 0) {
						$style_element .= ' height="' . $n . '"';
					} else {
						$style_element .= ' height="50"';
					}
				} else {
					$style_element .= ' height="300" height="50"';
				}
			} else {
				$style_element .= ' height="300" height="50"';
			}

			if (stripos($img, "://") !== false) {
				$base_src2 = "";
			} else {
				$base_src2 = $base_src;
			}

			$arr_find[] = $val2;
			$arr_replace[] = '<audio src="' . $base_src2 . $img . '" alt="' . $img . '"' . $style_element . ' controls></audio>';

			if ($temp_dir != $base_dir && $temp_dir != "") {
				if (!file_exists($temp_dir . "/" . basename($img))) {
					@copy($temp_dir . "/" . $img, $base_dir . "/" . basename($img));
				}
			}
		}
		if (stripos($val, "youtube:") === 0) {
			$val2 = trim(str_replace("<br>", "", $val));
			$val2 = trim(str_replace("<br />", "", $val2));
			$arr2 = explode(":", $val2, 2);

			$img = trim($arr2[1]);
			$style = array();
			$style[] = "border:none";
			$style_element = '';
			if (stripos($img, "#") !== false) {
				$arr2 = explode("#", $img);
				$img = $arr2[0];
				if (in_array($arr2[1], $verticalAlign)) {
					$style[] = "vertical-align:" . $arr2[1];
				}
				$style_element = ' style="' . implode("; ", $style) . '"';
				$style_element = str_replace(' style=""', '', $style_element);
			}
			$arr_find[] = $val2;
			$params = getYoutubeParams($img);
			$video_id = $params['video_id'];
			$time = $params['time'];
			$arr_replace[] = '<iframe type="text/html" marginwidth="0" marginheight="0" scrolling="no" src="https://www.youtube.com/embed/' . $video_id . '?html5=1&playsinline=1&allowfullscreen=true&rel=0&version=3&autoplay=0&start=' . $time . '" allowfullscreen="" height="281" width="500"' . $style_element . '></iframe>';
		}
	}

	$text = str_replace("<br>", "\r\n", $text);
	$text = str_replace("<br />", "\r\n", $text);
	$text = htmlspecialchars($text);
	if (count($arr_replace)) {
		$text = str_replace($arr_find, $arr_replace, $text);
	}

	// add question

	if (substr_count($text, "$$") > 1) {
		$arr = explode("$$", $text);
		$count = count($arr);
		for ($i = 1; $i < $count; $i += 2) {
			$txt = $arr[$i];
			$txtTrimmed = trim($txt, " \r\n\t ");
			if (strlen($txtTrimmed)) {
				$imghtml = '<img src="../cgi-bin/equgen.cgi?' . $txt . '" class="latex-image" style="vertical-align:middle" data-latex="' . $txt . '" alt="' . $txt . '">';
				$text = str_replace('$$' . $txt . '$$', $imghtml, $text);
			} else {
				$text = str_replace('$$' . $txt . '$$', '', $text);
			}
		}
	}
	return $text;
}

class DocxConversion
{
	private $filename;

	public function __construct($filePath)
	{
		$this->filename = $filePath;
	}
	private function read_doc()
	{
		$fileHandle = fopen($this->filename, "r");
		$line = @fread($fileHandle, filesize($this->filename));
		$lines = explode(chr(0x0D), $line);
		$outtext = "";
		foreach ($lines as $thisline) {
			$pos = strpos($thisline, chr(0x00));
			if (($pos !== false) || (strlen($thisline) == 0)) {
				// Do nothing
			} else {
				$outtext .= $thisline . " ";
			}
		}
		$outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-@\/\_\(\)]/", "", $outtext);
		return $outtext;
	}

	private function read_docx()
	{

		$striped_content = '';
		$content = '';

		$zip = zip_open($this->filename);

		if (!$zip || is_numeric($zip)) return false;

		while ($zip_entry = zip_read($zip)) {
			if (zip_entry_open($zip, $zip_entry) == FALSE) continue;
			if (zip_entry_name($zip_entry) != "word/document.xml") continue;
			$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			zip_entry_close($zip_entry);
		} // end while

		zip_close($zip);
		$content = str_replace("</w:r></w:p></w:tc><w:tc>", "\r\n", $content);
		$content = str_replace("</w:r></w:p></w:tc><w:tc>", "\r\n", $content);
		$content = str_replace("</w:r></w:p>", "\r\n", $content);
		$striped_content = strip_tags($content);

		return $striped_content;
	}

	/************************excel sheet************************************/

	public function xlsx_to_text($input_file)
	{
		$xml_filename = "xl/sharedStrings.xml"; //content file name
		$zip_handle = new ZipArchive;
		$output_text = "";
		if (true === $zip_handle->open($input_file)) {
			if (($xml_index = $zip_handle->locateName($xml_filename)) !== false) {
				$xml_datas = $zip_handle->getFromIndex($xml_index);
				$domDoc = new DOMDocument();
				$xml_handle = $domDoc->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
				$output_text = strip_tags($xml_handle->saveXML());
			} else {
				$output_text .= "";
			}
			$zip_handle->close();
		} else {
			$output_text .= "";
		}
		return $output_text;
	}

	/*************************power point files*****************************/
	public function pptx_to_text($input_file)
	{
		$zip_handle = new ZipArchive;
		$output_text = "";
		if (true === $zip_handle->open($input_file)) {
			$slide_number = 1; //loop through slide files
			while (($xml_index = $zip_handle->locateName("ppt/slides/slide" . $slide_number . ".xml")) !== false) {
				$xml_datas = $zip_handle->getFromIndex($xml_index);
				$domDoc = new DOMDocument();
				$xml_handle = $domDoc->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
				$output_text .= strip_tags($xml_handle->saveXML());
				$slide_number++;
			}
			if ($slide_number == 1) {
				$output_text .= "";
			}
			$zip_handle->close();
		} else {
			$output_text .= "";
		}
		return $output_text;
	}

	public function convertToText()
	{
		if (isset($this->filename) && !file_exists($this->filename)) {
			return "File Not exists";
		}

		$fileArray = pathinfo($this->filename);
		$file_ext  = $fileArray['extension'];
		if ($file_ext == "doc" || $file_ext == "docx" || $file_ext == "xlsx" || $file_ext == "pptx") {
			if ($file_ext == "doc") {
				return $this->read_doc();
			} elseif ($file_ext == "docx") {
				return $this->read_docx();
			} elseif ($file_ext == "xlsx") {
				return $this->xlsx_to_text($this->filename);
			} elseif ($file_ext == "pptx") {
				return $this->pptx_to_text($this->filename);
			}
		} else {
			return "Invalid File Type";
		}
	}
}
function removeparagraphtag($text)
{
	$cnt = trim($text, " \r\n\t");
	$np = substr_count(strtolower($cnt), '</p>');
	if ($np == 1 && stripos($cnt, '<p') === 0) {
		$ret = strip_only_tags($text, '<p>');
	}
	/*
	else if($np > 1)
	{
		$text = str_replace('<p', '<br />', $text);
		$text = strip_only_tags($text, '<p>');
		if(stripos($text, '<br />') === 0)
		{
			$text = substr($text, 6); 
		}
		$ret = $text;
	}
	*/ else {
		$ret = $text;
	}
	return $ret;
}
function loadXmlData($xml_file)
{
	$s = file_get_contents($xml_file);
	$test_data = simplexml_load_string($s);
	$files = array();
	$questions = array();
	$options = array();
	$order = 0;
	$index = 0;
	foreach ($test_data->item as $question) {
		// petanyaan
		$text_pertanyaan = trim(@$question->question->text);
		$random = trim(@$question->question->random) * 1;
		$numbering = addslashes(trim(@$question->question->numbering));
		$competence = addslashes(trim(@$question->question->competence));
		$order++;
		if (count(@$question->question->file)) {
			foreach ($question->question->file as $file) {
				$name_file = trim(@$file->name, " \r\n\t ");
				$type_file = trim(@$file->type, " \r\n\t ");
				$encoding_file = trim(@$file->encoding, " \r\n\t ");
				$data_file = trim(@$file->data, " \r\n\t ");
				$files[$name_file] = array('type' => $type_file, 'encoding' => $encoding_file, 'data' => $data_file);
			}
		}
		$pertanyaan = $text_pertanyaan;
		$digest = md5($pertanyaan);

		if (count(@$question->answer->option) > 0) {
			$options = array();
			foreach ($question->answer->option as $index_option => $option) {
				$text_option = trim(@$option->text);
				$score = trim(@$option->value) * 1;
				if (count(@$option->file)) {
					foreach ($option->file as $file) {
						$name_file = trim(@$file->name, " \r\n\t ");
						$type_file = trim(@$file->type, " \r\n\t ");
						$encoding_file = trim(@$file->encoding, " \r\n\t ");
						$data_file = trim(@$file->data, " \r\n\t ");
						$files[$name_file] = array('type' => $type_file, 'encoding' => $encoding_file, 'data' => $data_file);
					}
				}
				$option = $text_option;
				$digest = md5($option);

				$order = $index_option + 1;
				if ($score > 0) {
					$cs = ' option-circle-selected';
				} else {
					$cs = '';
				}
				$options[] = "<li><span class=\"option-circle$cs\">$score</span><div class=\"list-option-item\"><div class=\"option-content\">" . $option . "</div></div></li>";
			}
		}
		$questions[] = "<li data-question-index=\"$index\"><div class=\"question\"><span class=\"competence-control\">" . $competence . "</span><a class=\"select-question\" href=\"javascript:;\" data-index=\"$index\" data-selected=\"true\"><span></span></a>" . $pertanyaan . "<ol style=\"list-style-type:$numbering\">" . implode("\r\n", $options) . "</ol></div></li>";
		$index++;
	}
	$text_all = "<ol class=\"test-question\">" . implode("\r\n", $questions) . "</ol>";
	foreach ($files as $name => $data) {
		$text_all = str_replace(' src="' . $name . '"', ' src="data:' . $data['type'] . ';' . $data['encoding'] . ',' . $data['data'] . '"', $text_all);
	}
	return $text_all;
}
function loadXmlData_word($xml_file, $key = 0)
{
	$s = file_get_contents($xml_file);
	$test_data = simplexml_load_string($s);
	$files = array();
	$questions = array();
	$options = array();
	$order = 0;
	$index = 0;
	$answer_key = '';

	$answer = array(
		'upper-alpha' => array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'),
		'lower-alpha' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'),
		'upper-roman' => array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'),
		'lower-roman' => array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x'),
		'decimal' => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10'),
		'decimal-leading-zero' => array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10')
	);

	foreach ($test_data->item as $question) {
		// petanyaan
		$text_pertanyaan = trim(@$question->question->text);
		$random = trim(@$question->question->random) * 1;
		$numbering = addslashes(trim(@$question->question->numbering));
		$competence = addslashes(trim(@$question->question->competence));
		$order++;
		if (count(@$question->question->file)) {
			foreach ($question->question->file as $index_file_question => $file) {
				$name_file = trim(@$file->name, " \r\n\t ");
				$type_file = trim(@$file->type, " \r\n\t ");
				$encoding_file = trim(@$file->encoding, " \r\n\t ");
				$data_file = trim(@$file->data, " \r\n\t ");
				$files[$name_file] = array('type' => $type_file, 'encoding' => $encoding_file, 'data' => $data_file);
			}
		}
		$pertanyaan = $text_pertanyaan;
		$digest = md5($pertanyaan);

		if (count(@$question->answer->option) > 0) {
			$options = array();
			$answer_key = '';
			$option_index = 0;
			foreach ($question->answer->option as $index_option => $option) {
				$text_option = trim(@$option->text);
				$score = trim(@$option->value) * 1;
				if (count(@$option->file)) {
					foreach ($option->file as $index_file_question => $file) {
						$name_file = trim(@$file->name, " \r\n\t ");
						$type_file = trim(@$file->type, " \r\n\t ");
						$encoding_file = trim(@$file->encoding, " \r\n\t ");
						$data_file = trim(@$file->data, " \r\n\t ");
						$files[$name_file] = array('type' => $type_file, 'encoding' => $encoding_file, 'data' => $data_file);
					}
				}
				$option = removeparagraphtag($text_option);
				$digest = md5($option);

				$order = $index_option + 1;
				if ($score > 0) {
					if ($answer_key == '') {
						$answer_key = @$answer[$numbering][$option_index];
					}
					$cs = ' option-circle-selected';
				} else {
					$cs = '';
				}
				$options[] = "<div class=\"option-item\" style=\"text-indent:-24px;padding:3px 0 3px 24px;\"><span style=\"text-indent:0px;display:inline-block;width:24px;\">" . ltrim(@$answer[$numbering][$option_index] . ". ", ".") . "</span>" . $option . "</div>";
				$option_index++;
			}
		}
		$questions[] = "<li data-question-index=\"$index\">" . $pertanyaan . "<div class=\"option-group\">" . implode("\r\n", $options) . "</div>" . ($key ? ("<div>Jawaban: $answer_key</div>") : "") . "</li>";
		$index++;
	}
	$text_all = "<ol class=\"test-question\">" . implode("\r\n", $questions) . "</ol>";
	foreach ($files as $name => $data) {
		$text_all = str_replace(' src="' . $name . '"', ' src="data:' . $data['type'] . ';' . $data['encoding'] . ',' . $data['data'] . '"', $text_all);
	}
	return $text_all;
}
