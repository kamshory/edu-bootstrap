<?php

namespace Pico;

class RichText
{

	/**
	 * Check if input is valid video
	 *
	 * @param string $input
	 * @return boolean
	 */
	private function isVideo($input)
	{
		return stripos($input, "://") !== false
			&& stripos($input, "facebook.com") !== false
			&& (stripos($input, "/videos/") !== false || stripos($input, "/video/embed") !== false);
	}

	/**
	 * Check if input is YouTube URL
	 *
	 * @param string $input
	 * @return boolean
	 */
	private function isYouTube($input)
	{
		return stripos($input, "://") !== false
			&& stripos($input, "youtube.com") !== false;
	}

	/**
	 * Check if input is ort YouTube URL
	 *
	 * @param string $input
	 * @return boolean
	 */
	private function isYouTubeShort($input)
	{
		return stripos($input, "://") !== false
			&& stripos($input, "youtu.be") !== false;
	}

	/**
	 * Check if input is YouTube thumbnail image
	 *
	 * @param string $input
	 * @return boolean
	 */
	private function isYtImage($input)
	{
		return stripos($input, "://") !== false
			&& stripos($input, "ytimg.com") !== false;
	}

	/**
	 * Check if input is contain URL
	 *
	 * @param string $input
	 * @return boolean
	 */
	private function isContainURL($input)
	{
		return stripos($input, "://") !== false;
	}

	/**
	 * Check if input is containing tag
	 *
	 * @param string $input
	 * @return boolean
	 */
	private function isContainTag($input)
	{
		return stripos($input, "#") === 0;
	}

	/**
	 * Create video element
	 *
	 * @param string $input
	 * @return string
	 */
	private function createVideo($input)
	{
		$souce = htmlspecialchars_decode($input);
		$start1 = stripos($input, "/video/embed");
		$start2 = stripos($input, "/videos/");
		if ($start1 !== false) {
			$data = parse_url($souce);
			parse_str(@$data['query'], $args);
			$video_id = @$args['video_id'];
			$input = '<iframe width="100%" height="218" src="https://www.facebook.com/video/embed?video_id=' . $video_id . '" frameborder="0" marginheight="0" marginwidth="0" vspace="0" hspace="0" scrolling="no" allowfullscreen allowtransparency="true" style="display:block;margin:4px 0px;"></iframe>';
		}
		if ($start2 !== false) {
			$substr = ltrim(substr($input, $start2 + 8), "/");
			$arr = explode("/", $substr);
			$video_id = $arr[0];
			$input = '<iframe width="100%" height="218" src="https://www.facebook.com/video/embed?video_id=' . $video_id . '" frameborder="0" marginheight="0" marginwidth="0" vspace="0" hspace="0" scrolling="no" allowfullscreen allowtransparency="true" style="display:block;margin:4px 0px;"></iframe>';
		}
		return $input;
	}

	/**
	 * Create YouTube video element
	 *
	 * @param string $input
	 * @return string
	 */
	private function createYouTube($input)
	{
		$souce = htmlspecialchars_decode($input);
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
		$input = '<div class="youtube-video"><a href="javascript:;" data-url="' . $yurl . '"><img data-full-width="true" class="image-youtube-video" src="https://i1.ytimg.com/vi/' . $vid . '/hqdefault.jpg" /><div class="video-player"></div></a></div>'; //NOSONAR
		return $input;
	}

	/**
	 * Create short YouTube video element
	 *
	 * @param string $input
	 * @return string
	 */
	private function createYouTubeShort($input)
	{
		$souce = htmlspecialchars_decode($input);
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
		$input = '<div class="youtube-video"><a href="javascript:;" data-url="' . $yurl . '"><img data-full-width="true" class="image-youtube-video" src="https://i1.ytimg.com/vi/' . $vid . '/hqdefault.jpg" /><div class="video-player"></div></a></div>'; //NOSONAR
		return $input;
	}

	private function createYtImage($input)
	{
		$souce = htmlspecialchars_decode($input);
		$data = parse_url($souce);
		$str = trim(@$data['path'], "/");
		$arr = explode("/", $str);
		$vid = @$arr[1];
		$yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1"; //NOSONAR
		$input = '<div class="youtube-video"><a href="javascript:;" data-url="' . $yurl . '"><img data-full-width="true" class="image-youtube-video" src="https://i1.ytimg.com/vi/' . $vid . '/hqdefault.jpg" /><div class="video-player"></div></a></div>'; //NOSONAR
		return $input;
	}

	private function createURL($input)
	{
		$souce = htmlspecialchars_decode($input);
		$arr = explode("://", $input, 2);
		$protocol1 = $arr[0];
		$protocol2 = ltrim(preg_replace("/[^A-Za-z\d]/i", "", $protocol1), "0123456789"); //NOSONAR
		if ($protocol1 != $protocol2) {
			$url = $protocol2 . "://" . $arr[1];
		} else {
			$url = $input;
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

	private function createTag($input)
	{
		$souce = htmlspecialchars_decode($input);
		$numtags = substr_count($souce, "#");
		if ($numtags == 1) {
			$input = "<a class=\"link-to-tag post-tag\" data-tag=\"" . substr($souce, 1) . "\" href=\"find.php?tag=" . substr($souce, 1) . "\">$souce</a>"; //NOSONAR
		} else {
			$arr = explode("#", $souce);
			$arr2 = array();
			foreach ($arr as $key => $val) {
				$arr2[$key] = "<a class=\"link-to-tag post-tag\" data-tag=\"" . $val . "\" href=\"find.php?tag=" . $val . "\">#$val</a>"; //NOSONAR
				$arr[$key] = "#" . $arr[$key];
			}
			$input = str_replace($arr, $arr2, $input);
		}
		return $input;
	}

	private function createEmailOrUserLink($input)
	{
		$souce = htmlspecialchars_decode($input);
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

		$input = str_replace($arr4, $arr5, $input);

		if (stripos($val, "#") !== false) {
			$arr6 = explode("#", $val);
			$arr7 = array();
			foreach ($arr6 as $key6 => $val6) {
				$arr7[$key6] = "<a class=\"link-to-tag post-tag\" data-tag=\"" . ($val6) . "\" href=\"find.php?tag=" . ($val6) . "\">#$val6</a>";
				$arr6[$key6] = "#" . $arr6[$key6];
			}
			$input = str_replace($arr6, $arr7, $input);
		}
		return $input;
	}

	private function createLinkURL($input)
	{
		$souce = htmlspecialchars_decode($input);
		if (stripos($input, "/") === false) {
			$link = preg_replace('/^\PL+|\PL\z/', '', $souce);
		} else {
			$link = $souce;
		}
		return str_replace($link, "<a href=\"http://" . $link . "\" target=\"_blank\">$link</a>", $souce);
	}

	public function createRichContent($input)
	{
		$souce = htmlspecialchars_decode($input);
		if ($this->isVideo($input)) {
			$input = $this->createVideo($input);
		} else if ($this->isYouTube($input)) {
			$input = $this->createYouTube($input);
		} else if ($this->isYouTubeShort($input)) {
			$input = $this->createYouTubeShort($input);
		} else if ($this->isYtImage($input)) {
			$input = $this->createYtImage($input);
		} else if ($this->isContainURL($input)) {
			$input = $this->createURL($input);
		} else if ($this->isContainTag($input)) {
			// test
			$input = $this->createTag($input);
		} else if (stripos($input, "@") === 0) {
			$input = $this->createEmailOrUserLink($input);
		} else if (stripos($input, "www.") === 0) {
			$input = $this->createLinkURL($input);
		} else if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
			$input = "<a href=\"mailto:" . $souce . "\">$souce</a>";
		}
		return $input;
	}
	public function removeYouTubeLink($content, $removeReturn = true)
	{
		$input = str_replace("\n", "\r\n", $content);
		$input = str_replace("\r\n\n", "\r\n", $input);
		$arr1 = explode("\r\n", $input);
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
