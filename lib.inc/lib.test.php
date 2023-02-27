<?php
if(!isset($cfg))
{
	$cfg = new \stdClass;
}
$cfg->image_not_exported = array('latex.codecogs.com');
$cfg->audio_not_exported = array();





$arr_files = array();




/*
Old code
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
*/





function addImages($text, $base_dir = '', $base_src = '', $temp_dir = "") //NOSONAR
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
	foreach ($arr as $val) {
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
					$style[] = "vertical-align:" . $arr2[1]; //NOSONAR
				}
				$style_element = ' style="' . implode("; ", $style) . '"'; //NOSONAR
				$style_element = str_replace(' style=""', '', $style_element); //NOSONAR
				if (count($arr2) > 2) {
					$l = explode(",", $arr2[2]);
					$m = ((int)@$l[0]) * 1;
					$n = ((int)@$l[1]) * 1;
					if ($m > 0) {
						$style_element .= ' width="' . $m . '"'; //NOSONAR
					}
					if ($n > 0) {
						$style_element .= ' height="' . $n . '"'; //NOSONAR
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
			$arr_replace[] = '<img src="' . $base_src . $img . '" alt="' . $img . '"' . $style_element . '>'; //NOSONAR
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
					$style_element .= ' height="500" height="280"'; //NOSONAR
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

			if ($temp_dir != $base_dir && $temp_dir != "" && !file_exists($temp_dir . "/" . basename($img))) {
				@copy($temp_dir . "/" . $img, $base_dir . "/" . basename($img));
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

			if ($temp_dir != $base_dir && $temp_dir != "" && !file_exists($temp_dir . "/" . basename($img))) {
				@copy($temp_dir . "/" . $img, $base_dir . "/" . basename($img));
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
			$params = \Pico\RichText::getYoutubeParams($img);
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
	*/ 
	else 
	{
		$ret = $text;
	}
	return $ret;
}

if(!function_exists('trimWhitespace'))
{
	function trimWhitespace($value)
	{
		return trim($value, " \r\n\t ");
	}
}
