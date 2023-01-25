<?php
function showquran($lang, $surah, $verse)
{
	if (file_exists($lang . "/" . $surah . ".php")) {
		$_GET['verse'] = $v = $verse;
		$versenumber = @$_GET['versenumber'];
		@ob_start();
		$vn = "";
		include strtolower($lang . "/" . $surah . ".php");
		$content = @ob_get_clean();
		if ($lang == 'ar-src') {
			if ($versenumber) {
				$vnx = "";
				for ($i = 0; $i < strlen($v); $i++) {
					$j = substr($v, $i, 1);
					$k = (int)(1632 + $j);
					$l = "&#" . $k . ";";
					$vnx .= $l;
				}
				$vn = " ($vnx)";
			}
			$content = '<p dir="rtl" align="right" class="quran-verse"><span style="font-size:18px;">' . $content . $vn . '</span></p>' . "\r\n";
		} else {
			if ($versenumber) {
				$vn = "$verse. ";
			}
			$content = "<p>$vn$content</p>\r\n";
		}
		echo $content;
	}
}
if (isset($_GET['v'])) {
	$v = $_GET['v'];
	$arr = @explode("/", $v);
	if (count($arr) >= 3) {
		$arr = @explode("/", $v, 3);
		$arr[0] = addslashes(strtolower(substr($arr[0], 0, 2)));
		$arr[1] = (int) $arr[1];
		$arr[2] = preg_replace("/[^\d\-]/i", "", strtolower($arr[2]));
		$lang = $arr[0] . '-src';
		$surah = $arr[1];
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
				$verse = $i;
				showquran($lang, $surah, $verse);
			}
		} else {
			$verse = (int) $arr[2];
			showquran($lang, $surah, $verse);
		}
	}
}