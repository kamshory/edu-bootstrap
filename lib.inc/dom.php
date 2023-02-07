<?php
function tidyHTML($buffer)
{
	$buffer = UTF8ToEntities($buffer);
	$buff = "<html><body>" . $buffer . "</body></html>";
	try {
		$domDoc = new DOMDocument();
		$dom = $domDoc->loadHTML($buff, 0);
		if ($dom) {
			$domDoc->formatOutput = true;
			return getTagNode($domDoc->saveHTML(), "body");
		}
		else
		{
			return $buffer;
		}
	} catch (Exception $e) {
		return $buffer;
	}
}

function getTagNode($data, $tag)
{
	$result = $data;
	if (strlen($data)) 
	{
		$closetag = "</" . $tag . ">";
		$opentag = "<" . $tag;
		$pos1 = stripos($data, $opentag, 0);
		if ($pos1 === false) {
			$result = $data;
		} else {
			$pos1 = stripos($data, ">", $pos1 + strlen($tag) - 1) + 1;
			$pos2 = stripos($data, $closetag);
			if ($pos1 === false || $pos2 === false) {
				$result = $data;
			} else {
				return substr($data, $pos1, ($pos2 - $pos1));
			}
		}
	}
	return $result;
}
function parseHtmlData($data, $base = "")
{
	$ret = new stdClass();
	$dom = new DOMDocument("1.0", "UTF-8");
	$data = UTF8ToEntities($data);
	@$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
	$doc = new StdClass();
	$doc->xmlVersion = "1.0";
	$doc->xmlEncoding = "UTF-8";
	$xpath = new DOMXPath($dom);
	// get heading
	$alternateheader = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
	foreach ($alternateheader as $header) {
		$hdr = @$xpath->evaluate("/html/body//" . $header);
		for ($i = 0; $i < $hdr->length; $i++) {
			$ret->h[$header][$i] = trim($hdr->item($i)->nodeValue);
		}
	}

	// get paragraphs
	$par = @$xpath->evaluate("/html/body//p"); //NOSONAR
	$j = 0;
	for ($i = 0; $i < $par->length; $i++) {
		$p = trim(UTF8ToEntities(htmlspecialchars($par->item($i)->nodeValue)), " \r\n ");
		$p = trim(preg_replace('/\s+/', ' ', $p));
		if (!empty($p)) {
			$ret->p[$j] = $p;
			$j++;
		}
	}

	// get thumbnails
	$imgs = @$xpath->evaluate("/html/body//img"); //NOSONAR
	for ($i = 0; $i < $imgs->length; $i++) {
		$img = $imgs->item($i);
		$url = $img->getAttribute('src');
		$width = $img->getAttribute('width');
		$height = $img->getAttribute('height');
		if (stripos($url, "://") === false && strlen($base)) {
			// replace base
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$ret->img[$i]['src'] = trim($newURL);
		$ret->img[$i]['width'] = trim($width);
		$ret->img[$i]['height'] = trim($height);
	}
	return $ret;
}

function filterHtmlTags($content)
{
	global $picoEdu;
	$content = UTF8ToEntities($content);
	if ($picoEdu->getProfile("filter_javascript", -1, 0)) {
		$content = strip_only_tags($content, "script", true);
	}
	if ($picoEdu->getProfile("filter_style", -1, 0)) {
		$content = strip_only_tags($content, "style", true);
	}
	if ($picoEdu->getProfile("filter_div", -1, 0)) {
		$content = strip_only_tags($content, "div", false);
	}
	if ($picoEdu->getProfile("filter_table", -1, 0)) {
		$content = strip_only_tags($content, array("table", "caption", "th", "colgroup", "thead", "tbody", "tr", "td"), false);
	}
	if ($picoEdu->getProfile("filter_frame", -1, 0)) {
		$content = strip_only_tags($content, array("frameset", "frame", "iframe"), true);
	}
	if ($picoEdu->getProfile("filter_link", -1, 0)) {
		$content = strip_only_tags($content, array("link"), true);
	}
	if ($picoEdu->getProfile("filter_event", -1, 0)) {
		$content = removeEvent($content, array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'));
	}
	return tidyHTML($content);
}

function removeEvent($sSource, $aDisabledAttributes = false)
{
	if (!is_array($aDisabledAttributes)) {
		$aDisabledAttributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	}

	$sSource = stripcslashes($sSource);
	if (empty($aDisabledAttributes)) {
		return $sSource;
	}

	$aDisabledAttributes = @implode('|', $aDisabledAttributes);
	$sSource = preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . $aDisabledAttributes . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", $sSource);
	/**
	 * Old logic
	 * $sSource = preg_replace('/\s(' . $aDisabledAttributes . ').*?([\s\>])/', '\\2', $sSource);
	 * $sSource = preg_replace('@[a-z]*=""@is', '', $sSource); 
	 */
	return $sSource;
}

function stripCdata($string)
{
	preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $string, $matches);
	return str_replace($matches[0], $matches[1], $string);
}
function stripCdata2($string)
{
	preg_match_all('/<!\-\-\[cdata\[(.*?)\]\]\-\->/is', $string, $matches);
	return str_replace($matches[0], $matches[1], $string);
}

function cerrarTag($tag, $xml)
{
	$indice = 0;
	while ($indice < strlen($xml)) {
		$pos = stripos($xml, "<$tag", $indice);
		if ($pos !== false) 
		{
			$posCierre = stripos($xml, ">", $pos);
			if ($xml[$posCierre - 1] == "/") {
				$xml = substr_replace($xml, "></$tag>", $posCierre - 1, 2);
			}
			$indice = $posCierre;
		} 
		else 
		{
			break;
		}
	}
	if(brokenTags($xml) && stripos($xml, "<$tag", 0) !== false) 
	{
		$xml .= "</$tag>";
	}
	return $xml;
}

function brokenTags($str)
{
	preg_match_all("/(<\w+)(?:.){0,}?>/", $str, $v1); // NOSONAR
	preg_match_all("/<\/\w+>/", $str, $v2);
	$open = array_map('strtolower', $v1[1]);
	$closed = array_map('strtolower', $v2[0]);
	foreach ($open as $tag) {
		$end_tag = preg_replace("/<(.*)/", "</$1>", $tag);
		if (!in_array($end_tag, $closed)) 
		{
			return true;
		}
		unset($closed[array_search($end_tag, $closed)]);
	}
	return false;
}

function replaceClass($data, $search, $replace) //NOSONAR
{
	$data = cerrarTag("script", $data);
	$dom = new DOMDocument("1.0", "UTF-8");
	$data = UTF8ToEntities($data);
	@$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
	$doc = new StdClass();
	$doc->xmlVersion = "1.0";
	$doc->xmlEncoding = "UTF-8";
	$xpath = new DOMXPath($dom);
	$blockclasses = @$xpath->evaluate("/html/body//div");
	for ($i = 0; $i < $blockclasses->length; $i++) {
		$blockclass = $blockclasses->item($i);
		$class = $blockclass->getAttribute('class');
		if (is_array($search)) {
			foreach ($search as $sr) {
				if (strcasecmp($sr, $class) == 0) {
					$newclass = $replace;
					$blockclass->removeAttribute('class');
					$blockclass->setAttribute("class", $newclass);
				}
			}
		} else {
			$sr = $search;
			if (strcasecmp($sr, $class) == 0) {
				$newclass = $replace;
				$blockclass->removeAttribute('class');
				$blockclass->setAttribute("class", $newclass);
			}
		}
	}
	$dom->encoding = "utf-8";
	$data = $dom->saveHTML();
	$data = UTF8ToEntities($data);
	if (stripos($data, '<body') !== false) //NOSONAR
	{
		$data = getTagNode($data, 'body');
	}
	else if (stripos($data, '<head') !== false) //NOSONAR
	{
		$data = getTagNode($data, 'head');
	}
	return $data;
}

function replaceUrlPrefix($data, $search, $replace, $base = "") //NOSONAR
{
	$dom = new DOMDocument("1.0", "UTF-8");
	$data = UTF8ToEntities($data);
	@$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
	$doc = new StdClass();
	$doc->xmlVersion = "1.0";
	$doc->xmlEncoding = "UTF-8";
	$xpath = new DOMXPath($dom);
	// a tag
	$hrefs = @$xpath->evaluate("/html/body//a"); //NOSONAR
	for ($i = 0; $i < $hrefs->length; $i++) {
		$href = $hrefs->item($i);
		$url = $href->getAttribute('href');
		if (stripos($url, $search, 0) === 0) {
			$newURL = $replace . substr($url, strlen($search));
		} else {
			$newURL = $url;
		}
		$href->removeAttribute('href');
		$href->setAttribute("href", $newURL);
	}
	// img tag
	$srcs = @$xpath->evaluate("/html/body//img"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, $search, 0) === 0) {
			$newURL = $replace . substr($url, strlen($search));
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// audio tag
	$srcs = @$xpath->evaluate("/html/body//audio"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, $search, 0) === 0) {
			$newURL = $replace . substr($url, strlen($search));
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// video tag
	$srcs = @$xpath->evaluate("/html/body//video"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// iframe tag
	$srcs = @$xpath->evaluate("/html/body//iframe"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// frame tag
	$srcs = @$xpath->evaluate("/html/body//frame"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// param tag
	$srcs = @$xpath->evaluate("/html/body//param"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$name = $src->getAttribute('name');
		if (strtolower($name) == 'src') {
			$url = $src->getAttribute('value');
			if (stripos($url, $search, 0) === 0) {
				$newURL = $replace . substr($url, strlen($search));
			} else {
				$newURL = $url;
			}
			$src->removeAttribute('value');
			$src->setAttribute("value", $newURL);
		}
	}
	// object tag
	$srcs = @$xpath->evaluate("/html/body//object"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$name = $src->getAttribute('type');
		if (stripos($name, 'application') !== false) {
			$url = $src->getAttribute('data');
			if (stripos($url, $search, 0) === 0) {
				$newURL = $replace . substr($url, strlen($search));
			} else {
				$newURL = $url;
			}
			$src->removeAttribute('data');
			$src->setAttribute("data", $newURL);
		}
	}

	// object tag
	$srcs = @$xpath->evaluate("/html/body//embed"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, $search, 0) === 0) {
			$newURL = $replace . substr($url, strlen($search));
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	$dom->encoding = "utf-8";
	$data = $dom->saveHTML();
	$data = UTF8ToEntities($data);
	if (stripos($data, '<body') !== false)
	{
		$data = getTagNode($data, 'body');
	}
	else if (stripos($data, '<head') !== false)
	{
		$data = getTagNode($data, 'head');
	}
	return $data;
}

function replaceBase($data, $base, $target = null) //NOSONAR
{
	$dom = new DOMDocument("1.0", "UTF-8");
	$data = UTF8ToEntities($data);
	@$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
	$doc = new StdClass();
	$doc->xmlVersion = "1.0";
	$doc->xmlEncoding = "UTF-8";
	$xpath = new DOMXPath($dom);
	// a tag
	$hrefs = @$xpath->evaluate("/html/body//a");
	for ($i = 0; $i < $hrefs->length; $i++) {
		$href = $hrefs->item($i);
		$url = $href->getAttribute('href');
		$href->removeAttribute('target');
		if ($target)
		{
			$href->setAttribute("target", $target);
		}
		if (stripos($url, "://") === false && strlen($base)) {
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$href->removeAttribute('href');
		$href->setAttribute("href", $newURL);
	}
	// img tag
	$srcs = @$xpath->evaluate("/html/body//img"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) //NOSONAR
		{
			$newURL = $base . $url;
		} 
		else 
		{
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// audio tag
	$srcs = @$xpath->evaluate("/html/body//audio");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) {
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// video tag
	$srcs = @$xpath->evaluate("/html/body//video");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) {
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// iframe tag
	$srcs = @$xpath->evaluate("/html/body//iframe");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) {
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// frame tag
	$srcs = @$xpath->evaluate("/html/body//frame");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// param tag
	$srcs = @$xpath->evaluate("/html/body//param");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$name = $src->getAttribute('name');
		if (strtolower($name) == 'src') {
			$url = $src->getAttribute('value');
			if (stripos($url, "://") === false && strlen($base)) {
				$newURL = $base . $url;
			} else {
				$newURL = $url;
			}
			$src->removeAttribute('value');
			$src->setAttribute("value", $newURL);
		}
	}
	// object tag
	$srcs = @$xpath->evaluate("/html/body//object");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$name = $src->getAttribute('type');
		if (stripos($name, 'application') !== false) {
			$url = $src->getAttribute('data');
			if (stripos($url, "://") === false && strlen($base)) {
				$newURL = $base . $url;
			} else {
				$newURL = $url;
			}
			$src->removeAttribute('data');
			$src->setAttribute("data", $newURL);
		}
	}

	// object tag
	$srcs = @$xpath->evaluate("/html/body//embed");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			$newURL = $base . $url;
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	$dom->encoding = "utf-8";
	$data = $dom->saveHTML();
	$data = UTF8ToEntities($data);
	if (stripos($data, '<body') !== false)
	{
		$data = getTagNode($data, 'body');
	}
	else if (stripos($data, '<head') !== false)
	{
		$data = getTagNode($data, 'head');
	}
	return $data;
}

function replaceBaseFile($data, $base, $target = null, $onlystartedwith = null) //NOSONAR
{
	$dom = new DOMDocument("1.0", "UTF-8");
	$data = UTF8ToEntities($data);
	@$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
	$doc = new StdClass();
	$doc->xmlVersion = "1.0";
	$doc->xmlEncoding = "UTF-8";
	$xpath = new DOMXPath($dom);
	// a tag
	$hrefs = @$xpath->evaluate("/html/body//a");
	for ($i = 0; $i < $hrefs->length; $i++) {
		$href = $hrefs->item($i);
		$url = $href->getAttribute('href');
		$href->removeAttribute('target');
		if ($target)
		{
			$href->setAttribute("target", $target);
		}
		if (stripos($url, "://") === false && strlen($base) && (stripos($url, $onlystartedwith) === 0 || !$onlystartedwith)) {
			if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
				$url = substr($url, strlen($onlystartedwith));
				$newURL = $base . $url;
			}
		} else {
			$newURL = $url;
		}
		$href->removeAttribute('href');
		$href->setAttribute("href", $newURL);
	}
	// img tag
	$srcs = @$xpath->evaluate("/html/body//img"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) // && (stripos($url, $onlystartedwith) === 0 || !$onlystartedwith))
		{
			if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
				$url = substr($url, strlen($onlystartedwith));
				$newURL = $base . $url;
			} else {
				$newURL = $url;
			}
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// audio tag
	$srcs = @$xpath->evaluate("/html/body//audio");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
				$url = substr($url, strlen($onlystartedwith));
				$newURL = $base . $url;
			} else {
				$newURL = $url;
			}
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// video tag
	$srcs = @$xpath->evaluate("/html/body//video");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
				$url = substr($url, strlen($onlystartedwith));
				$newURL = $base . $url;
			} else {
				$newURL = $url;
			}
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// iframe tag
	$srcs = @$xpath->evaluate("/html/body//iframe");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
				$url = substr($url, strlen($onlystartedwith));
				$newURL = $base . $url;
			} else {
				$newURL = $url;
			}
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// frame tag
	$srcs = @$xpath->evaluate("/html/body//frame");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
				$url = substr($url, strlen($onlystartedwith));
				$newURL = $base . $url;
			} else {
				$newURL = $url;
			}
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	// param tag
	$srcs = @$xpath->evaluate("/html/body//param");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$name = $src->getAttribute('name');
		if (strtolower($name) == 'src') {
			$url = $src->getAttribute('value');
			if (stripos($url, "://") === false && strlen($base)) {
				if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
					$url = substr($url, strlen($onlystartedwith));
					$newURL = $base . $url;
				} else {
					$newURL = $url;
				}
			} else {
				$newURL = $url;
			}
			$src->removeAttribute('value');
			$src->setAttribute("value", $newURL);
		}
	}
	// object tag
	$srcs = @$xpath->evaluate("/html/body//object");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$name = $src->getAttribute('type');
		if (stripos($name, 'application') !== false) {
			$url = $src->getAttribute('data');
			if (stripos($url, "://") === false && strlen($base)) {
				if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
					$url = substr($url, strlen($onlystartedwith));
					$newURL = $base . $url;
				} else {
					$newURL = $url;
				}
			} else {
				$newURL = $url;
			}
			$src->removeAttribute('data');
			$src->setAttribute("data", $newURL);
		}
	}

	// object tag
	$srcs = @$xpath->evaluate("/html/body//embed");
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		if (stripos($url, "://") === false && strlen($base)) {
			if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
				$url = substr($url, strlen($onlystartedwith));
				$newURL = $base . $url;
			} else {
				$newURL = $url;
			}
		} else {
			$newURL = $url;
		}
		$src->removeAttribute('src');
		$src->setAttribute("src", $newURL);
	}
	$dom->encoding = "utf-8";
	$data = $dom->saveHTML();
	$data = UTF8ToEntities($data);
	if (stripos($data, '<body') !== false)
	{
		$data = getTagNode($data, 'body');
	}
	else if (stripos($data, '<head') !== false)
	{
		$data = getTagNode($data, 'head');
	}
	return $data;
}

function addFirstParagraphClass($data)
{
	$data = cerrarTag("script", $data);
	$dom = new DOMDocument("1.0", "UTF-8");
	$data = UTF8ToEntities($data);
	@$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
	$xpath = new DOMXPath($dom);
	$blockclasses = @$xpath->evaluate("/html/body//p"); //NOSONAR
	if ($blockclasses->length > 0) {
		$blockclass = $blockclasses->item(0);
		$class = $blockclass->getAttribute('class');
		$class .= " first-paragraph";
		$class = trim($class);
		$blockclass->removeAttribute('class');
		$blockclass->setAttribute("class", $class);
	}
	$dom->encoding = "utf-8";
	$data = $dom->saveHTML();
	if (stripos($data, '<body') !== false)
	{
		$data = getTagNode($data, 'body');
	}
	else if (stripos($data, '<head') !== false)
	{
		$data = getTagNode($data, 'head');
	}
	return $data;
}


function extractImageData($data, $directory, $prefix, $fileSync) //NOSONAR
{
	$dom = new DOMDocument("1.0", "UTF-8");
	$data = UTF8ToEntities($data);
	@$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
	$doc = new StdClass();
	$doc->xmlVersion = "1.0";
	$doc->xmlEncoding = "UTF-8";
	$xpath = new DOMXPath($dom);

	// img tag
	$srcs = @$xpath->evaluate("/html/body//img"); //NOSONAR
	for ($i = 0; $i < $srcs->length; $i++) {
		$src = $srcs->item($i);
		$url = $src->getAttribute('src');
		$data_latex = @$src->getAttribute('data-latex') . "";
		if ($data_latex != "") {
			$src->setAttribute("data-latex", $data_latex);
		}
		$alt = @$src->getAttribute('alt') . "";
		if ($alt != "") {
			$src->setAttribute("alt", $alt);
		}
		if (stripos($url, "data:image/") === 0) {
			$path = 0;
			$arr = explode(",", $url);
			$data = $arr[1];
			$arr1 = explode("/", $arr[0]);
			$arr2 = explode(";", $arr1[1]);
			$type = trim($arr2[0], "/;");
			unset($arr);
			$fn = md5($data);

			if (stripos($type, 'svg') !== false) {
				$path = $directory . "/" . $fn . ".svg";
				$filename = $prefix . "/" . $fn . ".svg";
				$fileSync->createFileWithContent($path, base64_decode($data), true);
				$src->removeAttribute('src');
				$src->setAttribute("src", $filename);
			} else {
				$image = @imagecreatefromstring(base64_decode($data));
				if ($image) {
					if (!file_exists($directory)) {
						$fileSync->createDirecory($directory, 0755, true);
					}
					switch ($type) {
						case "png":
							$path = $directory . "/" . $fn . ".png";
							$filename = $prefix . "/" . $fn . ".png";
							$fileSync->createFileWithContent($path, base64_decode($data), true);
							break;
						case "gif":
							$path = $directory . "/" . $fn . ".gif";
							$filename = $prefix . "/" . $fn . ".gif";
							$fileSync->createFileWithContent($path, base64_decode($data), true);
							break;
						default:
							$path = $directory . "/" . $fn . ".jpeg";
							$filename = $prefix . "/" . $fn . ".jpeg";
							$fileSync->createFileWithContent($path, base64_decode($data), true);
							break;
					}
					$src->removeAttribute('src');
					$src->setAttribute("src", $filename);
				}
			}
		}
	}

	$dom->encoding = "utf-8";
	$data = $dom->saveHTML();
	$data = UTF8ToEntities($data);
	if (stripos($data, '<body') !== false)
	{
		$data = getTagNode($data, 'body');
	}
	else if (stripos($data, '<head') !== false)
	{
		$data = getTagNode($data, 'head');
	}
	return $data;
}


function getDataInTag($data, $tag)
{
	$result = $data;
	if (strlen($data)) {
		$closetag = "</" . $tag . ">";
		$opentag = "<" . $tag;
		$pos1 = stripos($data, $opentag, 0);
		if ($pos1 === false)
		{
			$result = $data;
		} else {
			$pos1 = stripos($data, ">", $pos1 + strlen($tag) - 1) + 1;
			$pos2 = stripos($data, $closetag);
			if ($pos1 === false || $pos2 === false) {
				$result = $data;
			}
			else
			{
				$result = substr($data, $pos1, ($pos2 - $pos1));	
			} 
		}
	}
	return $result;
	
}

function extractParagraph($data)
{
    $result = array();
    $dom = new DOMDocument("1.0", "UTF-8");
	$data = UTF8ToEntities($data);
	@$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
	$doc = new StdClass();
	$doc->xmlVersion = "1.0";
	$doc->xmlEncoding = "UTF-8";
	$xpath = new DOMXPath($dom);
	// a tag
	$hrefs = @$xpath->evaluate("/html/body//p"); //NOSONAR
	for ($i = 0; $i < $hrefs->length; $i++) {
		$par = $hrefs->item($i);
        $result[$i] = $par->textContent;
	}
    return $result;
}
