<?php


class RichText{
    
	private function isVideo($s)
	{
		return stripos($s, "://") !== false && stripos($s, "facebook.com") !== false && (stripos($s, "/videos/") !== false || stripos($s, "/video/embed") !== false);
	}
	private function isYouTube($s)
	{
		return stripos($s, "://") !== false && stripos($s, "youtube.com") !== false;
	}
	private function isYouTubeShort($s)
	{
		return stripos($s, "://") !== false && stripos($s, "youtu.be") !== false;
	}
	private function isYtImage($s)
	{
		return stripos($s, "://") !== false && stripos($s, "ytimg.com") !== false;
	}
	private function isContainURL($s)
	{
		return stripos($s, "://") !== false;
	}
	private function isContainTag($s)
	{
		return stripos($s, "#") === 0;
	}

	private function createVideo($s)
	{
		$souce = htmlspecialchars_decode($s);
		$start1 = stripos($s, "/video/embed");
		$start2 = stripos($s, "/videos/");
		if ($start1 !== false) {
			$data = parse_url($souce);
			parse_str(@$data['query'], $args);
			$video_id = @$args['video_id'];
			$s = '<iframe width="100%" height="218" src="https://www.facebook.com/video/embed?video_id=' . $video_id . '" frameborder="0" marginheight="0" marginwidth="0" vspace="0" hspace="0" scrolling="no" allowfullscreen allowtransparency="true" style="display:block;margin:4px 0px;"></iframe>';
		}
		if ($start2 !== false) {
			$substr = ltrim(substr($s, $start2 + 8), "/");
			$arr = explode("/", $substr);
			$video_id = $arr[0];
			$s = '<iframe width="100%" height="218" src="https://www.facebook.com/video/embed?video_id=' . $video_id . '" frameborder="0" marginheight="0" marginwidth="0" vspace="0" hspace="0" scrolling="no" allowfullscreen allowtransparency="true" style="display:block;margin:4px 0px;"></iframe>';
		}
		return $s;
	}

	private function createYouTube($s)
	{
		$souce = htmlspecialchars_decode($s);
		$data = parse_url($souce);
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
		$yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1&amp;start=$time"; //NOSONAR
		$s = '<div class="youtube-video"><a href="javascript:;" data-url="' . $yurl . '"><img data-full-width="true" class="image-youtube-video" src="https://i1.ytimg.com/vi/' . $vid . '/hqdefault.jpg" /><div class="video-player"></div></a></div>'; //NOSONAR
		return $s;
	}

	private function createYpuTubeShort($s)
	{
		$souce = htmlspecialchars_decode($s);
		$data = parse_url($souce);
		$vid = trim(@$data['path'], "/");
		parse_str(@$data['query'], $args);
		$sstart = @$args['t'];
		$sstart = str_ireplace(array("h", "m", "s"), array(" ", " ", ""), $sstart);
		$arr = explode(" ", $sstart);
		$arr2 = array_reverse($arr);
		$t = $arr2[0] + (@$arr2[1] * 60) + (@$arr2[2] * 3600);
		$time = $t;
		$yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1&amp;start=$time"; //NOSONAR
		$s = '<div class="youtube-video"><a href="javascript:;" data-url="' . $yurl . '"><img data-full-width="true" class="image-youtube-video" src="https://i1.ytimg.com/vi/' . $vid . '/hqdefault.jpg" /><div class="video-player"></div></a></div>'; //NOSONAR
		return $s;
	}

	private function createYtImage($s)
	{
		$souce = htmlspecialchars_decode($s);
		$data = parse_url($souce);
		$str = trim(@$data['path'], "/");
		$arr = explode("/", $str);
		$vid = @$arr[1];
		$yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1"; //NOSONAR
		$s = '<div class="youtube-video"><a href="javascript:;" data-url="' . $yurl . '"><img data-full-width="true" class="image-youtube-video" src="https://i1.ytimg.com/vi/' . $vid . '/hqdefault.jpg" /><div class="video-player"></div></a></div>'; //NOSONAR
		return $s;
	}

	private function createURL($s)
	{
		$souce = htmlspecialchars_decode($s);
		$arr = explode("://", $s, 2);
		$protocol1 = $arr[0];
		$protocol2 = ltrim(preg_replace("/[^A-Za-z\d]/i", "", $protocol1), "0123456789");
		if ($protocol1 != $protocol2) {
			$url = $protocol2 . "://" . $arr[1];
		} else {
			$url = $s;
		}
		$url = filter_var($url, FILTER_VALIDATE_URL);
		if (substr_count($url, "/") == 3) {
			$url = rtrim($url, "/");
		}

		$arr = explode("/", $url, 4);
		if (count($arr) > 3 && strlen(@$arr[3]) <= 2) {
			$arr[3] = preg_replace('/^\PL+|\PL\z/', '', $arr[3]);
			$url = implode("/", $arr);
			if (strlen($arr[3]) == 0) {
				$url = rtrim($url, "/");
			}
		}
		$arr = explode("/", $url, 4);
		if (count($arr) > 3 && stripos($arr[3], "?") === false) {
			$arr[3] = trim($arr[3], "!*(),.=");
			$url = implode("/", $arr);
			if (strlen($arr[3]) == 0) {
				$url = rtrim($url, "/");
			}
		}
		$link = $url;
		$link = htmlspecialchars_decode($link);
		return str_replace($link, "<a href=\"" . $link . "\" target=\"_blank\">$link</a>", $souce);
	}

	private function createTag($s)
	{
		$souce = htmlspecialchars_decode($s);
		$numtags = substr_count($souce, "#");
		if ($numtags == 1) {
			$s = "<a class=\"link-to-tag post-tag\" data-tag=\"" . substr($souce, 1) . "\" href=\"find.php?tag=" . substr($souce, 1) . "\">$souce</a>"; //NOSONAR
		} else {
			$arr = explode("#", $souce);
			$arr2 = array();
			foreach ($arr as $key => $val) {
				$arr2[$key] = "<a class=\"link-to-tag post-tag\" data-tag=\"" . $val . "\" href=\"find.php?tag=" . $val . "\">#$val</a>"; //NOSONAR
				$arr[$key] = "#" . $arr[$key];
			}
			$s = str_replace($arr, $arr2, $s);
		}
		return $s;
	}

	private function createEmailOrUserLink($s)
	{
		$souce = htmlspecialchars_decode($s);
		$val = $souce;

		$arr3 = preg_split("/[^\@a-zA-Z0-9_]+/", $val);
		$arr4 = array();
		foreach ($arr3 as $val2) {
			if (stripos($val2, "@") !== false) {
				if (substr_count($val2, "@") > 1) {
					$arrx = explode(" ", trim(str_replace("@", " @", $val2)));
					$arr4 = array_merge($arr4, $arrx);
				} else {
					$arr4[] = $val2;
				}
			}
		}
		$arr5 = array();
		foreach ($arr4 as $val3) {
			$arr5[] = "<a class=\"link-to-user mention-user\" data-username=\"" . substr($val3, 1) . "\" href=\"" . substr($val3, 1) . "\">$val3</a>";
		}

		$s = str_replace($arr4, $arr5, $s);

		if (stripos($val, "#") !== false) {
			$arr6 = explode("#", $val);
			$arr7 = array();
			foreach ($arr6 as $key6 => $val6) {
				$arr7[$key6] = "<a class=\"link-to-tag post-tag\" data-tag=\"" . ($val6) . "\" href=\"find.php?tag=" . ($val6) . "\">#$val6</a>";
				$arr6[$key6] = "#" . $arr6[$key6];
			}
			$s = str_replace($arr6, $arr7, $s);
		}
		return $s;
	}

	private function createLinkURL($s)
	{
		$souce = htmlspecialchars_decode($s);
		if (stripos($s, "/") === false) {
			$link = preg_replace('/^\PL+|\PL\z/', '', $souce);
		} else {
			$link = $souce;
		}
		return str_replace($link, "<a href=\"http://" . $link . "\" target=\"_blank\">$link</a>", $souce);
	}

	public function createRichContent($s)
	{
		$souce = htmlspecialchars_decode($s);
		if ($this->isVideo($s)){
			$s = $this->createVideo($s);
		} 
		else if ($this->isYouTube($s)) 
		{
			$s = $this->createYouTube($s);
		} else if ($this->isYouTubeShort($s)) {
			$s = $this->createYpuTubeShort($s);
			
		} else if ($this->isYtImage($s)) {
			$s = $this->createYtImage($s);
		} else if ($this->isContainURL($s)) {
			$s = $this->createURL($s);
		} else if ($this->isContainTag($s)) {
			// test
			$s = $this->createTag($s);
		} else if (stripos($s, "@") === 0) {
			$s = $this->createEmailOrUserLink($s);
		} else if (stripos($s, "www.") === 0) {
			$s = $this->createLinkURL($s);
		} else if (filter_var($s, FILTER_VALIDATE_EMAIL)) {
			$s = "<a href=\"mailto:" . $souce . "\">$souce</a>";
		}
		return $s;
	}
	public function removeYouTubeLink($content, $removeReturn = true)
	{
		$s = str_replace("\n", "\r\n", $content);
		$s = str_replace("\r\n\n", "\r\n", $s);
		$arr1 = explode("\r\n", $s);
		foreach ($arr1 as $key1 => $val1) {
			$arr2 = @preg_split('/\s+/', $val1);
			foreach ($arr2 as $key2 => $val2) {
				if (stripos($val2, "youtube.com/watch") !== false || stripos($val2, "youtu.be/") !== false) {
					$arr2[$key2] = "";
				}
			}
			$arr1[$key1] = implode(" ", $arr2);
		}
		if ($removeReturn) {
			$ret = trim(implode(" ", $arr1));
		} else {
			$ret = trim(implode("\r\n", $arr1));
		}
		return $ret;
	}
}