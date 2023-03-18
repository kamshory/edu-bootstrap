<?php
function showquran($lang, $numVerse, $verse)
{
	$_GET['verse'] = $v = $numVerse;
	$numVersenumber = @$_GET['versenumber'];
	$vn = "";
	$content = $verse[$numVerse];
	if ($lang == 'ar-src') {
		if ($numVersenumber) {
			$vnx = "";
			for ($i = 0; $i < strlen($v); $i++) {
				$j = substr($v, $i, 1);
				$k = (int)(1632 + $j);
				$l = "&#" . $k . ";";
				$vnx .= $l;
			}
			$vn = " $vnx";
		}
		$content = '<p dir="rtl" align="right" class="quran-verse quran-verse-arabic">' . $content . $vn . '</p>' . "\r\n";
	} else {
		if ($numVersenumber) {
			$vn = "$numVerse. ";
		}
		$content = "<p>$vn$content</p>\r\n";
	}
	return $content;
}
if (isset($_GET['v'])) {
	$v = $_GET['v'];
	$arr = @explode("/", $v);
	if (count($arr) >= 3) {
		$arr = @explode("/", $v, 3);
		$arr[0] = strtolower(substr($arr[0], 0, 2));
		$arr[1] = (int) $arr[1];
		$arr[2] = preg_replace("/[^\d\-]/i", "", strtolower($arr[2]));
		$lang = $arr[0] . '-src';
		$surah = $arr[1];
		if (file_exists($lang . "/" . $surah . ".php")) {
			include_once($lang . "/" . $surah . ".php");
			if (stripos($arr[2], '-') !== false) {
				$arrv = @explode("-", $arr[2], 2);
				$start = $arrv[0];
				$end = $arrv[1];
				if ($end < $start) {
					$tmp = $start;
					$start = $end;
					$end = $tmp;
				}
				$start = ($start > 0) ? $start : 1;
				$end = ($end > 0) ? $end : 1;
				for ($i = $start; $i <= $end; $i++) {
					$numVerse = $i;
					echo showquran($lang, $numVerse, $quranArray);
				}
			} else {
				$numVerse = (int) $arr[2];
				echo showquran($lang, $numVerse, $quranArray);
			}
		}
	}
}
